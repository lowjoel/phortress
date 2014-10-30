<?php
namespace Phortress;

/**
 * Represents one source file and the definitions it contains.
 *
 * @package Phortress
 */
class SourceFile {
	/**
	 * The path to the source file.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Constructs a new Source File object.
	 *
	 * @param string $path The path to the source file to parse.
	 * @param \PhpParser\Node[] $statements The statements in the file.
	 *
	 * @throws Exception\IOException When the file specified cannot be opened for parsing.
	 */
	public function __construct($path, $statements) {
		if (!file_exists($path)) {
			throw new Exception\IOException($path);
		}

		$this->path = $path;
	}
}
