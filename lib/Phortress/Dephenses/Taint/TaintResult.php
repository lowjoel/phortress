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

	public function getAnnotation(){
		return $this->annotation;
	}

	public function getSanitisingFunctions(){
		return $this->sanitising_functions;
	}

	public function setAnnotation($annot){
		$this->annotation = $annot;
	}

	public function setSanitisingFunctions($functions){
		$this->sanitising_functions = $functions;
	}

	public static function mergeTaintResults(TaintResult $result1, TaintResult $result2){
		$mergedTaint = max($result1->getAnnotation(), $result2->getAnnotation());
		$mergedSanitisation = self::mergeTaintResultSanitisingFunctions($result1, $result2);
		return new TaintResult($mergedTaint, $mergedSanitisation);
	}

	public static function mergeTaintResultSanitisingFunctions(TaintResult $result1,
	                                                           TaintResult $result2){
		$taint1 = $result1->getAnnotation();
		$taint2 = $result2->getAnnotation();
		if($taint1 >= Annotation::UNKNOWN && $taint2 >= Annotation::UNKNOWN){
			return self::mergeSanitisingFunctions($result1->getSanitisingFunctions(),
				$result2->getSanitisingFunctions());
		}else if($taint1 >= Annotation::UNKNOWN){
			return $result1->getSanitisingFunctions();
		}else{
			return $result2->getSanitisingFunctions();
		}
	}
	/**
	 * Merges two arrays of sanitising functions.
	 */
	public static function mergeSanitisingFunctions($functions1, $functions2){
		return array_intersect($functions1, $functions2);
	}

} 