<?php

/**
 * Contains the list of sanitising functions
 * Has a method which checks if an expression correctly sanitises the variable 
 * it returns
 *
 * @author naomileow
 */
class SanitisingFunctions {
    //put your code here
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
