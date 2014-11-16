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

/**
 * Traverses the parse tree in search of nodes meeting the give filter condition.
 */
class NodeFinder extends NodeVisitorAbstract{
	private $nodes = array();

	protected $ignoredNodes;

	protected $inIgnoredNode = array();

	protected $filter;

	public function __construct($filter, $ignoredNodes = array()){
		$this->nodes = array();
		$this->filter = $filter;
		$this->ignoredNodes = $ignoredNodes;
	}
	
	public function enterNode(Node $node) {
		$nodeClass = get_class($node);
		if(in_array($nodeClass, $this->ignoredNodes)){
			array_push($inIgnoredNode, true);
		}else if((count($this->inIgnoredNode) == 0)  && call_user_func($this->filter, $node)){
			$this->nodes[] = $node;
		}
	}

	public function leaveNode(Node $node){
		$nodeClass = get_class($node);
		if(in_array($nodeClass, $this->ignoredNodes)) {
			array_pop($this->inIgnoredNode);
		}
	}

	public function  getNodes(){
		return $this->nodes;
	}
} 