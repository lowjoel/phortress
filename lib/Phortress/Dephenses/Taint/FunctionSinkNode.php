<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 19/11/14
 * Time: 1:02 PM
 */

namespace Phortress\Dephenses\Taint;


use PhpParser\Node;

class FunctionSinkNode {
	protected $node;
	protected $taints;

	public function __construct(Node $node, array $taints){
		$this->node = $node;
		$this->taints = $taints;
	}

	public function getNode(){
		return $this->node;
	}

	public function getTaints(){
		return $this->taints;
	}
} 