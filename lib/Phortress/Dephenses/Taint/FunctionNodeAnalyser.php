<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 18/11/14
 * Time: 8:56 AM
 */

namespace Phortress\Dephenses\Taint;


class FunctionNodeAnalyser extends NodeAnalyser{
	protected function createTaintResult($taint, $sanitising_funcs = array()){
		return new FunctionTaintResult($taint, $sanitising_funcs);
	}

	protected function mergeAnalysisResults(array $results){
//		$mergeResult = self::createTaintResult(Annotation::UNASSIGNED);
//		foreach($results as $result){
//			$mergeResult->merge($result);
//		}
//		return $result;
	}
} 