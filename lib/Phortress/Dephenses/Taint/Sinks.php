<?php
namespace Phortress\Dephenses\Taint;
use \PhpParser\Node\Expr;

/**
 * List of Sink Functions adapted from RIPS
 *
 * @author naomileow
 */
class Sinks {
    public static $XSS_SINKS = array(
		'echo'							=> array(), 
		'print'							=> array(),
		'print_r'						=> array(),
		'exit'							=> array(),
		'die'							=> array(),
		'printf'						=> array(),
		'vprintf'						=> array()
	);
	
	// HTTP header injections
    public static $HTTP_HEADER = array(
		'header' 						=> array(array(1), array())
	);
	
	// code evaluating functions  => (parameters to scan, securing functions)
	// example parameter array(1,3) will trace only first and third parameter 
    public static $CODE_EXE_SINKS = array(
		'array_diff_uassoc'				=> array(array(3), array()),
		'array_diff_ukey'				=> array(array(3), array()),
		'array_filter'					=> array(array(2), array()),
		'array_intersect_uassoc'		=> array(array(3), array()),
		'array_intersect_ukey'			=> array(array(3), array()),
		'array_map'						=> array(array(1), array()),
		'array_reduce'					=> array(array(2), array()),
		'array_udiff'					=> array(array(3), array()),
		'array_udiff_assoc'				=> array(array(3), array()),
		'array_udiff_uassoc'			=> array(array(3,4), array()),
		'array_uintersect'				=> array(array(3), array()),
		'array_uintersect_assoc'		=> array(array(3), array()),
		'array_uintersect_uassoc'		=> array(array(3,4), array()),		
		'array_walk'					=> array(array(2), array()),
		'array_walk_recursive'			=> array(array(2), array()),
		'assert' 						=> array(array(1), array()),
		'assert_options'				=> array(array(1,2), array()),
		'call_user_func'				=> array(array(1), array()),
		'call_user_func_array'			=> array(array(1), array()),
		'create_function' 				=> array(array(1,2), array()),
		'dotnet_load'					=> array(array(1), array()),
		'forward_static_call'			=> array(array(1), array()),
		'forward_static_call_array'		=> array(array(1), array()),
		'eio_busy'						=> array(array(3), array()),
		'eio_chmod'						=> array(array(4), array()),
		'eio_chown'						=> array(array(5), array()),
		'eio_close'						=> array(array(3), array()),
		'eio_custom'					=> array(array(1,2), array()),
		'eio_dup2'						=> array(array(4), array()),
		'eio_fallocate'					=> array(array(6), array()),
		'eio_fchmod'					=> array(array(4), array()),
		'eio_fchown'					=> array(array(5), array()),
		'eio_fdatasync'					=> array(array(3), array()),
		'eio_fstat'						=> array(array(3), array()),
		'eio_fstatvfs'					=> array(array(3), array()),
		'eval' 							=> array(array(1), array()),
		'event_buffer_new'				=> array(array(2,3,4), array()),		
		'event_set'						=> array(array(4), array()),
		'iterator_apply'				=> array(array(2), array()),
		'mb_ereg_replace'				=> array(array(1,2), array()),
		'mb_eregi_replace'				=> array(array(1,2), array()),
		'ob_start'						=> array(array(1), array()),
		'preg_filter'					=> array(array(1,2), array()),
		'preg_replace'					=> array(array(1,2), array()),
		'preg_replace_callback'			=> array(array(1,2), array()),
		'register_shutdown_function'	=> array(array(1), array()),
		'register_tick_function'		=> array(array(1), array()),
		'runkit_method_add'				=> array(array(1,2,3,4), array()),
		'runkit_method_copy'			=> array(array(1,2,3), array()),
		'runkit_method_redefine'		=> array(array(1,2,3,4), array()),	
		'runkit_method_rename'			=> array(array(1,2,3), array()),
		'runkit_function_add'			=> array(array(1,2,3), array()),
		'runkit_function_copy'			=> array(array(1,2), array()),
		'runkit_function_redefine'		=> array(array(1,2,3), array()),
		'runkit_function_rename'		=> array(array(1,2), array()),
		'session_set_save_handler'		=> array(array(1,2,3,4,5), array()),
		'set_error_handler'				=> array(array(1), array()),
		'set_exception_handler'			=> array(array(1), array()),
		'spl_autoload'					=> array(array(1), array()),	
		'spl_autoload_register'			=> array(array(1), array()),
		'sqlite_create_aggregate'		=> array(array(2,3,4), array()), 
		'sqlite_create_function'		=> array(array(2,3), array()), 
		'stream_wrapper_register'		=> array(array(2), array()), 
		'uasort'						=> array(array(2), array()),
		'uksort'						=> array(array(2), array()),
		'usort'							=> array(array(2), array()),
		'yaml_parse'					=> array(array(4), array()),
		'yaml_parse_file'				=> array(array(4), array()),
		'yaml_parse_url'				=> array(array(4), array())
	);
	
