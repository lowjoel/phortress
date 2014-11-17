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
	}

	public function setTaintResult($varName, TaintResult $result){
		assert(!($varName instanceof Expr));
		$this->taintResults[$varName] = $result;
	}

	public function mergeAndSetTaintResult($varName, TaintResult $result){
		$existingResult = $this->getTaintResult($varName);
		if(isset($existingResult)){
			$existingResult = $this->taintResults[$varName];
			$existingResult->merge($result);
		}else{
			$this->setTaintResult($varName, $result);
		}
	}

	public  static function getTaintEnvironmentFromEnvironment(Environment $env){
		return $env->taintEnvironment;
	}

	public  static function setTaintEnvironmentForEnvironment(Environment $env,
	                                                          TaintEnvironment $taintEnv){
		$env->taintEnvironment = $taintEnv;
	}

	private function checkParentTaintPropagationCondition(){
		if(is_null($this->environment->getParent())){
			return false;
		}else if(get_class($this->environment) !== 'Phortress\FunctionEnvironment'){
			return true;
		}else{
			return get_class($this->environment->getParent()) === 'Phortress\FunctionEnvironment';
		}
	}

	public function getTaintResult($varName){
		if(array_key_exists($varName, $this->taintResults)){
			return $this->taintResults[$varName];
		}else if($this->checkParentTaintPropagationCondition()){
			$parentTaintEnv = self::getTaintEnvironmentFromEnvironment($this->environment->getParent());
			if(isset($parentTaintEnv)){
				return $parentTaintEnv->getTaintResult($varName);
			}else{
				return new TaintResult(Annotation::UNKNOWN);
			}

		}else{
			return new TaintResult(Annotation::UNKNOWN);
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