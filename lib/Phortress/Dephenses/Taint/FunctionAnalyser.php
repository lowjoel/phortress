<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 15/11/14
 * Time: 10:58 PM
 */

namespace Phortress\Dephenses\Taint;

use Phortress\Dephenses\Engine\VulnerabilityReporter;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
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

	protected $returnStmtTaintResults = array();

	/**
	 * The parameters to the function
	 */
	protected $params = array();

	/**
	 * The statements in the function
	 */
	protected $functionStmts;

	protected $sinkFunctionCalls = array();

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
		$this->returnStmtTaintResults = $funcNodeAnalyser->getReturnTaintResult();
		$this->sinkFunctionCalls = $funcNodeAnalyser->getSinkFunctionCalls();
	}

	/**
	 * Takes in an array of argument Taint Results
	 * Returns an array containing taint value of the value returned by the function,
	 * and the array of sanitising functions applied
	 */
	public function analyseFunctionCall($argMappings, VulnerabilityReporter $reporter = null){
		$paramTaintMappings = $this->getParametersToTaintResultMappings($argMappings);
		$result = new TaintResult(Annotation::UNASSIGNED);
		foreach($this->returnStmts as $retStmt){
			$retStmtResult = $this->analyseArgumentsEffectOnReturnStmt($paramTaintMappings, $retStmt);
			$result->merge($retStmtResult);
		}
		if(!empty($reporter)){
			$this->checkSinkFunctionCalls($paramTaintMappings, $reporter);
		}
		return $result;
	}

	private function checkSinkFunctionCalls($paramMappings, VulnerabilityReporter $reporter){
		foreach($this->sinkFunctionCalls as $lineNum => $funcCallArr){
			$argTaintMappings = $funcCallArr[0];
			$funcCall = $funcCallArr[1];
			$argTaints = array();
			foreach($argTaintMappings as $argTaint){
				$argTaint = $argTaint->copy();
				foreach($paramMappings as $paramName => $taint){
					if($argTaint->isAffectingParameter($paramName)){
						var_dump($taint->getTaint());
						$argTaint->merge($taint);
					}
					$argTaints[] = $argTaint;
				}
			}
			$reporter->runNodeVulnerabilityChecks($funcCall, $argTaints);
		}
	}

	private function analyseArgumentsEffectOnReturnStmt($argTaints, Stmt\Return_ $return){
		$retTaint = $this->returnStmtTaintResults[$return->getLine()];
		return $this->mergeTaintResultsWithWithParameterTaints($retTaint, $argTaints);
	}

	private function mergeTaintResultsWithWithParameterTaints($resTaint, $argTaints){
		if(empty($resTaint)){
			return new TaintResult(Annotation::UNASSIGNED);
		}
		$taintResult = new TaintResult($resTaint->getTaint(), $resTaint->getSanitisingFunctions());
		foreach($argTaints as $paramName => $taint){
			if($resTaint->isAffectingParameter($paramName)){
				$taintResult->merge($taint);
			}
		}
		return $taintResult;
	}

	private function getParametersToTaintResultMappings($argTaints){
		$mappings = array();
		for($i = 0; $i<count($argTaints);$i++){
			$param = $this->params[$i];
			$mappings[$param->name] = $argTaints[$i];
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