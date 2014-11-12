<?php
namespace Phortress\Dephenses\Taint;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

class FunctionAnalyser{
    const TAINT_KEY = "taint";
    const SANITISATION_KEY = "sanfuncs";
    const VARIABLE_KEY = "variable";
    const UNRESOLVED_VARIABLE_KEY = "unresolved";
    const VARIABLE_DEF = "def";
    /**
     * Return statements and the variables they are dependent on.
     * array(Stmt => array(variable name => array(Variable Info, as stored in the $variables array))
     */
    protected $returnStmts = array();
    
    /**
     * The parameters to the function
     */
    protected $params = array();
    
    /**
     * The statements in the function
     */
    protected $functionStmts;
    
    /**
     * array(variable name => array(VARIABLE_KEY => variable, TAINT_KEY => taint,
     *  SANITISATION_KEY => array(sanitising functions)))
     */
    protected $variables = array();
    
    /**
     * Environment where the function was defined
     */
    protected $environment;
    
    protected $function;
    
    public function __construct(\Phortress\Environment $env, $functionName) {
        assert(!($functionName instanceof Expr));
        //For now we do not handle dynamic function names;
        $this->environment = $env;
        $this->function = $env->resolveFunction($functionName);
        $this->functionStmts = $this->function->stmts;
        $this->params = $this->function->params;
        $this->analyseReturnStatementsDependencies($this->functionStmts);
    }
    
    public static function getFunctionAnalyser(\Phortress\Environment $env, $functionName){
        assert(!empty($env));
        $func_def = $env->resolveFunction($functionName);
        $analyser = $func_def->analyser;
        if(empty($analyser)){
            $analyser = new FunctionAnalyser($env, $functionName);
            $func_def->analyser = $analyser;
        }
        return $analyser;
    }
    
    /**
     * Takes in an array of Node\Args[]
     * Returns an array containing taint value of the value returned by the function,
     * and the array of sanitising functions applied
     */
    public function analyseFunctionCall($args){
        $result = array(Annotation::UNASSIGNED, array());
        foreach($this->returnStmts as $return){
            $ret_effect = $this->analyseArgumentsEffectOnReturn($args, $return);
            $result = array(max($result[0], $ret_effect[0]), merge_array($result[1], $ret_effect[1]));
        }
        return $result;
    }
    
    private function analyseArgumentsEffectOnReturn($args, $return){
        $taint_mappings = $this->getParametersToTaintMappings($args);
        $sanitising_funcs = array();
        $taint_val = Annotation::UNASSIGNED;
        foreach($return as $var_name => $var_info){
            $sanitising_funcs = array_merge($sanitising_funcs, $var_info[self::SANITISATION_KEY]);
            if(!empty($var_info[self::TAINT_KEY])){
                $taint_val = max($taint_val, $var_info[self::TAINT_KEY]);
            }
            if(array_key_exists($var_name, $taint_mappings)){
                $taint_val = max($taint_val, $taint_mappings[$var_name]);
            }
        }
        return array($taint_val, $sanitising_funcs);
    }
    
    private function getParametersToTaintMappings($args){
        $mappings = array();
        for($i = 0; $i<count($args);$i++){
            $param = $this->params[$i];
            $arg_val = $args[$i]->value;
            $taint_val = StmtAnalyser::resolveExprTaint($arg_val);
            $mappings[$param->name] = $taint_val;
        }
        return $mappings;
    }

    private function analyseReturnStatementsDependencies($stmts){
        $retStmts = $this->getReturnStatements($stmts);
        $stmt_dependencies = array();
        foreach($retStmts as $ret){
            $depending_vars = $this->analyseStatementDependency($ret);
            $index = $ret->getLine(); //Use the statement's line number to index the statement for now.
            $stmt_dependencies[$index] = $depending_vars;
        }
        $this->returnStmts = $stmt_dependencies;
    }
    
