<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 17/11/14
 * Time: 4:17 PM
 */

namespace Phortress\Dephenses\Taint;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

/**
 * Runs the analyser on a node in the parse tree.
 *
 * @param \PhpParser\Node $node
 */
class NodeAnalyser {
	public static function analyse(){

	}

	protected  static function resolveAssignmentTaintEnvironment(Expr $exp){
		if($exp instanceof Expr\Assign){
			self::resolveTaintEnvironmentForAssign($exp);
		}else if($exp instanceof Expr\AssignOp){
			self::resolveTaintEnvironmentForAssignOp($exp);
		}
	}

	protected static function resolveTaintEnvironmentForAssignOp(Expr\AssignOp $assignOp){
		
	}

	protected static function resolveTaintEnvironmentForAssign(Expr\Assign $assign){
		$var = $assign->var;
		$varName = $var->name;
		if($varName instanceof Expr){
			//Cannot resolve variable variables.
			return;
		}
		$exp = $assign->expr;
		$environment = $assign->environment;
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($environment);
		if(!isset($taintEnv)){
			$taintEnv = new TaintEnvironment($environment);
		}
		if($var instanceof Expr\List_){
			self::resolveListAssignment($assign);
		}else{
			$expTaint = self::resolveExprTaint($exp);
			$taintEnv->setTaintResult($varName, $expTaint);
		}
	}

	private static function resolveListAssignment(Expr\Assign $assign){
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

			$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($var->environment);
			if(!isset($taintEnv)){
				$taintEnv = new TaintEnvironment($var->environment);
			}
			$taintEnv->setTaintResult($varName, $taint_vals[$i]);
		}


	}

	protected static function resolveTaintOfExprsInArray(Expr\Cast\Array_ $array_){

	}

	protected  static function resolveExprTaint(Expr $exp){
		if($exp instanceof Node\Scalar){
			return new TaintResult(Annotation::SAFE);
		}else if($exp instanceof Expr\Variable) {

		}else if (($exp instanceof Expr\ClassConstFetch) || ($exp instanceof
				Expr\ConstFetch)){
			return new TaintResult(Annotation::SAFE);
		}else if($exp instanceof Expr\PreInc || $exp instanceof Expr\PreDec || $exp instanceof Expr\PostInc || $exp instanceof Expr\PostDec){

		}else if($exp instanceof Expr\BinaryOp){

		}else if($exp instanceof Expr\UnaryMinus || $exp instanceof Expr\UnaryPlus){

		}else if($exp instanceof Expr\Array_){

		}else if($exp instanceof Expr\ArrayDimFetch){

		}else if($exp instanceof Expr\PropertyFetch){

		}else if($exp instanceof Expr\StaticPropertyFetch){

		}else if($exp instanceof Expr\FuncCall){

		}else if($exp instanceof Expr\MethodCall){

		}else if($exp instanceof Expr\Ternary){

		}else if($exp instanceof Expr\Eval_){

		}else{
			//Other expressions we will not handle.
			return new TaintResult(Annotation::UNKNOWN);
		}
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