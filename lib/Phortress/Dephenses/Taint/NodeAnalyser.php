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

	public static function resolveAssignmentTaintEnvironment(Expr $exp){
		if($exp instanceof Expr\Assign){

		}else if($exp instanceof Expr\AssignOp){

		}
	}

	public static function resolveExprTaint(Expr $exp){
		if($exp instanceof Node\Scalar){

		}else if($exp instanceof Expr\Variable) {

		}else if (($exp instanceof Expr\ClassConstFetch) || ($exp instanceof
				Expr\ConstFetch)){

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

		}
	}

	public static function resolveStmtTaintEnvironment(Stmt $exp){
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