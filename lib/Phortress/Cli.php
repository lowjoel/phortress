<?php
namespace Phortress;

use \Colors\Color;
use \Phortress\Dephenses\Error;

/**
 * Manages running Phortress from the command line.
 *
 * @package Phortress
 */
class Cli {
	/**
	 * The getopt configuration string.
	 */
	const GETOPT_STRING = 'hf:';

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
		if (self::parseOptions($options)) {
			return self::check();
		} else {
			return 0;
		}
	}

	/**
	 * Parses the given options specified by getopt.
	 *
	 * @param array $options The options given by getopt.
	 * @return bool True if execution should continue.
	 */
	private static function parseOptions(array $options) {
		if (isset($options['h']) || !isset($options['f'])) {
			self::displayHelp();
			return false;
		} else {
			self::$files = (array)$options['f'];
			return true;
		}
	}

	/**
	 * Displays the help message
	 */
	private static function displayHelp() {
		echo <<<EOH
    uuu       uuu     ______
   uuu|=====uuu |    / __  /_  ________________________________________
   | |======| |'|   / /_/ / /_/ / __ / __  /_  __/ __  / ___/  __/ ___/
   | | .==. | | |  / ____/ __  / /_// /_/_/ / / / / /_/ ___/__  /___ /
   |___|##|___|/  /_/   /_/ /_/____/_/\_\  /_/ /_/\_\/____/____/ ___/


EOH;
		$color = new Color();
		echo $color('Usage:')->yellow;
		echo '
    phortress.php [-hf] [arguments]

';
		echo $color('Options:')->yellow;
		echo '
    -h          Display this help message
    -f [file1]  Runs Phortress on the given file

Pretty fortress ASCII art, from http://ascii.co.uk/art/fortress
';
	}

	/**
	 * Executes Phortress, returning the exit code.
	 *
	 * @return int The exit code of the program.
	 */
	private static function check() {
		foreach (self::$files as $file) {
			self::checkProgram($file);
		}

		return 0;
	}

	/**
	 * Starts parsing the given PHP source file as the entry point to a program.
	 *
	 * @param string $file The path to the file to treat as the entry point.
	 */
	private static function checkProgram($file) {
		$program = new Program($file);
		$program->parse();
		$result = $program->verify(null);

		self::printResults($result);
	}

	/**
	 * Prints the errors found in the Dephenses.
	 *
	 * @param Dephenses\Message[] $results
	 */
	private static function printResults(array $results) {
		$color = new Color();
		foreach ($results as $result) {
			if ($result instanceof Error) {
				echo $color('[Error]   ')->red;
			} else {
				echo '[Message] ';
			}

			echo $result->getMessage();
			echo ' at ';
			echo $color(sprintf('%sline %d',
					empty($result->getNode()->file) ? '' : $result->getNode()->file . ' ',
					$result->getNode()->getLine()))->yellow;
			echo PHP_EOL;
		}
	}
}
