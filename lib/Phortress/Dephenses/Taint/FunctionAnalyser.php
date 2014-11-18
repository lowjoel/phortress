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
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use Phortress\Environment;
use PhpParser\NodeTraverser;

class FunctionAnalyser{
	/**
	 * Return statements in the function
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
	 * Environment where the function was defined
	 */
	protected $environment;

	protected $function;

	public function __construct(Stmt\Function_ $function) {

		//For now we do not handle dynamic function names;
		$this->function = $function;
		$this->functionStmts = $this->function->stmts;
		$this->params = $this->function->params;
		$this->returnStmts = $this->getReturnStatements($this->functionStmts);
		$this->analyseFunction();
	}

	public static function getFunctionAnalyser(Environment $env, Name $functionName){
		assert(!($functionName instanceof Expr));
		assert(!empty($env));
		$func_def = $env->resolveFunction($functionName);
		$analyser = $func_def->analyser;
		if(empty($analyser)){
			$analyser = new FunctionAnalyser($func_def);
			$func_def->analyser = $analyser;
		}
		return $analyser;
	}

	private function analyseFunction(){
		$currentTaintEnv = new TaintEnvironment($this->function->environment);
		$funcNodeAnalyser = new FunctionNodeAnalyser($this->params);
		foreach($this->functionStmts as $statement){
			$nodeTaintEnv = $funcNodeAnalyser->analyse($statement, $currentTaintEnv);
			$currentTaintEnv->updateTaintEnvironment($nodeTaintEnv);
		}
	}

	/**
	 * Takes in an array of Node\Args[]
	 * Returns an array containing taint value of the value returned by the function,
	 * and the array of sanitising functions applied
	 */
	public function analyseFunctionCall($args){
		$argTaintMappings = $this->getParametersToTaintResultMappings($args);
		$result = new TaintResult(Annotation::UNASSIGNED);
		foreach($this->returnStmts as $retStmt){
			$retStmtResult = $this->analyseArgumentsEffectOnReturnStmt($argTaintMappings, $retStmt);
			$result->merge($retStmtResult);
		}
		return $result;
	}

	private function analyseArgumentsEffectOnReturnStmt($argTaints, Stmt\Return_ $return){
		$retExpEnv = $return->expr->environment;
		if(!empty($retExpEnv)){
			$retExpTaintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($retExpEnv);
			$retTaint = $retExpTaintEnv->getTaintResult(FunctionNodeAnalyser::RETURN_STMT_KEY);
			$taintResult = new TaintResult($retTaint->getTaint(), $retTaint->getSanitisingFunctions());
			foreach($argTaints as $paramName => $taint){
				if($retTaint->isAffectingParameter($paramName)){
					$taintResult->merge($taint);
				}
			}
			return $taintResult;
		}else{
			return new TaintResult(Annotation::UNASSIGNED);
		}
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

	private function getReturnStatements(array $stmts){
		$traverser = new NodeTraverser();
		$filter = function($node) {return ($node instanceof Stmt\Return_);};
		$ignore = array('PhpParser\Node\Stmt\Function_');
		$finder = new NodeFinder($filter, $ignore);
		$traverser->addVisitor($finder);
		$traverser->traverse($stmts);
		return $finder->getNodes();
	}
} 