    private function analyseStatementDependency(Stmt\Return_ $stmt){
        $exp = $stmt->expr;
        $trace = array();
        if($exp instanceof Expr){
            $trace = $this->traceExpressionVariables($exp);
        }
        return $trace;
    }
    
    private function traceExpressionVariables(Expr $exp){
        if($exp instanceof Node\Scalar){
            return array();
        }else if ($exp instanceof Expr\Variable) {
            return $this->traceVariable($exp);
        }else if($exp instanceof Expr\ClassConstFetch || Expr\ConstFetch){
            return array();
        }else if($exp instanceof Expr\PreInc || $exp instanceof Expr\PreDec || $exp instanceof Expr\PostInc || $exp instanceof Expr\PostDec){
            $var = $exp->var;
            return $this->traceVariable($var);
        }else if($exp instanceof Expr\BinaryOp){
            return $this->traceBinaryOp($exp);
        }else if($exp instanceof Expr\UnaryMinus || $exp instanceof Expr\UnaryPlus){
            $var = $exp->expr;
            return $this->traceVariable($var);
        }else if($exp instanceof Expr\Array_){
            return $this->traceVariablesInArray($exp);
        }else if($exp instanceof Expr\ArrayDimFetch){
            //For now treat all array dimension fields as one
            $var = $exp->var;
            return $this->traceVariable($var);
        }else if($exp instanceof Expr\PropertyFetch){
            $var = $exp->var;
            return $this->traceVariable($var);
        }else if($exp instanceof Expr\StaticPropertyFetch){
            //TODO:
        }else if($exp instanceof Expr\FuncCall){
            return $this->traceFunctionCall($exp);
        }else if($exp instanceof Expr\MethodCall){
            return $this->traceMethodCall($exp);
        }else if($exp instanceof Expr\Ternary){
            //If-else block
           return $this->traceTernaryTrace($exp);
        }else if($exp instanceof Expr\Eval_){
            return $this->resolveTernaryTrace($exp->expr);
        }else{
            //Other expressions we will not handle.
            return array();
        }
    }
    
    private static function traceFunctionCall(Expr\FuncCall $exp){
        $func_name = $exp->name;
        $args = $exp->args;
        $traced_args = array();
        
        foreach($args as $arg){
            $arg_val = $arg->value;
            $traced = $this->traceExpressionVariables($arg_val);
            
            $traced_args[] = $this->addSanitisingFunctionInfo($traced, $func_name);
        }
        return $this->mergeVariables($traced_args);
    }
    
    private static function addSanitisingFunctionInfo($var_infolist, $func_name){
        foreach($var_infolist as $var=>$infolist){
            $original = $infolist[self::SANITISATION_KEY];
            if(\SanitisingFunctions::isSanitisingFunction($func_name)){
                $new_list = array_merge($original, array($func_name));
                $infolist[self::SANITISATION_KEY] = $new_list;
            }else if(\SanitisingFunctions::isSanitisingReverseFunction($func_name)){
                $reverse_func = \SanitisingFunctions::getAffectedSanitiser($func_name);
                $new_list = array_diff($original, array($reverse_func));
                $infolist[self::SANITISATION_KEY] = $new_list;
            }
        }
        return $var_infolist;
    }
    
    private static function traceMethodCall(Expr\MethodCall $exp){
        
    }
    
     private static function resolveTernaryTrace(Expr\Ternary $exp){
        $if = $exp->if;
        $else = $exp->else;
        $if_trace = $this->traceExpressionVariables($if);
        $else_trace = $this->traceExpressionVariables($else);
        return $this->mergeTaintValues($if_trace, $else_trace);
    }
    
    private function traceVariablesInArray(Expr\Array_ $arr){
        $arr_items = $arr->items;
        $var_traces = array();
        foreach($arr_items as $item){
            $exp = $item->value;
            $var_traces[] = $this->traceExpressionVariables($exp);
        }
        return $this->mergeVariables($var_traces);
    }
    
    private function traceBinaryOp(Expr\BinaryOp $exp){
        $left = $exp->left;
        $right = $exp->right;
        $left_var = $this->traceVariable($left);
        $right_var = $this->traceVariable($right);
        return $this->mergeVariables(array($left_var, $right_var));
    }
    
