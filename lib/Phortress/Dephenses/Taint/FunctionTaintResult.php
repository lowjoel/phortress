<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 13/11/14
 * Time: 6:09 PM
 */

namespace Phortress\Dephenses\Taint;

use PhpParser\Node\Expr;
class FunctionTaintResult extends TaintResult{
	/**
	 * Array of Variables (function parameters) which will affect the variable's taint value.
	 */
	protected $affecting_params = array();

	public function __construct($taint = Annotation::UNASSIGNED, $sanitising = array(),
	                            $affecting_params = array()){
		parent::__construct($taint, $sanitising);
		$this->affecting_params = $affecting_params;
	}

	public function getAffectingParameters(){
		return $this->affecting_params;
	}

	public function setAffectingParameters($params){
		$this->affecting_params = $params;
	}

	public function isAffectingParameter($paramName){
		return in_array($paramName, $this->affecting_params);
	}

	public function addAffectingParameter($paramName){
		$this->affecting_params = array_merge($this->affecting_params, array($paramName));
	}

	public function merge($info){
		parent::merge($info);
		if($info instanceof FunctionTaintResult){
			$other_params = $info->getAffectingParameters();
			$params = array_merge($this->affecting_params, $other_params);
			$this->affecting_params = $params;
		}
	}

	public static function mergeFunctionTaintResults(FunctionTaintResult $var1, FunctionTaintResult $var2){
		$mergedResult = TaintResult::mergeTaintResults($var1, $var2);
		$varInfo = new FunctionTaintResult($mergedResult->getTaint(), $mergedResult->getSanitisingFunctions());

		$params = array_merge($var1->getAffectingParameters(), $var2->getAffectingParameters());
		$varInfo->setAffectingParameters($params);
		return $varInfo;
	}

	public function copy(){
		return new FunctionTaintResult($this->getTaint(), $this->getSanitisingFunctions(),
			$this->affecting_params);
	}
} 