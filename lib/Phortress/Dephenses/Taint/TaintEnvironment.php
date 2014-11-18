<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 17/11/14
 * Time: 2:05 PM
 */

namespace Phortress\Dephenses\Taint;


use Phortress\Environment;
use Phortress\GlobalEnvironment;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

/**
 * Contains a mapping of Variable Name to TaintResult
 */
class TaintEnvironment {
	private $taintResults = array();
	private $environment;

	public function __construct(Environment $env = null, $taints = array()){
		if(!is_null($env)){
			$this->environment = $env;
		}else{
			$this->environment = new GlobalEnvironment();
		}
		$this->taintResults = $taints;
		$this->environment->taintEnvironment = $this;
	}

	public function setTaintResult($varName, TaintResult $result){
		assert(!($varName instanceof Expr));
		$this->taintResults[$varName] = $result;
	}

	public function mergeAndSetTaintResult($varName, TaintResult $result){
		$existingResult = $this->getTaintResult($varName);
		if(!empty($existingResult)){
			$existingResult->merge($result);
			$this->setTaintResult($varName, $existingResult);
		}else{
			$this->setTaintResult($varName, $result);
		}
	}

	public static function getTaintEnvironmentFromEnvironment(Environment $env){
		if(property_exists($env, 'taintEnvironment')){
			return $env->taintEnvironment;
		}else{
			return null;
		}
	}

	public static function getTaintEnvironmentFromEnvironmentRecursive(Environment $env){
		if(empty($env)){
			return null;
		}
		$taintEnv = self::getTaintEnvironmentFromEnvironment($env);
		if(!empty($taintEnv)){
			return $taintEnv;
		}else if(self::checkParentPropagationCondition($env)){
			return self::getTaintEnvironmentFromEnvironmentRecursive($env->getParent());
		}else{
			return null;
		}
	}

	public static function setTaintEnvironmentForEnvironment(Environment $env,
	                                                          TaintEnvironment $taintEnv){
		$env->taintEnvironment = $taintEnv;
	}

	public static function mergeTaintEnvironmentForEnvironment(Environment $env,
	                                                           TaintEnvironment $taintEnv){
		$originalEnv = self::getTaintEnvironmentFromEnvironment($env);
		if(isset($originalEnv)){
			$originalEnv->mergeTaintEnvironment($taintEnv);
		}else{
			self::setTaintEnvironmentForEnvironment($env, $taintEnv);
		}
	}

	public static function updateTaintEnvironmentForEnvironment(Environment $env,
	                                                           TaintEnvironment $taintEnv){
		$originalEnv = self::getTaintEnvironmentFromEnvironment($env);
		if(isset($originalEnv)){
			$originalEnv->updateTaintEnvironment($taintEnv);
		}else{
			self::setTaintEnvironmentForEnvironment($env, $taintEnv);
		}
	}

	private function checkParentTaintPropagationCondition(){
		if(is_null($this->environment->getParent())){
			return false;
		}else{
			return self::checkParentPropagationCondition($this->environment);
		}
	}

	private static function checkParentPropagationCondition($env){
		if(get_class($env) !== 'Phortress\FunctionEnvironment'){
			return true;
		}else{
			return get_class($env->getParent()) === get_class($env);
		}
	}

	public function getTaintResult($varName){
		if(array_key_exists($varName, $this->taintResults)){
			return $this->taintResults[$varName];
		}else if($this->checkParentTaintPropagationCondition()){
			$parentTaintEnv = self::getTaintEnvironmentFromEnvironment($this->environment->getParent());
			if(isset($parentTaintEnv)){
				return $parentTaintEnv->getTaintResult($varName);
			}
		}
	}


	public function getTaintResultForVariable(Variable $var){
		$name = $var->name;
		return $this->getTaintResult($name);
	}


	public function getTaintResults(){
		return $this->taintResults;
	}
	/**
	 * Replaces the variable taint results stored in the current TaintEnvironment with the taint
	 * results stored in the given TaintEnvironment $env, for each variable that the current
	 * TaintEnvironment has in common with $env
	 * @param TaintEnvironment $env
	 */
	public function updateTaintEnvironment(TaintEnvironment $env){
		$envTaints = $env->getTaintResults();
		foreach($envTaints as $varName => $taintRes){
			$this->setTaintResult($varName, $taintRes);
		}
	}

	public function mergeTaintEnvironment(TaintEnvironment $env){
		$envTaints = $env->getTaintResults();
		foreach($envTaints as $varName => $taintRes){
			$this->mergeAndSetTaintResult($varName, $taintRes);
		}
	}

	public function copy(){
		return new TaintEnvironment($this->environment, $this->taintResults);
	}
} 