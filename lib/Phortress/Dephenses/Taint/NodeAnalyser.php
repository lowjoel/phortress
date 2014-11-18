<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 17/11/14
 * Time: 4:17 PM
 */

namespace Phortress\Dephenses\Taint;
use Phortress\Dephenses\Taint;
use Phortress\Environment;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

/**
 * Runs the analyser on a node in the parse tree.
 *
 * @param \PhpParser\Node $node
 */
class NodeAnalyser {
	public function analyse(Node $node, TaintEnvironment $taintEnv){
		$taintEnv = $taintEnv->copy();
		if($node instanceof Stmt){
			$result = $this->resolveStmtTaintEnvironment($node, $taintEnv);
			assert($result != null);
			return $result;
		}else if($node instanceof Expr){
			TaintEnvironment::updateTaintEnvironmentForEnvironment($node->environment, $taintEnv);
			$result = $this->resolveAssignmentTaintEnvironment($node);
			if(empty($result)){
				return $taintEnv;
			}else{
				return $result;
			}
		}else{
			return $taintEnv;
		}
	}

	protected function resolveAssignmentTaintEnvironment(Expr $exp){
		if($exp instanceof Expr\Assign){
			$this->resolveTaintEnvironmentForAssign($exp);
		}else if($exp instanceof Expr\AssignOp){
			$this->resolveTaintEnvironmentForAssignOp($exp);
		}
		$env = $exp->environment;
		return TaintEnvironment::getTaintEnvironmentFromEnvironment($env);
	}

	protected function resolveTaintEnvironmentForAssignOp(Expr\AssignOp $assignOp){
		$var = $assignOp->var;
		$varName = $var->name;
		if($varName instanceof Expr){
			//Cannot resolve variable variables.
			return;
		}

		$exp = $assignOp->expr;

		$assignEnv = $this->getVariableAssignmentEnvironment($var);
		$varEnv = $var->environment;

		$assignEnvTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($assignEnv);
		$assignEnvTaintResult = $assignEnvTaintEnv->getTaintResult($varName);

		$expTaint = $this->resolveExprTaint($exp);
		assert(isset($expTaint));
		$expTaint->merge($assignEnvTaintResult);

		$this->mergeVariableTaintEnvironment($varEnv, $varName, $expTaint);
	}

	protected function resolveTaintEnvironmentForAssign(Expr\Assign $assign){
		$var = $assign->var;
		$varName = $var->name;
		if($varName instanceof Expr){
			//Cannot resolve variable variables.
			return;
		}
		$exp = $assign->expr;

		if($var instanceof Expr\List_){
			$this->resolveListAssignment($assign);
		}else{
			$environment = $assign->environment;
			$expTaint = $this->resolveExprTaint($exp);
			$this->setVariableTaintEnvironment($environment, $varName, $expTaint);
		}
	}

	protected function setVariableTaintEnvironment(Environment $env, $varName,
	                                                    TaintResult $taintRes){
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($env);
		if(!isset($taintEnv)){
			$taintEnv = new TaintEnvironment($env);
		}
		$taintEnv->setTaintResult($varName, $taintRes);
		TaintEnvironment::setTaintEnvironmentForEnvironment($env, $taintEnv);
	}

	protected function mergeVariableTaintEnvironment(Environment $env, $varName,
	                                                    TaintResult $taintRes){
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($env);
		if(!isset($taintEnv)){
			$taintEnv = new TaintEnvironment($env);
		}
		$taintEnv->mergeAndSetTaintResult($varName, $taintRes);
		TaintEnvironment::setTaintEnvironmentForEnvironment($env, $taintEnv);
	}

	protected function getVariableAssignmentEnvironment(Expr\Variable $var){
		$varName = $var->name;
		if($var instanceof Expr){
			return $var->environment;
		}else{
			$resolution = $var->environment->resolveVariable($varName);
			return $resolution->environment;
		}
	}

	protected function resolveListAssignment(Expr\Assign $assign){
		assert($assign->var instanceof Expr\List_);
		$list_of_vars = $assign->var->vars;
		$exp = $assign->expr;

		if($exp instanceof Expr\Array_){
			$taint_vals = $this->resolveTaintOfExprsInArray($exp);

		}else{
			$taint_vals = array();
			$taintRes = $this->resolveExprTaint($exp);
			for($i = 0; $i < count(list_of_vars); $i++){
				$taint_vals[$i] = $taintRes;
			}
		}
		for($i = 0; $i < count(list_of_vars); $i++){
			$var = $list_of_vars[$i];
			$varName = $var->name;

			$env = $this->getVariableAssignmentEnvironment($var);
			assert(isset($env));
			$this->setVariableTaintEnvironment($env, $varName, $taint_vals[$i]);
		}


	}

