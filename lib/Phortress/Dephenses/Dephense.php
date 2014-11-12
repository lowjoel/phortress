<?php
namespace Phortress\Dephenses;
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
        return array();
    }
    
    /**
     * Runs the analysis of the program
     * @param $files array containing AST of program files to be analysed
     */
    public abstract function run($files);
}
