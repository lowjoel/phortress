<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 13/11/14
 * Time: 6:09 PM
 */

namespace Phortress\Dephenses\Taint;

use PhpParser\Node\Expr;
class VariableInfo extends TaintResult{
	protected $variable;
	/**
	 * Array of Variables (function parameters) which will affect the variable's taint value.
	 */
	protected $affecting_params = array();

	public function __construct(Expr\Variable $var = null, $taint = Annotation::UNASSIGNED,
	                            $sanitising = array()){
		parent::__construct($taint, $sanitising);
		$this->variable = $var;
	}

	public function isEmpty(){
		return $this->variable === null;
	}

	public function getVariable(){
		return $this->variable;
	}

	public function setVariable(Expr\Variable $var){
		$this->variable = $var;
	}

	public function getDefinition(){
		return $this->variable->environment->resolveVariable($this->variable->name);
	}

	public function getAffectingParameters(){
		return $this->affecting_params;
	}

	public function setAffectingParameters($params){
		$this->affecting_params = $params;
	}

	public function merge($info){
		parent::merge($info);
		assert($info instanceof VariableInfo);
		$other_params = $info->getAffectingParameters();
		$params = array_merge($this->affecting_params, $other_params);
		$this->affecting_params = $params;
	}

	public static function mergeVariableInfo(VariableInfo $var1, VariableInfo $var2){
		$mergedResult = TaintResult::mergeTaintResults($var1, $var2);
		$varInfo = new VariableInfo($var1->getVariable(), $mergedResult->getTaint(),
		$mergedResult->getSanitisingFunctions());

		$params = array_merge($var1->getAffectingParameters(), $var2->getAffectingParameters());
		$varInfo->setAffectingParameters($params);
		return $varInfo;
	}

	/**
	 * Takes in of the form: array(array(var name => VariableInfo))
	 * Flattens it to a single array mapping a variable's name to the variable's corresponding
	 * VariableInfo object. In otherwords, the return array should be of the form:
	 * array(variable_name => VariableInfo)
	 */
	public static function mergeVariables($vars){
		$merged = array();
		foreach($vars as $item){
			foreach($item as $var_name => $varInfo){
				if(empty($varInfo)){
					continue;
				}
				if(!array_key_exists($var_name, $merged)){
					$merged[$var_name] = $varInfo;
				}else{
					$existing = $merged[$var_name];
					$merged[$var_name] = self::mergeVariableInfo($existing, $varInfo);
				}
			}
		}
		return $merged;
	}
} 