	/**
	 * Takes in an array of Nodes and resolves their taint values if they are are variables.
	 * Returns an array containing the taint value of each item in the array.
	 */
	protected function resolveTaintOfExprsInArray(Expr\Cast\Array_ $array){
		$arr_items = $array->items;
		$taint_vals = array();
		foreach($arr_items as $item){
			$exp = $item->value;
			$taint_val = $this->createTaintResult(Annotation::UNKNOWN);
			if($exp instanceof Expr){
				$taint_val = $this->resolveExprTaint($exp);
			}
			$taint_vals[] = $taint_val;
		}
		return $taint_vals;
	}

	protected function resolveVariableTaint(Expr\Variable $var){
		if(InputSources::isInputVariable($var)){
			return $this->createTaintResult(Annotation::TAINTED);
		}else{
			$varName = $var->name;
			if($varName instanceof Expr){
				return $this->createTaintResult(Annotation::UNKNOWN);
			}
			$varTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($var->environment);
			if(!isset($varTaintEnv)){
				$assignEnv = $this->getVariableAssignmentEnvironment($var);
				$varTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($assignEnv);
			}
			if(isset($varTaintEnv)){
				return $varTaintEnv->getTaintResult($varName);
			}
			return $this->traceVariableTaint($var);
		}
	}

	protected function traceVariableTaint(Expr\Variable $var){
		$varTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($var->environment);
		$varName = $var->name;
		$assignEnv = $this->getVariableAssignmentEnvironment($var);
		if(!isset($varTaintEnv)){
			$varTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($assignEnv);
		}
		if(isset($varTaintEnv)){
			$taintResult = $varTaintEnv->getTaintResult($var->name);
		}
		if(empty($taintResult) || $taintResult->getTaint() == Annotation::UNASSIGNED){
			$assign = $assignEnv->resolveVariable($varName);
			return $this->resolveExprTaint($assign->expr);
		}

		return $taintResult;
	}

	public function resolveExprTaint(Expr $exp){
		if($exp instanceof Node\Scalar){
			return $this->createTaintResult(Annotation::SAFE);
		}else if (($exp instanceof Expr\ClassConstFetch) || ($exp instanceof
				Expr\ConstFetch)){
			return $this->createTaintResult(Annotation::SAFE);
		}else if($exp instanceof Expr\Variable) {
			return $this->resolveVariableTaint($exp);
		}else if($exp instanceof Expr\PreInc || $exp instanceof Expr\PreDec || $exp instanceof Expr\PostInc || $exp instanceof Expr\PostDec){
			$var = $exp->var;
			return $this->resolveVariableTaint($var);
		}else if($exp instanceof Expr\UnaryMinus || $exp instanceof Expr\UnaryPlus){
			$var = $exp->expr;
			return $this->resolveVariableTaint($var);
		}else if($exp instanceof Expr\PropertyFetch){
			$var = $exp->var;
			return $this->resolveVariableTaint($var);
		}else if($exp instanceof Expr\BinaryOp){
			return $this->resolveBinaryOpTaint($exp);
		}else if($exp instanceof Expr\Array_){
			return $this->resolveAndMergeTaintOfExprsInArray($exp);
		}else if($exp instanceof Expr\ArrayDimFetch){
			return $this->resolveArrayFieldTaint($exp);
		}else if($exp instanceof Expr\StaticPropertyFetch){
			return $this->resolveClassPropertyTaint($exp);
		}else if($exp instanceof Expr\FuncCall){
			return $this->resolveFuncResultTaint($exp);
		}else if($exp instanceof Expr\MethodCall){
			return $this->resolveMethodResultTaint($exp);
		}else if($exp instanceof Expr\Ternary){
			return $this->resolveTernaryTaint($exp);
		}else if($exp instanceof Expr\Eval_){
			return $this->resolveExprTaint($exp->expr);
		}else{
			//Other expressions we will not handle.
			return $this->createTaintResult(Annotation::UNKNOWN);
		}
	}

	protected function resolveMethodResultTaint(Expr\MethodCall $exp){
		//Method stub
		return $this->createTaintResult(Annotation::UNKNOWN);
	}

	protected function resolveFuncResultTaint(Expr\FuncCall $exp){
		if(InputSources::isInputReadFuncCall($exp)){
			return $this->createTaintResult(Annotation::TAINTED);
		}
		$func_name = $exp->name;
		if($func_name instanceof Expr){
			return $this->createTaintResult(Annotation::UNKNOWN);
		}

		if(SanitisingFunctions::isGeneralSanitisingFunction($func_name)||
			SanitisingFunctions::isSanitisingReverseFunction($func_name)){
			return $this->resolveSanitisationFuncCall($exp, $func_name);
		}else{
			//TODO:
			$func_analyser = FunctionAnalyser::getFunctionAnalyser($exp->environment, $func_name);
			$analysis_res = $func_analyser->analyseFunctionCall($exp->args);
			return $analysis_res;
		}
	}

