<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 15/11/14
 * Time: 10:58 PM
 */

namespace Phortress\Dephenses\Taint;


use PhpParser\Node;

class FunctionAnalyser2{
	/**
	 * Return statements and the variables they are dependent on.
	 * array(Stmt => array(variable name => VariableInfo)
	 */
	protected $returnStmts = array();

	/**
	 * The parameters to the function
	 */
	protected $params = array();

	/**
	 * The statements in the function
	 */
	protected $functionStmts;

	/**
	 * array(variable name => VariableInfo)
	 */
	protected $variables = array();


	protected $unresolved_variables = array();

	/**
	 * Environment where the function was defined
	 */
	protected $environment;

	protected $function;

	public function __construct(\Phortress\Environment $env, $functionName) {
		assert(!($functionName instanceof Expr));
		//For now we do not handle dynamic function names;
		$this->variables = array();
		$this->unresolved_variables = array();

		$this->environment = $env;
		$this->function = $env->resolveFunction($functionName);
		$this->functionStmts = $this->function->stmts;
		$this->params = $this->function->params;
	}

} 