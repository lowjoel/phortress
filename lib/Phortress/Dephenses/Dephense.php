<?php
namespace Phortress\Dephenses;

use PhpParser\Node;

/**
 * Description of Dephense
 *
 * @author naomileow
 */
abstract class Dephense {
	/**
	 * Gets all registered Dephenses.
	 *
	 * @return Dephense[] The list of registered Dephenses.
	 */
	public static function getAll() {
		$files = scandir(__DIR__);
		$result = array();

		foreach ($files as $file) {
			if (is_dir(__DIR__ . '/' . $file)) {
				continue;
			}

			$basename = basename($file);
			$className = basename($basename, substr($basename, strrpos($basename, '.')));
			$className = 'Phortress\Dephenses\\' . $className;

			if (self::isDephense($className)) {
				$result[] = new $className();
			}
		}

		return $result;
	}

	/**
	 * Checks if the given class name is a Dephense.
	 *
	 * @param $className
	 * @return bool True if the class is a Dephense.
	 */
	private static function isDephense($className) {
		try {
			$reflectionClass = new \ReflectionClass($className);
			$parent = $reflectionClass->getParentClass();
			for (; $parent !== false; $parent = $parent->getParentClass()) {
				if ($parent->getName() === 'Phortress\Dephenses\Dephense') {
					return true;
				}
			}
		} catch (\ReflectionException $e) {
		}

		return false;
	}

	/**
	 * Runs the analysis of the program
	 *
	 * @param Node[] $parseTree The AST of the program to be analysed.
	 * @return Message[] The messages raised by this Dephense.
	 */
	public abstract function run(array $parseTree);
}
