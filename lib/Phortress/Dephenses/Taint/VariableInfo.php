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
	protected $sanitising_functions;
	protected $taint_value;
	protected $definition;

	public function __construct(Expr\Variable $var = null, $taint = Annotation::UNASSIGNED,
	                            $sanitising = array()){
		$this->variable = $var;
		$this->taint_value = $taint;
		$this->sanitising_functions = $sanitising;
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

	public function getTaint(){
		return $this->taint_value;
	}

	public function setTaint($taint){
		$this->taint_value = $taint;
	}

	public function getSanitisingFunctions(){
		return $this->sanitising_functions;
	}

	public function setSanitisingFunctions($funcs){
		$this->sanitising_functions = $funcs;
	}

	public function getDefinition(){
		return $this->definition;
	}

	public function setDefinition($def){
		$this->definition = $def;
	}

	public static function mergeVariableInfo(VariableInfo $var1, VariableInfo $var2){
		$taint = self::mergeTaintValues($var1, $var2);
		$san = self::mergeSanitisingFunctions($var1, $var2);
		$varInfo = new VariableInfo($var1->getVariable(), $taint, $san);
		$varInfo->setDefinition($var2->getDefinition());
		return $varInfo;
	}

	public static function mergeTaintValues(VariableInfo $var1, VariableInfo $var2){
		return max($var1->getTaint(), $var2->getTaint());
	}

	public static function mergeSanitisingFunctions(VariableInfo $var1, VariableInfo $var2){
		$sanitising1 = $var1->getSanitisingFunctions();
		$sanitising2 = $var2->getSanitisingFunctions();
		return array_intersect($sanitising1, $sanitising2);
	}


	public static function mergeVariables($vars){
		$merged = array();
		//This takes in an array of the $item is of the form: array(array(var name => VariableInfo))
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