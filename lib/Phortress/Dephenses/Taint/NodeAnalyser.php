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
	public static function analyse(Node $node){
		resolveAssignmentTaintEnvironment($node);
		if($node instanceof Stmt){
			self::resolveStmtTaintEnvironment($node);
		}
	}

	protected  static function resolveAssignmentTaintEnvironment(Expr $exp){
		if($exp instanceof Expr\Assign){
			self::resolveTaintEnvironmentForAssign($exp);
		}else if($exp instanceof Expr\AssignOp){
			self::resolveTaintEnvironmentForAssignOp($exp);
		}
	}

	protected static function resolveTaintEnvironmentForAssignOp(Expr\AssignOp $assignOp){
		$var = $assignOp->var;
		$varName = $var->name;
		if($varName instanceof Expr){
			//Cannot resolve variable variables.
			return;
		}

		$exp = $assignOp->expr;

		$assignEnv = self::getVariableAssignmentEnvironment($var);
		$varEnv = $var->environment;

		$assignEnvTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($assignEnv);
		$assignEnvTaintResult = $assignEnvTaintEnv->getTaintResult($varName);

		$expTaint = self::resolveExprTaint($exp);
		assert(isset($expTaint));
		$expTaint->merge($assignEnvTaintResult);

		self::mergeVariableTaintEnvironment($varEnv, $varName, $expTaint);
	}

	protected static function resolveTaintEnvironmentForAssign(Expr\Assign $assign){
		$var = $assign->var;
		$varName = $var->name;
		if($varName instanceof Expr){
			//Cannot resolve variable variables.
			return;
		}
		$exp = $assign->expr;

		if($var instanceof Expr\List_){
			self::resolveListAssignment($assign);
		}else{
			$environment = $assign->environment;
			$expTaint = self::resolveExprTaint($exp);
			self::setVariableTaintEnvironment($environment, $varName, $expTaint);
		}
	}

	private static function setVariableTaintEnvironment(Environment $env, $varName,
	                                                    TaintResult $taintRes){
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($env);
		if(!isset($taintEnv)){
			$taintEnv = new TaintEnvironment($env);
		}
		$taintEnv->setTaintResult($varName, $taintRes);
		TaintEnvironment::setTaintEnvironmentForEnvironment($env, $taintEnv);
	}

	private static function mergeVariableTaintEnvironment(Environment $env, $varName,
	                                                    TaintResult $taintRes){
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($env);
		if(!isset($taintEnv)){
			$taintEnv = new TaintEnvironment($env);
		}
		$taintEnv->mergeAndSetTaintResult($varName, $taintRes);
		TaintEnvironment::setTaintEnvironmentForEnvironment($env, $taintEnv);
	}

	private static function getVariableAssignmentEnvironment(Expr\Variable $var){
		$varName = $var->name;
		if($var instanceof Expr){
			return $var->environment;
		}else{
			$resolution = $var->environment->resolveVariable($varName);
			return $resolution->environment;
		}
	}

	protected  static function resolveListAssignment(Expr\Assign $assign){
		assert($assign->var instanceof Expr\List_);
		$list_of_vars = $assign->var->vars;
		$exp = $assign->expr;

		if($exp instanceof Expr\Array_){
			$taint_vals = self::resolveTaintOfExprsInArray($exp);

		}else{
			$taint_vals = array();
			$taintRes = self::resolveExprTaint($exp);
			for($i = 0; $i < count(list_of_vars); $i++){
				$taint_vals[$i] = $taintRes;
			}
		}
		for($i = 0; $i < count(list_of_vars); $i++){
			$var = $list_of_vars[$i];
			$varName = $var->name;

			$env = self::getVariableAssignmentEnvironment($var);
			assert(isset($env));
			self::setVariableTaintEnvironment($env, $varName, $taint_vals[$i]);
		}


	}

	/**
	 * Takes in an array of Nodes and resolves their taint values if they are are variables.
	 * Returns an array containing the taint value of each item in the array.
	 */
	protected static function resolveTaintOfExprsInArray(Expr\Cast\Array_ $array){
		$arr_items = $array->items;
		$taint_vals = array();
		foreach($arr_items as $item){
			$exp = $item->value;
			$taint_val = Annotation::UNKNOWN;
			if($exp instanceof Expr){
				$taint_val = self::resolveExprTaint($exp);
			}
			$taint_vals[] = $taint_val;
		}
		return $taint_vals;
	}

	protected static function resolveVariableTaint(Expr\Variable $var){
		if(InputSources::isInputVariable($var)){
			return TaintResult(Annotation::TAINTED);
		}else{
			$varName = $var->name;
			if($varName instanceof Expr){
				return TaintResult(Annotation::UNKNOWN);
			}
			$varTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($var->environment);
			if(!isset($varTaintEnv)){
				$assignEnv = self::getVariableAssignmentEnvironment($var);
				$varTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($assignEnv);
			}
			return $varTaintEnv->getTaintResult($varName);
		}
	}

	protected  static function resolveExprTaint(Expr $exp){
		if($exp instanceof Node\Scalar){
			return new TaintResult(Annotation::SAFE);
		}else if (($exp instanceof Expr\ClassConstFetch) || ($exp instanceof
				Expr\ConstFetch)){
			return new TaintResult(Annotation::SAFE);
		}else if($exp instanceof Expr\Variable) {
			return self::resolveVariableTaint($exp);
		}else if($exp instanceof Expr\PreInc || $exp instanceof Expr\PreDec || $exp instanceof Expr\PostInc || $exp instanceof Expr\PostDec){
			$var = $exp->var;
			return self::resolveVariableTaint($var);
		}else if($exp instanceof Expr\UnaryMinus || $exp instanceof Expr\UnaryPlus){
			$var = $exp->expr;
			return self::resolveVariableTaint($var);
		}else if($exp instanceof Expr\PropertyFetch){
			$var = $exp->var;
			return self::resolveVariableTaint($var);
		}else if($exp instanceof Expr\BinaryOp){
			return self::resolveBinaryOpTaint($exp);
		}else if($exp instanceof Expr\Array_){
			return self::resolveAndMergeTaintOfExprsInArray($exp);
		}else if($exp instanceof Expr\ArrayDimFetch){
			return self::resolveArrayFieldTaint($exp);
		}else if($exp instanceof Expr\StaticPropertyFetch){
			return self::resolveClassPropertyTaint($exp);
		}else if($exp instanceof Expr\FuncCall){
			return self::resolveFuncResultTaint($exp);
		}else if($exp instanceof Expr\MethodCall){
			return self::resolveMethodResultTaint($exp);
		}else if($exp instanceof Expr\Ternary){
			return self::resolveTernaryTaint($exp);
		}else if($exp instanceof Expr\Eval_){
			return self::resolveExprTaint($exp->expr);
		}else{
			//Other expressions we will not handle.
			return new TaintResult(Annotation::UNKNOWN);
		}
	}

	protected  static function resolveMethodResultTaint(Expr\MethodCall $exp){
		return new TaintResult(Annotation::UNKNOWN);
	}

	protected  static function resolveFuncResultTaint(Expr\FuncCall $exp){
		if(InputSources::isInputReadFuncCall($exp)){
			return new TaintResult(Annotation::TAINTED);
		}
		$func_name = $exp->name;
		if($func_name instanceof Expr){
			return new TaintEnvironment(Annotation::UNKNOWN);
		}

		if(SanitisingFunctions::isGeneralSanitisingFunction($func_name)||
			SanitisingFunctions::isSanitisingReverseFunction($func_name)){
			return self::resolveSanitisationFuncCall($exp, $func_name);
		}else{
			//TODO:
		}
	}

	protected static function resolveSanitisationFuncCall(Expr\FuncCall $exp, $func_name){
		$func_args = $exp->args;
		$results = array();
		foreach($func_args as $arg){
			$exp = $arg->value;
			$taintRes = self::resolveExprTaint($exp);
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
		return self::mergeTaintResults($results);
	}

	protected static function resolveClassPropertyTaint(Expr\StaticPropertyFetch $exp){
		$classEnv = $exp->environment->resolveClass($exp->class);
		$varName = $exp->name;
		if($varName instanceof Expr){
			return new TaintResult(Annotation::UNKNOWN);
		}
		$varAssignEnv = $classEnv->resolveVariable($varName);
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($varAssignEnv);
		return $taintEnv->getTaintResult($varName);
	}
	protected static function resolveAndMergeTaintOfExprsInArray(Expr\Array_ $arr){
		$taintResults = self::resolveTaintOfExprsInArray($arr);

		return self::mergeTaintResults($taintResults);
	}

	protected static function mergeTaintResults(array $results){
		$mergeResult = new TaintResult(Annotation::UNASSIGNED);
		foreach($results as $result){
			$mergeResult->merge($result);
		}
		return $result;
	}

	protected static function resolveBinaryOpTaint(Expr\BinaryOp $exp){
		$left = $exp->left;
		$right = $exp->right;
		$left_taint = self::resolveExprTaint($left);
		$right_taint = self::resolveExprTaint($right);
		assert($left_taint != NULL);
		assert($right_taint !=NULL);
		return TaintResult::mergeTaintResults($left_taint, $right_taint);
	}

	protected static function resolveTernaryTaint(Expr\Ternary $exp){
		$if = $exp->if;
		$else = $exp->else;
		$if_taint = self::resolveExprTaint($if);
		$else_taint = self::resolveExprTaint($else);
		return TaintResult::mergeTaintResults($if_taint, $else_taint);
	}

	protected  static function resolveArrayFieldTaint(Expr\ArrayDimFetch $exp){
		//Treats all the fields in an array as a single entity
		$array_var = $exp->var;
		$array_var_name = $array_var->name;

		if(!($array_var_name instanceof Expr) && InputSources::isInputVariableName($array_var_name)){
			return new TaintResult(Annotation::TAINTED);
		}
		$taint = self::resolveExprTaint($array_var);
		return $taint;
	}

	protected  static function resolveStmtTaintEnvironment(Stmt $exp){
		if($exp instanceof Stmt\If_){

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
	}


} 