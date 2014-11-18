<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 15/11/14
 * Time: 10:58 PM
 */

namespace Phortress\Dephenses\Taint;


use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use Phortress\Environment;

class FunctionAnalyser2{
	/**
	 * Return statements and the variables they are dependent on.
	 * array(Stmt line number => array(variable_name)
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
	 * array(variable name => VariableInfo)
	 */
	protected $variables = array();


	protected $unresolved_variables = array();

	/**
	 * Environment where the function was defined
	 */
	protected $environment;

	protected $function;

	public function __construct(Stmt\Function_ $function) {

		//For now we do not handle dynamic function names;
		$this->variables = array();
		$this->unresolved_variables = array();
		$this->function = $function;
		$this->functionStmts = $this->function->stmts;
		$this->params = $this->function->params;
	}

	public static function getFunctionAnalyser(Environment $env, $functionName){
		assert(!($functionName instanceof Expr));
		assert(!empty($env));
		$func_def = $env->resolveFunction($functionName);
		$analyser = $func_def->analyser;
		if(empty($analyser)){
			$analyser = new FunctionAnalyser2($func_def);
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

	}

	private function getVariableDetails(Variable $var){

	}

	/**
	 * Runs through all the statements in the function to gather information about the variables
	 * in the function, namely
	 */
	public function analyseFunction(){

	}

	private function getParametersToTaintResultMappings($args){
		$nodeAnalyser = new NodeAnalyser();
		$mappings = array();
		for($i = 0; $i<count($args);$i++){
			$param = $this->params[$i];
			$arg_val = $args[$i]->value;
			$result = $nodeAnalyser->resolveExprTaint($arg_val);
			$mappings[$param->name] = $result;
		}
		return $mappings;
	}

	private function analyseFunctionArgumentsEffect($args, $return){
		$taint_mappings = $this->getParametersToTaintResultMappings($args);

		foreach($return as $var_name => $var_info){

		}
		//taint_val might be UNASSIGNED if the return values trace to a scalar.

	}

	private function traceStatementVariables(Stmt $stmt){
		if($stmt instanceof Stmt\If_){

		}else if($stmt instanceof Stmt\Else_){
			$items = $stmt->stmts;

		}else if($stmt instanceof Stmt\ElseIf_){
			$items = $stmt->stmts;

		}else if($stmt instanceof Stmt\Do_){
			$items = $stmt->stmts;

		}else if($stmt instanceof Stmt\For_){
			$items = $stmt->stmts;

		}else if($stmt instanceof Stmt\Foreach_){
			$items = $stmt->stmts;

		}else if($stmt instanceof Stmt\While_){
			$items = $stmt->stmts;

		}else{
			return array();
		}
	}

	private function traceExpressionVariables(Expr $exp){
		if($exp instanceof Scalar){
			return array();
		}else if($exp instanceof Assign){

		}else if($exp instanceof AssignOp){

		}else if ($exp instanceof Variable) {

		}else if(($exp instanceof ClassConstFetch) || ($exp instanceof ConstFetch)){
			return array();
		}else if($exp instanceof PreInc || $exp instanceof PreDec || $exp instanceof PostInc
			|| $exp instanceof PostDec){

		}else if($exp instanceof BinaryOp){

		}else if($exp instanceof UnaryMinus || $exp instanceof UnaryPlus){

		}else if($exp instanceof Array_){

		}else if($exp instanceof ArrayDimFetch){
			//For now treat all array dimension fields as one

		}else if($exp instanceof PropertyFetch){

		}else if($exp instanceof StaticPropertyFetch){
			//TODO:
			return array();
		}else if($exp instanceof FuncCall){

		}else if($exp instanceof MethodCall){

		}else if($exp instanceof Ternary){
			//If-else block

		}else if($exp instanceof Eval_){
			//TODO:

		}else{
			//Other expressions we will not handle.
			return array();
		}
	}

	private function traceVariable(Variable $var){
		$name = $var->name;
		$var_details = $this->getVariableDetails($var);
		if($name instanceof Expr){
			return $var_details;
		}else{
			if(InputSources::isInputVariable($var)){
				$var_details->setTaint(Annotation::TAINTED);
				$details_ret = array($name => $var_details);
				return $details_ret;
			}

		}

	}
} 