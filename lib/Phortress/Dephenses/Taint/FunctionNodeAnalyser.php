<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 18/11/14
 * Time: 8:56 AM
 */

namespace Phortress\Dephenses\Taint;


use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Return_;

class FunctionNodeAnalyser extends NodeAnalyser{
	protected $functionParams = array();
	protected $returnResults = array();

	/**
	 * array(int lineNumber => FuncCall functionCall)
	 */
	protected $sinkFunctionCalls = array();

	public function __construct($params){
		$this->functionParams = $params;
	}

	protected function createTaintResult($taint, $sanitising_funcs = array()){
		return new FunctionTaintResult($taint, $sanitising_funcs);
	}

	public function getSinkFunctionCalls(){
		return $this->sinkFunctionCalls;
	}

	private function isFunctionParameter($name){
		foreach($this->functionParams as $param){
			if($param->name === $name){
				return true;
			}
		}
		return false;
	}

	private function addAffectingParameterToAnalysisResult(FunctionTaintResult $result, $name){
		if(!empty($name) && $this->isFunctionParameter($name)){
			$result->addAffectingParameter($name);
		}
	}

	protected function resolveVariableTaint(Variable $var){
		$result = parent::resolveVariableTaint($var);
		if(empty($result)){
			$result = $this->createTaintResult(Annotation::UNASSIGNED);
		}
		$this->addAffectingParameterToAnalysisResult($result, $var->name);
		return $result;
	}

	protected function resolveFuncResultTaint(FuncCall $exp){
		$args = $exp->args;
		if(Sinks::isSinkFunction($exp)){
			$argTaints = $this->getArgumentsTaintValuesForAnalysis($args);
			$this->sinkFunctionCalls[$exp->getLine()] = array($argTaints, $exp);
			return;
		}
		$result = parent::resolveFuncResultTaint($exp);
		foreach($args as $arg){
			$argExpName = $arg->value->name;
			$this->addAffectingParameterToAnalysisResult($result, $argExpName);
		}
		return $result;
	}

	protected function runSinkExpressionCheck(Expr $exp){

	}

	protected function runEchoStatementCheck(Stmt $exp){

	}

	protected function resolveBinaryOpTaint(BinaryOp $exp){
		$result = parent::resolveBinaryOpTaint($exp);
		if(empty($result)){
			$result = $this->createTaintResult(Annotation::UNASSIGNED);
		}
		$leftName = $exp->left->name;
		$rightName = $exp->right->name;
		$this->addAffectingParameterToAnalysisResult($result, $leftName);
		$this->addAffectingParameterToAnalysisResult($result, $rightName);
		return $result;
	}


	protected function resolveArrayFieldTaint(ArrayDimFetch $exp){
		$array_var_name = $exp->var->name;
		$result = parent::resolveArrayFieldTaint($exp);
		if(empty($result)){
			$result = $this->createTaintResult(Annotation::UNASSIGNED);
		}
		$this->addAffectingParameterToAnalysisResult($result, $array_var_name);
		return $result;
	}

	protected function resolveStmtTaintEnvironment(Stmt $exp, TaintEnvironment $taintEnv){
		if($exp instanceof Return_){
			return $this->resolveReturnStatementTaintEnvironment($exp, $taintEnv);

		}else{
			return parent::resolveStmtTaintEnvironment($exp, $taintEnv);
		}
	}

	protected function resolveReturnStatementTaintEnvironment(Return_ $exp,
	                                                          TaintEnvironment $taintEnv){
		$retExp = $exp->expr;
		TaintEnvironment::updateTaintEnvironmentForEnvironment($retExp->environment, $taintEnv);
		$retExpTaint = $this->resolveExprTaint($retExp);
		$retEnv = $taintEnv->copy();
		$retEnv->setTaintResult($exp->getLine(), $retExpTaint);
		$this->addReturnTaintResult($exp, $retExpTaint);
		return $retEnv;
	}
	private function addReturnTaintResult(Return_ $ret, FunctionTaintResult $result){
		$this->returnResults[$ret->getLine()] = $result;
	}

	public function getReturnTaintResult(){
		return $this->returnResults;
	}
} 