	// file inclusion functions => (parameters to scan, securing functions)
	public static $FILE_INCLUSION_SINKS = array(
		'include' 						=> array(array(1)),
		'include_once' 					=> array(array(1)),
		'parsekit_compile_file'			=> array(array(1)),
		'php_check_syntax' 				=> array(array(1)),	
		'require' 						=> array(array(1)),
		'require_once' 					=> array(array(1)),
		'runkit_import'					=> array(array(1)),
		'set_include_path' 				=> array(array(1)),
		'virtual' 						=> array(array(1))		
	);
	// file affecting functions  => (parameters to scan, securing functions)
	// file handler functions like fopen() are added as parameter 
	// for functions that use them like fread() and fwrite()
//	$NAME_FILE_READ = 'File Disclosure';
	public static $FILE_READ_SINKS = array(
		'bzread'						=> array(array(1), array()), 
		'bzflush'						=> array(array(1), array()), 
		'dio_read'						=> array(array(1), array()),   
		'eio_readdir'					=> array(array(1), array()),  
		'fdf_open'						=> array(array(1), array()), 
		'file'							=> array(array(1), array()), 
		'file_get_contents'				=> array(array(1), array()),  
		'finfo_file'					=> array(array(1,2), array()), 
		'fflush'						=> array(array(1), array()),
		'fgetc'							=> array(array(1), array()),
		'fgetcsv'						=> array(array(1), array()),
		'fgets'							=> array(array(1), array()),
		'fgetss'						=> array(array(1), array()),
		'fread'							=> array(array(1), array()), 
		'fpassthru'						=> array(array(1,2), array()), 
		'fscanf'						=> array(array(1), array()), 
		'ftok'							=> array(array(1), array()),
		'get_meta_tags'					=> array(array(1), array()), 
		'glob'							=> array(array(1), array()), 
		'gzfile'						=> array(array(1), array()), 
		'gzgetc'						=> array(array(1), array()),
		'gzgets'						=> array(array(1), array()), 
		'gzgetss'						=> array(array(1), array()), 
		'gzread'						=> array(array(1), array()),  
		'gzpassthru'					=> array(array(1), array()), 
		'highlight_file'				=> array(array(1), array()),  
		'imagecreatefrompng'			=> array(array(1), array()), 
		'imagecreatefromjpg'			=> array(array(1), array()), 
		'imagecreatefromgif'			=> array(array(1), array()), 
		'imagecreatefromgd2'			=> array(array(1), array()), 
		'imagecreatefromgd2part'		=> array(array(1), array()), 
		'imagecreatefromgd'				=> array(array(1), array()),  
		'opendir'						=> array(array(1), array()),  
		'parse_ini_file' 				=> array(array(1), array()),	
		'php_strip_whitespace'			=> array(array(1), array()),	
		'readfile'						=> array(array(1), array()), 
		'readgzfile'					=> array(array(1), array()), 
		'readlink'						=> array(array(1), array()),		
		//'stat'						=> array(array(1), array()),
		'scandir'						=> array(array(1), array()),
		'show_source'					=> array(array(1), array()),
		'stream_get_contents'			=> array(array(1), array()),
		'stream_get_line'				=> array(array(1), array()),
		'xdiff_file_bdiff'				=> array(array(1,2), array()),
		'xdiff_file_bpatch'				=> array(array(1,2), array()),
		'xdiff_file_diff_binary'		=> array(array(1,2), array()),
		'xdiff_file_diff'				=> array(array(1,2), array()),
		'xdiff_file_merge3'				=> array(array(1,2,3), array()),
		'xdiff_file_patch_binary'		=> array(array(1,2), array()),
		'xdiff_file_patch'				=> array(array(1,2), array()),
		'xdiff_file_rabdiff'			=> array(array(1,2), array()),
		'yaml_parse_file'				=> array(array(1), array()),
		'zip_open'						=> array(array(1), array())
	);
	
