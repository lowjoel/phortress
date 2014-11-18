<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 18/11/14
 * Time: 8:56 AM
 */

namespace Phortress\Dephenses\Taint;


use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;

class FunctionNodeAnalyser extends NodeAnalyser{
	protected $functionParams = array();

	public function __construct($params){
		$this->functionParams = $params;
	}

	protected function createTaintResult($taint, $sanitising_funcs = array()){
		return new FunctionTaintResult($taint, $sanitising_funcs);
	}

	private function isFunctionParameter($name){
		foreach($this->functionParams as $param){
			if($param->name === $name){
				return true;
			}
		}
		return false;
	}

	private function addAffectingFunctionToAnalysisResult(FunctionTaintResult $result, $name){
		if(!empty($name) && $this->isFunctionParameter($name)){
			$result->addAffectingParameter($name);
		}
	}

	protected function resolveVariableTaint(Variable $var){
		$result = parent::resolveVariableTaint($var);
		$this->addAffectingFunctionToAnalysisResult($result, $var->name);
		return $result;
	}

	protected function resolveFuncResultTaint(FuncCall $exp){
		$result = parent::resolveFuncResultTaint($exp);
		$args = $exp->args;
		foreach($args as $arg){
			$argExpName = $arg->value->name;
			$this->addAffectingFunctionToAnalysisResult($result, $argExpName);
		}
		return $result;
	}

	protected function resolveBinaryOpTaint(BinaryOp $exp){
		$result = parent::resolveBinaryOpTaint($exp);
		$leftName = $exp->left->name;
		$rightName = $exp->right->name;
		$this->addAffectingFunctionToAnalysisResult($result, $leftName);
		$this->addAffectingFunctionToAnalysisResult($result, $rightName);
		return $result;
	}


	protected function resolveArrayFieldTaint(ArrayDimFetch $exp){
		$array_var_name = $exp->var->name;
		$result = parent::resolveArrayFieldTaint($exp);
		$this->addAffectingFunctionToAnalysisResult($result, $array_var_name);
		return $result;
	}


//	protected function mergeAnalysisResults(array $results){
//		$mergeResult = self::createTaintResult(Annotation::UNASSIGNED);
//		foreach($results as $result){
//			$mergeResult->merge($result);
//		}
//		return $result;
//	}
} 