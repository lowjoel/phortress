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
}