	// file or file system affecting functions
//	$NAME_FILE_AFFECT = 'File Manipulation';
	public static $FILE_AFFECT_SINKS = array(
		'bzwrite'						=> array(array(2), array()),
		'chmod'							=> array(array(1), array()),
		'chgrp'							=> array(array(1), array()),
		'chown'							=> array(array(1), array()),
		'copy'							=> array(array(1), array()),
		'dio_write'						=> array(array(1,2), array()),	
		'eio_chmod'						=> array(array(1), array()),
		'eio_chown'						=> array(array(1), array()),
		'eio_mkdir'						=> array(array(1), array()),
		'eio_mknod'						=> array(array(1), array()),
		'eio_rmdir'						=> array(array(1), array()),
		'eio_write'						=> array(array(1,2), array()),
		'eio_unlink'					=> array(array(1), array()),
		'event_buffer_write'			=> array(array(2), array()),
		'file_put_contents'				=> array(array(1,2), array()),
		'fputcsv'						=> array(array(1,2), array()),
		'fputs'							=> array(array(1,2), array()),	
		'ftruncate'						=> array(array(1), array()),
		'fwrite'						=> array(array(1,2), array()),		
		'gzwrite'						=> array(array(1,2), array()),
		'gzputs'						=> array(array(1,2), array()),
		'loadXML'						=> array(array(1), array()),
		'mkdir'							=> array(array(1), array()),
		'move_uploaded_file'			=> array(array(1,2), array()),	
		'posix_mknod'					=> array(array(1), array()),
		'recode_file'					=> array(array(2,3), array()),	
		'rename'						=> array(array(1,2), array()),
		'rmdir'							=> array(array(1), array()),	
		'shmop_write'					=> array(array(2), array()),
		'touch'							=> array(array(1), array()),
		'unlink'						=> array(array(1), array()),
		'xdiff_file_bdiff'				=> array(array(3), array()),
		'xdiff_file_bpatch'				=> array(array(3), array()),
		'xdiff_file_diff_binary'		=> array(array(3), array()),
		'xdiff_file_diff'				=> array(array(3), array()),	
		'xdiff_file_merge3'				=> array(array(4), array()),
		'xdiff_file_patch_binary'		=> array(array(3), array()),
		'xdiff_file_patch'				=> array(array(3), array()),
		'xdiff_file_rabdiff'			=> array(array(3), array()),
		'yaml_emit_file'				=> array(array(1,2), array()),
	);
	// OS Command executing functions => (parameters to scan, securing functions)
//	$NAME_EXEC = 'Command Execution';
	public static $FILE_EXEC_SINKS = array(
		'backticks'						=> array(array(1), array()), # transformed during parsing
		'exec'							=> array(array(1), array()),
		'expect_popen'					=> array(array(1), array()),
		'passthru'						=> array(array(1), array()),
		'pcntl_exec'					=> array(array(1), array()),
		'popen'							=> array(array(1), array()),
		'proc_open'						=> array(array(1), array()),
		'shell_exec'					=> array(array(1), array()),
		'system'						=> array(array(1), array()),
		'mail'							=> array(array(5), array()), // http://esec-pentest.sogeti.com/web/using-mail-remote-code-execution
		'w32api_invoke_function'		=> array(array(1), array()),
		'w32api_register_function'		=> array(array(2), array()),
	);
	// SQL executing functions => (parameters to scan, securing functions)
	public static $DATABASE_SINKS = array(
	// Abstraction Layers
		'dba_open'						=> array(array(1), array()),
		'dba_popen'						=> array(array(1), array()), 
		'dba_insert'					=> array(array(1,2), array()),
		'dba_fetch'						=> array(array(1), array()), 
		'dba_delete'					=> array(array(1), array()), 
		'dbx_query'						=> array(array(2), array()), 
		'odbc_do'						=> array(array(2), array()),
		'odbc_exec'						=> array(array(2), array()),
		'odbc_execute'					=> array(array(2), array()),
	// Vendor Specific	
		'db2_exec' 						=> array(array(2), array()),
		'db2_execute'					=> array(array(2), array()),
		'fbsql_db_query'				=> array(array(2), array()),
		'fbsql_query'					=> array(array(1), array()), 
		'ibase_query'					=> array(array(2), array()), 
		'ibase_execute'					=> array(array(1), array()), 
		'ifx_query'						=> array(array(1), array()), 
		'ifx_do'						=> array(array(1), array()),
		'ingres_query'					=> array(array(2), array()),
		'ingres_execute'				=> array(array(2), array()),
		'ingres_unbuffered_query'		=> array(array(2), array()),
		'msql_db_query'					=> array(array(2), array()), 
		'msql_query'					=> array(array(1), array()),
		'msql'							=> array(array(2), array()), 
		'mssql_query'					=> array(array(1), array()), 
		'mssql_execute'					=> array(array(1), array()),
		'mysql_db_query'				=> array(array(2), array()),  
		'mysql_query'					=> array(array(1), array()), 
		'mysql_unbuffered_query'		=> array(array(1), array()), 
		'mysqli_stmt_execute'			=> array(array(1), array()),
		'mysqli_query'					=> array(array(2), array()),
		'mysqli_real_query'				=> array(array(1), array()),
		'mysqli_master_query'			=> array(array(2), array()),
		'oci_execute'					=> array(array(1), array()),
		'ociexecute'					=> array(array(1), array()),
		'ovrimos_exec'					=> array(array(2), array()),
		'ovrimos_execute'				=> array(array(2), array()),
		'ora_do'						=> array(array(2), array()), 
		'ora_exec'						=> array(array(1), array()), 
		'pg_query'						=> array(array(2), array()),
		'pg_send_query'					=> array(array(2), array()),
		'pg_send_query_params'			=> array(array(2), array()),
		'pg_send_prepare'				=> array(array(3), array()),
		'pg_prepare'					=> array(array(3), array()),
		'sqlite_open'					=> array(array(1), array()),
		'sqlite_popen'					=> array(array(1), array()),
		'sqlite_array_query'			=> array(array(1,2), array()),
		'arrayQuery'					=> array(array(1,2), array()),
		'singleQuery'					=> array(array(1), array()),
		'sqlite_query'					=> array(array(1,2), array()),
		'sqlite_exec'					=> array(array(1,2), array()),
		'sqlite_single_query'			=> array(array(2), array()),
		'sqlite_unbuffered_query'		=> array(array(1,2), array()),
		'sybase_query'					=> array(array(1), array()), 
		'sybase_unbuffered_query'		=> array(array(1), array())
	);
	
