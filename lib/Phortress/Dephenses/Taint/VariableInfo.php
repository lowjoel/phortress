<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 13/11/14
 * Time: 6:09 PM
 */

namespace Phortress\Dephenses\Taint;

use PhpParser\Node\Expr;
class VariableInfo {
	protected $variable;
	protected $taintResult;
	protected $definition; //This is derived from the variable's environment. Can probably be
//removed
	/**
	 * Array of Variables (function parameters) which will affect the variable's taint value.
	 */
	protected $affecting_params;

	public function __construct(Expr\Variable $var = null, $taint = Annotation::UNASSIGNED,
	                            $sanitising = array()){
		$this->variable = $var;
		$this->taintResult = new TaintResult($taint, $sanitising);
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

	public function getTaintResult(){
		return $this->taintResult;
	}

	public function setTaintResult($result){
		$this->taintResult = $result;
	}

	public function getTaint(){
		return $this->taintResult->getAnnotation();
	}

	public function setTaint($taint){
		$this->taintResult->setAnnotation($taint);
	}

	public function getSanitisingFunctions(){
		return $this->taintResult->getSanitisingFunctions();
	}

	public function setSanitisingFunctions($funcs){
		$this->taintResult->setSanitisingFunctions($funcs);
	}

	public function getDefinition(){
		return $this->definition;
	}

	public function setDefinition($def){
		$this->definition = $def;
	}

	public static function mergeVariableInfo(VariableInfo $var1, VariableInfo $var2){
		$mergedResult = TaintResult::mergeTaintResults($var1->getTaintResult(), $var2->getTaintResult());
		$varInfo = new VariableInfo($var1->getVariable());
		$varInfo->setDefinition($var2->getDefinition());
		$varInfo->setTaintResult($mergedResult);
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