	protected function resolveSanitisationFuncCall(Expr\FuncCall $exp, $func_name){
		$func_args = $exp->args;
		$results = array();
		foreach($func_args as $arg){
			$exp = $arg->value;
			$taintRes = $this->resolveExprTaint($exp);
			if(SanitisingFunctions::isGeneralSanitisingFunction($func_name)){
				$taintRes->addSanitisingFunction($func_name);
			}else if(SanitisingFunctions::isSanitisingReverseFunction($func_name)){
				$reverse_func = SanitisingFunctions::getAffectedSanitiser($func_name);
				$original = $taintRes->getSanitisingFunctions();
				$new_list = array_diff($original, array($reverse_func));
				$taintRes->setSanitisingFunctions($new_list);
			}
			$results[] = $taintRes;
		}
		return $this->mergeAnalysisResults($results);
	}

	protected function resolveClassPropertyTaint(Expr\StaticPropertyFetch $exp){
		$classEnv = $exp->environment->resolveClass($exp->class);
		$varName = $exp->name;
		if($varName instanceof Expr){
			return $this->createTaintResult(Annotation::UNKNOWN);
		}
		$varAssignEnv = $classEnv->resolveVariable($varName);
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($varAssignEnv);
		return $taintEnv->getTaintResult($varName);
	}

	protected function resolveAndMergeTaintOfExprsInArray(Expr\Array_ $arr){
		$taintResults = $this->resolveTaintOfExprsInArray($arr);

		return $this->mergeAnalysisResults($taintResults);
	}

	protected function mergeAnalysisResults(array $results){
		$mergeResult = $this->createTaintResult(Annotation::UNASSIGNED);
		foreach($results as $result){
			$mergeResult->merge($result);
		}
		return $result;
	}

	protected function resolveBinaryOpTaint(Expr\BinaryOp $exp){
		$left = $exp->left;
		$right = $exp->right;
		$left_taint = $this->resolveExprTaint($left);
		$right_taint = $this->resolveExprTaint($right);
		assert($left_taint != NULL);
		assert($right_taint !=NULL);
		return $this->mergeAnalysisResults(array($left_taint, $right_taint));
	}

	protected function resolveTernaryTaint(Expr\Ternary $exp){
		$if = $exp->if;
		$else = $exp->else;
		$if_taint = $this->resolveExprTaint($if);
		$else_taint = $this->resolveExprTaint($else);
		return $this->mergeAnalysisResults(array($if_taint, $else_taint));
	}

	protected function resolveArrayFieldTaint(Expr\ArrayDimFetch $exp){
		//Treats all the fields in an array as a single entity
		$array_var = $exp->var;
		$array_var_name = $array_var->name;

		if(!($array_var_name instanceof Expr) && InputSources::isInputVariableName($array_var_name)){
			return $this->createTaintResult(Annotation::TAINTED);
		}
		$taint = $this->resolveExprTaint($array_var);
		return $taint;
	}

	protected function resolveStmtTaintEnvironment(Stmt $exp, TaintEnvironment $taintEnv){
		assert($taintEnv != null);
		if($exp instanceof Stmt\If_){
			return $this->resolveIfStatementTaints($exp, $taintEnv);
		}else if($exp instanceof Stmt\Else_){
			$items = $exp->stmts;
		}else if($exp instanceof Stmt\ElseIf_){
			$items = $exp->stmts;
		}else if($exp instanceof Stmt\Do_){
			$items = $exp->stmts;
		}else if($exp instanceof Stmt\For_){
			$items = $exp->stmts;
		}else if($exp instanceof Stmt\Foreach_){
			$items = $exp->stmts;
		}else if($exp instanceof Stmt\While_){
			$items = $exp->stmts;
		}
		if(!empty($items)){
			$result = $this->resolveTaintForArrayOfStatements($items, $taintEnv);
			return $result;
		}else{
			return $taintEnv;
		}
	}

	protected function resolveIfStatementTaints(Stmt\If_ $stmt, TaintEnvironment $taintEnv){
		$if_items = $stmt->stmts;
		$if_res = $this->resolveTaintForArrayOfStatements($if_items, $taintEnv);
		$else = $stmt->else;
		if(isset($else)){
			$else_res = $this->resolveStmtTaintEnvironment($else, $taintEnv);
			$if_res->mergeTaintEnvironment($else_res);
			return $if_res;
		}else{
			return $if_res;
		}
	}

	protected function resolveTaintForArrayOfStatements($nodes, TaintEnvironment $taintEnv){
		$envResult = $taintEnv->copy();
		foreach($nodes as $node){
			$nodeTaintEnv = $this->analyse($node, $taintEnv);
			$envResult->mergeTaintEnvironment($nodeTaintEnv);
		}
		return $envResult;
	}

	protected function createTaintResult($taint, $sanitising_funcs = array()){
		return new TaintResult($taint, $sanitising_funcs);
	}
} 