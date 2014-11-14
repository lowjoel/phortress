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

class NodeFinder extends NodeVisitorAbstract{
	private $nodes = array();

	protected $filter;

	public function __construct($filter){
		$this->nodes = array();
		$this->filter = $filter;
	}
	
	public function enterNode(Node $node) {
		if(call_user_func($this->filter, $node)){
			$this->nodes[] = $node;
		}
	}

	public function  getNodes(){
		return $this->nodes;
	}
} 