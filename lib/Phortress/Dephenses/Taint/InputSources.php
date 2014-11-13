<?php
namespace Phortress\Dephenses\Taint;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;

/**
 * Contains the list of input sources
 * Has a method which checks if the variable is an input variable, 
 * ie it is a PHP global which stores user input.
 *
 * @author naomileow
 */
class InputSources {
    const USER_INPUT_GLOBALS = array(
        '_GET',
        '_POST',
        '_COOKIE',
        '_REQUEST',
        '_FILES',
        '_SERVER',
        '_ENV',
        'argv' //argc should be safe? since it's just the number of arguments
    );
    
    const INPUT_READ_FUNCTIONS = array(
        'readline',
        'get_headers',
        'parse_url',
        
    );
    
    const FILE_READ_FUNCTIONS = array(
        'fread', 
        'fopen', 
        'popen',
        'fgets', 
        'fgetss', //fgetss strips HTML tags
        'fscanf',
        'file',
        'ftell',
        'file_get_contents',
        'get_meta_tags',
        'bzread', //bzip2 file read
        'gzread', //gz file read
        'zip_read',
        'read_exif_data',
        'exif_read_data', //read EXIF headers from JPEG or TIFF
        'exif_imagetype', //reads the first bytes of an image and checks its signature
        'exif_thumbnail', //reads embedded thumbnail of TIFFor JPEG
        'readdir', //Should be ok, unless server is compromised
        'scandir',
    );
    
    const DATABASE_READ_FUNCTIONS = array(
        'mysql_fetch_array',
        'mysql_fetch_row',
        'mysql_fetch_assoc',
        'mysql_data_seek',
        'mysql_query',
        'mysql_unbuffered_query',
        'mysql_fetch_object',
        //the above functions are DEPRECATED as of PHP5.5
    
        'dba_open',
        'dba_popen',
    
        'dba_fetch',
        'dbx_query',
        'odbc_do',
        'odbc_exec',
        'odbc_execute',
    
        //mysqli
        'mysqli_fetch_assoc',
        'mysqli_fetch_row',
        'mysqli_fetch_object',
        'mysqli_query',
        'mysqli_data_seek',
        'mysqli_fetch_array',
    
        //postgre
        'pg_fetch_all',
        'pg_fetch_array',
        'pg_fetch_assoc',
        'pg_fetch_object',
        'pg_fetch_result',
        'pg_fetch_row',
    
        //sqlite
        'sqlite_fetch_all',
        'sqlite_fetch_array',
        'sqlite_fetch_object',
        'sqlite_fetch_single',
        'sqlite_fetch_string'
    );


    public static function isInputVariable(Node\Expr\Variable $var){
        return in_array($var->name, self::USER_INPUT_GLOBALS);
    }
    
    public static function isInputVariableName(String $name){
        return in_array($name, self::USER_INPUT_GLOBALS);
    }
    
    public static function isDatabaseRead(FuncCall $func){
        $name = $func->name->getLast();
        return in_array($name, self::DATABASE_READ_FUNCTIONS);
    }
    
    public static function isFileRead(FuncCall $func){
        $name = $func->name->getLast();
        return in_array($name, self::FILE_READ_FUNCTIONS);
    }
    
    public static function isInputRead(FuncCall $func){
        $name = $func->name->getLast();
        return in_array($name, self::INPUT_READ_FUNCTIONS);
    }
}