    private function mergeVariables($vars){
        $merged = array();
        foreach($vars as $var){
            if(empty($var)){
                continue;
            }
            $var_name = key($var);
            if(!array_key_exists($var_name, $merged)){
                $merged[$var_name] = $var;
            }else{
                $existing = $merged[$var_name];
                $merged[$var_name] = $this->mergeVariableRecords($existing, $var);
            }
        }
    }
    
    private function mergeVariableRecords($var1, $var2){
        $taint = $this->mergeTaintValues($var1, $var2);
        $san = $this->mergeSanitisingFunctions($var1, $var2);
        $var_arr = $this->constructVariableDetails($var1[self::VARIABLE_KEY], $taint, $san);
        $var_arr[self::VARIABLE_DEF] = $var2[self::VARIABLE_DEF];
        return $var_arr;
    }
    
    private function mergeTaintValues($var1, $var2){
        return max($var1[self::TAINT_KEY], $var2[self::TAINT_KEY]);
    }
    
    private function mergeSanitisingFunctions($var1, $var2){
        $sanitising1 = $var1[self::SANITISATION_KEY];
        $sanitising2 = $var2[self::SANITISATION_KEY];
        return array_intersect($sanitising1, $sanitising2);
    }
    
    private function traceVariable(Expr\Variable $var){
        $name = $var->name;
        if($name instanceof Expr){
            $name = self::UNRESOLVED_VARIABLE_KEY;
        }
        $var_details = $this->getVariableDetails($var);
        $details_ret = array($name => $var_details);
        
        if(\Phortress\Dephenses\InputSources::isInputVariable($var)){
            $var_details[self::TAINT_KEY] = Annotation::TAINTED;
            return $details_ret;
        }
        
        if(!$this->isFunctionParameter($var)){
            $assign = $var_details[self::VARIABLE_DEF];
            if(!empty($assign)){
                $ref_expr = $assign->expr;
                return $this->traceExpressionVariables($ref_expr);
            }else{
                return $details_ret;
            }
            
        }else{
            return $details_ret;
        }
    }
    
    private function isFunctionParameter(Expr\Variable $var){
        $name = $var->name;
        $filter = function($item) use ($name){
            return ($item->name == $name);
        };
        return !empty(array_filter($this->params, $filter));
    }
    
    private function constructVariableDetails(Expr\Variable $var, $taint = Annotation::UNASSIGNED, $sanitising = array()){
        return array(self::VARIABLE_KEY => $var,
                    self::TAINT_KEY => $taint,
                    self::SANITISATION_KEY => $sanitising);
    }
    
    private function getVariableDetails(Expr\Variable $var){
        $name = $var->name;
        if(array_key_exists($name, $this->variables)){
            return $this->variables[$name];
        }else if($name instanceof Expr){
            $unresolved_vars = $this->variables[self::UNRESOLVED_VARIABLE_KEY];
            $filter_matching = function($item) use ($var){
                return $item[self::VARIABLE_KEY] == $var;
            };
            $filter_res = array_filter($unresolved_vars, $filter_matching);
            if(!empty($filter_res)){
                return $filter_res;
            }else{
                $var_arr = $this->constructVariableDetails($var, Annotation::UNKNOWN);
                $unresolved_vars[] = $var_arr;
                return $var_arr;
            }            
        }else{
            if(array_key_exists($name, $this->variables)){
                return $this->variables[$name];
            }else{
                $assign = $var->environment->resolveVariable($name);
                $var_arr = $this->constructVariableDetails($var);
                $var_arr[self::VARIABLE_DEF] = $assign;
                $this->variables[$name] = $var_arr;
                return $var_arr;
            }
        }
    }
    
    private function getReturnStatements($stmts){
        $filter_returns = function($item){
            return ($item instanceof Stmt\Return_);
        };
        return array_filter($stmts, $filter_returns);
    }
    
}
