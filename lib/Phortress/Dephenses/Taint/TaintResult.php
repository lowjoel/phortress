<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 14/11/14
 * Time: 10:53 AM
 */

namespace Phortress\Dephenses\Taint;


class TaintResult {
	private $annotation;
	private $sanitising_functions;

	public function __construct($annot, $functions = array()){
		$this->annotation = $annot;
		$this->sanitising_functions = $functions;
	}

	public function getTaint(){
		return $this->annotation;
	}

	public function getSanitisingFunctions(){
		return $this->sanitising_functions;
	}

	public function setTaint($annot){
		$this->annotation = $annot;
	}

	public function setSanitisingFunctions($functions){
		$this->sanitising_functions = $functions;
	}

	public function addSanitisingFunction($func){
		$this->sanitising_functions[] = $func;
	}

	public function merge($result){
		assert($result instanceof TaintResult);
		$this->setTaint(max($this->getTaint(), $result->getTaint()));
		$sanitisingFuncs = self::mergeTaintResultSanitisingFunctions($this, $result);
		$this->setSanitisingFunctions($sanitisingFuncs);
	}

	public static function mergeTaintResults(TaintResult $result1, TaintResult $result2){
		$mergedTaint = max($result1->getTaint(), $result2->getTaint());
		$mergedSanitisation = self::mergeTaintResultSanitisingFunctions($result1, $result2);
		return new TaintResult($mergedTaint, $mergedSanitisation);
	}

	protected static function mergeTaintResultSanitisingFunctions(TaintResult $result1,
	                                                           TaintResult $result2){
		$taint1 = $result1->getTaint();
		$taint2 = $result2->getTaint();
		if($taint1 >= Annotation::UNKNOWN && $taint2 >= Annotation::UNKNOWN){
			$functions1 = $result1->getSanitisingFunctions();
			$functions2 = $result2->getSanitisingFunctions();
			if(empty($functions1)){
				$merged = $functions2;
			}else{
				$merged = self::mergeSanitisingFunctions($functions1, $functions2);
			}
			return $merged;
		}else if($taint1 >= Annotation::UNKNOWN){
			return $result1->getSanitisingFunctions();
		}else{
			return $result2->getSanitisingFunctions();
		}
	}
	/**
	 * Merges two arrays of sanitising functions.
	 */
	protected static function mergeSanitisingFunctions($functions1, $functions2){
		return array_intersect($functions1, $functions2);
	}

	public function copy(){
		return new TaintResult($this->getTaint(), $this->getSanitisingFunctions());
	}
} 