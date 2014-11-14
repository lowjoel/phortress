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

	public function getSanitisingFunction(){
		return $this->sanitising_functions;
	}
} 