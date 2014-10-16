<?php
namespace Phortress\Dephenses;
/**
 * Description of Dephense
 *
 * @author naomileow
 */
abstract class Dephense {
    //put your code here
    
    /**
     * Runs the analysis of the program
     * @param $files array containing AST of program files to be analysed
     */
    public function run($files);
}
