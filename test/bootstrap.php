<?php
require __DIR__ . '/../lib/bootstrap.php';

class TestClass {
	/**
	 * The class we are encapsulating.
	 *
	 * @var string
	 */
	private $class;

	/**
	 * The reflector we are using to access the methods.
	 *
	 * @var ReflectionClass
	 */
	private $reflector;

	/**
	 * Gets the wrapper for the given class name.
	 *
	 * @param string $class The name of the class to wrap.
	 */
	public function __construct($class) {
		$this->class = $class;
		$this->reflector = new \ReflectionClass($class);
	}

	/**
	 * Shorthand to invoke the given method in our wrapped class.
	 *
	 * @param string $method The method of the object to call.
	 * @param array $arguments The arguments to the method.
	 * @return mixed The result of the method call.
	 * @throws BadMethodCallException If no such method exists in the class.
	 */
	public function __call($method, array $arguments) {
		$method = $this->reflector->getMethod($method);
		if (!$method) {
			throw new BadMethodCallException('Unknown method ' . $method);
		}

		$method->setAccessible(true);
		return $method->invokeArgs(null, $arguments);
	}

	/**
	 * Shorthand to retrieve the inaccessible property.
	 *
	 * @param string $name The name of the property
	 * @return mixed The value of the property.
	 */
	public function __get($name) {
		$property = $this->reflector->getProperty($name);
		$property->setAccessible(true);

		return $property->getValue();
	}

	/**
	 * Shorthand to set the value of an inaccessible property.
	 *
	 * @param string $name the name of the property.
	 * @param mixed $value The value of the property
	 */
	public function __set($name, $value) {
		$property = $this->reflector->getProperty($name);
		$property->setAccessible(true);

		$property->setValue($value);
	}
}

/**
 * Wraps an object such that its private and protected methods are available for
 * calling in test objects.
 */
class TestObject {
	/**
	 * The object we are testing.
	 *
	 * @var Object
	 */
	private $object;

	/**
	 * The reflector we are using to access the class innards.
	 *
	 * @var ReflectionClass
	 */
	private $reflector;

	/**
	 * Gets the wrapper for the class of the current object.
	 *
	 * @return TestClass
	 */
	public function getClass() {
		return new TestClass(get_class($this->object));
	}

	public function __construct($object) {
		$this->object = $object;
		$this->reflector = new \ReflectionClass(get_class($object));
	}

	/**
	 * Shorthand to invoke the given method in our wrapped class.
	 *
	 * @param string $method The method of the object to call.
	 * @param array $arguments The arguments to the method.
	 * @return mixed The result of the method call.
	 * @throws BadMethodCallException If no such method exists in the class.
	 */
	public function __call($method, array $arguments) {
		$method = $this->reflector->getMethod($method);
		if (!$method) {
			throw new BadMethodCallException('Unknown method ' . $method);
		}

		$method->setAccessible(true);
		return $method->invokeArgs($this->object, $arguments);
	}

	/**
	 * Shorthand to retrieve the inaccessible property.
	 *
	 * @param string $name The name of the property
	 * @return mixed The value of the property.
	 */
	public function __get($name) {
		$property = $this->reflector->getProperty($name);
		$property->setAccessible(true);

		return $property->getValue($this->object);
	}

	/**
	 * Shorthand to set the value of an inaccessible property.
	 *
	 * @param string $name the name of the property.
	 * @param mixed $value The value of the property
	 */
	public function __set($name, $value) {
		$property = $this->reflector->getProperty($name);
		$property->setAccessible(true);

		$property->setValue($this->object, $value);
	}
}

/**
 * Loads the given file as a Program, then returns it wrapped in a TestClass.
 * @param string $file The path to the file to load.
 * @return \Phortress\Program
 */
function loadGlassBoxProgram($file) {
	$program = new \Phortress\Program($file);
	$program = new \TestObject($program);
	$program->parse();

	return $program;
}

ini_set('xdebug.max_nesting_level', 2000);
ini_set('xdebug.var_display_max_depth', -1);
