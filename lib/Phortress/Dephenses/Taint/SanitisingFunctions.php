<?php

/**
 * Contains the list of sanitising functions
 * Has a method which checks if an expression correctly sanitises the variable 
 * it returns
 *
 * @author naomileow
 */
class SanitisingFunctions {
    //This are the functions that can be used to sanitise input against any type of vulnerability
    public static $general_sanitising = array(
		'intval',
		'floatval',
		'doubleval',
		'filter_input',
		'urlencode',
		'rawurlencode',
		'hexdec',
		'md5',
		'sha1',
		'crypt',
		'crc32',
		'hash',
		'base64_encode',
                'str_rot13',
	);
    
    public static $sanitising_reverse = array(
		'rawurldecode' => 'rawurlencode',
		'urldecode' => 'urlencode',
		'base64_decode' => 'base64_encode',
		'html_entity_decode' => 'htmlentities',
		'str_rot13' => 'str_rot13',
	);
    
    public static $sql_sanitising = array(
		'addslashes',
		'dbx_escape_string',
		'db2_escape_string',
		'ingres_escape_string',
		'maxdb_escape_string',
		'maxdb_real_escape_string',
		'mysql_real_escape_string',
		'mysqli_escape_string',
		'mysqli_real_escape_string',
		'pg_escape_string',	
		'pg_escape_bytea',
		'sqlite_escape_string',
		'sqlite_udf_encode_binary'
    );
    
    public static $shell_sanitising = array(
		'escapeshellarg',
		'escapeshellcmd'
    );	
    
    public static $xss_sanitising = array(
		'htmlentities',
		'htmlspecialchars'
    );	
    
    public static function isGeneralSanitisingFunction($func_name){
        return in_array($func_name, self::general_sanitising);
    }
    
    public static function isShellSanitisingFunction($func_name){
        return in_array($func_name, self::shell_sanitising);
    }
    
    public static function isXSSSanitisingFunction($func_name){
        return in_array($func_name, self::xss_sanitising());
    }
    
    public static function isSQLSanitisingFunction($func_name){
        return in_array($func_name, self::sql_sanitising);
    }
    
    public static function isSanitisingFunction($func_name){
        return self::isGeneralSanitisingFunction($func_name) || self::isSQLSanitisingFunction($func_name) 
                || self::isShellSanitisingFunction($func_name) || self::isShellSanitisingFunction($func_name);
    }
    
    public static function isSanitisingReverseFunction($func_name){
        return array_key_exists($func_name, self::$sanitising_reverse);
    }
    
    public static function getAffectedSanitiser($func_name){
        return self::$sanitising_reverse[$func_name];
    }
}
