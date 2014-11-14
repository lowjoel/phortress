<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 14/11/14
 * Time: 8:03 AM
 */

namespace Phortress\Dephenses\Taint;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ReturnStatementFinder extends NodeVisitorAbstract{
	private $returnStatements = array();

	public function __construct(){
		$this->returnStatements = array();
	}
	
	public function enterNode(Node $node) {
		if($node instanceof Node\Stmt\Return_){
			$this->returnStatements[] = $node;
		}
	}

	public function  getReturnStatements(){
		return $this->returnStatements;
	}
} 