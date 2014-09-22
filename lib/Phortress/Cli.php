<?php
namespace Phortress;

/**
 * Manages running Phortress from the command line.
 *
 * @package Phortress
 */
class Cli {
	/**
	 * The getopt configuration string.
	 */
	const GETOPT_STRING = 'f:';

	/**
	 * @var string[] The files which were specified to check.
	 */
	private static $files = array();

	/**
	 * Executes Phortress.
	 *
	 * @return int The exit code of the program.
	 */
	public static function run() {
		$options = getopt(self::GETOPT_STRING);
		self::parseOptions($options);

		return self::execute();
	}

	/**
	 * Parses the given options specified by getopt.
	 *
	 * @param $options The options given by getopt.
	 */
	private static function parseOptions($options) {
		if (isset($options['f'])) {
			self::$files = (array)$options['f'];
		} else {
			self::$files = array();
		}
	}

	/**
	 * Executes Phortress, returning the exit code.
	 *
	 * @return int The exit code of the program.
	 */
	private static function execute() {
		return 0;
	}
}
