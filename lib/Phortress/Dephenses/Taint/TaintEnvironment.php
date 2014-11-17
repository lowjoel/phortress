<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 17/11/14
 * Time: 2:05 PM
 */

namespace Phortress\Dephenses\Taint;


use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

/**
 * Contains a mapping of Variable Name to TaintResult
 */
class TaintEnvironment {
	private $taintResults = array();

	public function setTaintResult($varName, TaintResult $result){
		assert(!($varName instanceof Expr));
		$this->taintResults[$varName] = $result;
	}

	public function mergeAndSetTaintResult($varName, TaintResult $result){
		if(array_key_exists($varName, $this->taintResults)){
			$existingResult = $this->taintResults[$varName];
			$newTaintResult = TaintResult::mergeTaintResults($existingResult, $result);
		}else{
			$newTaintResult = $result;
		}
		$this->setTaintResult($varName, $newTaintResult);
	}

	public function getTaintResult($varName){
		if(array_key_exists($varName, $this->taintResults)){
			return $this->taintResults[$varName];
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
} 