	// xpath injection
	public static $XPATH_SINKS = array(
		'xpath_eval'					=> array(array(2), array()),	
		'xpath_eval_expression'			=> array(array(2), array()),		
		'xptr_eval'						=> array(array(2), array())
	);
	
	// ldap injection
	public static $LDAP_SINKS = array(
		'ldap_add'						=> array(array(2,3), array()),
		'ldap_delete'					=> array(array(2), array()),
		'ldap_list'						=> array(array(3), array()),
		'ldap_read'						=> array(array(3), array()),
		'ldap_search'					=> array(array(3), array())
	);	
		
	// connection handling functions
//	$NAME_CONNECT = 'Header Injection';
	public static $CONNECTION_SINKS = array(
		'curl_setopt'					=> array(array(2,3), array()),
		'curl_setopt_array' 			=> array(array(2), array()),
		'cyrus_query' 					=> array(array(2), array()),
		'error_log'						=> array(array(3), array()),
		'fsockopen'						=> array(array(1), array()), 
		'ftp_chmod' 					=> array(array(2,3), array()),
		'ftp_exec'						=> array(array(2), array()), 
		'ftp_delete' 					=> array(array(2), array()), 
		'ftp_fget' 						=> array(array(3), array()), 
		'ftp_get'						=> array(array(2,3), array()), 
		'ftp_nlist' 					=> array(array(2), array()), 
		'ftp_nb_fget' 					=> array(array(3), array()), 
		'ftp_nb_get' 					=> array(array(2,3), array()), 
		'ftp_nb_put'					=> array(array(2), array()), 
		'ftp_put'						=> array(array(2,3), array()), 
		'imap_open'						=> array(array(1), array()),  
		'imap_mail'						=> array(array(1), array()),
		'mail' 							=> array(array(1,4), array()), 
		'pfsockopen'					=> array(array(1), array()),   
		'session_register'				=> array(array(0), array()),  
		'socket_bind'					=> array(array(2), array()),  
		'socket_connect'				=> array(array(2), array()),  
		'socket_send'					=> array(array(2), array()), 
		'socket_write'					=> array(array(2), array()),  
		'stream_socket_client'			=> array(array(1), array()),  
		'stream_socket_server'			=> array(array(1), array())
	);
	
	// other critical functions
//	$NAME_OTHER = 'Possible Flow Control'; // :X
	public static $FLOW_CONTROL_SINKS = array(
		'dl' 							=> array(array(1), array()),	
		'ereg'							=> array(array(2), array()), # nullbyte injection affected		
		'eregi'							=> array(array(2), array()), # nullbyte injection affected			
		'ini_set' 						=> array(array(1,2), array()),
		'ini_restore'					=> array(array(1), array()),
		'runkit_constant_redefine'		=> array(array(1,2), array()),
		'runkit_method_rename'			=> array(array(1,2,3), array()),
		'sleep'							=> array(array(1), array()),
		'unserialize'					=> array(array(1), array()),
		'extract'						=> array(array(1), array()),
		'mb_parse_str'					=> array(array(1), array()),
		'parse_str'						=> array(array(1), array()),
		'putenv'						=> array(array(1), array()),
		'set_include_path'				=> array(array(1), array()),
		'apache_setenv'					=> array(array(1,2), array()),	
		'define'						=> array(array(1), array())
	);
	
	// property oriented programming with unserialize
//	$NAME_POP = 'Unserialize';
//	$F_POP = array(
//		'unserialize'					=> array(array(1), array()), // calls __destruct
//		'is_a'							=> array(array(1), array())	 // calls __autoload in php 5.3.7, 5.3.8
//	);

	public static function isSQLInjectionSinkFunction(Expr\FuncCall $func){
		$funcName = $func->name->getLast();
		return array_key_exists($funcName, self::$DATABASE_SINKS);
	}
}
