<?php
namespace Phortress\Dephenses;
/* *
 * Contains the predefined PHP variables and function names
 */

$FILE_READ_FUNCTIONS = array(
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

$FILE_WRITE_FUNCTIONS = array(
    'fwrite',
    'fpassthru',
    'rewind'
);

$DATABASE_READ_FUNCTIONS = array(
    'mysql_fetch_array',
    'mysql_fetch_row',
    'mysql_fetch_assoc',
    'mysql_data_seek',
    'mysql_query',
    'mysql_unbuffered_query',
    'mysql_fetch_object',
    //the above functions are DEPRECATED as of PHP5.5
    
    //these functions only open the db connection. must we handle these?
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

$DATABASE_WRITE_FUNCTIONS = array(
    'mysql_query',
    'mysql_unbuffered_query',
    
    'dba_insert',
    'dba_delete', //TODO: do we treat this differently?
    'dbx_query',
    'odbc_do',
    'odbc_exec',
    'odbc_execute',
    
    'mysqli_query',
    'mysqli_stmt_execute',
    
);

$LDAP_FUNCTIONS = array(
    'ldap_add',
    'ldap_delete',
    'ldap_list',
    'ldap_read',
    'ldap_search'
);

$XPATH_FUNCTIONS = array(
    'xpath_eval',
    'xpath_eval_expression',
    'xptr_eval'
);

$PRINTOUT_FUNCTIONS = array(
    'echo', 
    'print',
    'print_r',
    'exit',
    'die',
    'printf',
    'vprintf'
);


$CODE_EVALUATING_FUNCTIONS = array(
    'array_diff_uassoc',
    'array_diff_ukey',
    'array_filter',
    'array_intersect_uassoc',
    'array_intersect_ukey',
    'array_map',
    'array_reduce',
    'array_udiff',
    'array_udiff_assoc',
    'array_udiff_uassoc',
    'array_uintersect',
    'array_uintersect_assoc',
    'array_uintersect_uassoc',		
    'array_walk',
    'array_walk_recursive',
    'assert',
    'assert_options',
    'call_user_func',
    'call_user_func_array',
    'create_function',
    'dotnet_load',
    'forward_static_call',
    'forward_static_call_array',
    'eio_busy',
    'eio_chmod',
    'eio_chown',
    'eio_close',
    'eio_custom',
    'eio_dup2',
    'eio_fallocate',
    'eio_fchmod',
    'eio_fchown',
    'eio_fdatasync',
    'eio_fstat',
    'eio_fstatvfs',
    'eval',
    'event_buffer_new',		
    'event_set',
    'iterator_apply',
    'mb_ereg_replace',
    'mb_eregi_replace',
    'ob_start',
    'preg_filter',
    'preg_replace',
    'preg_replace_callback',
    'register_shutdown_function',
    'register_tick_function',
    'runkit_method_add',
    'runkit_method_copy',
    'runkit_method_redefine',	
    'runkit_method_rename',
    'runkit_function_add',
    'runkit_function_copy',
    'runkit_function_redefine',
    'runkit_function_rename',
    'session_set_save_handler',
    'set_error_handler',
    'set_exception_handler',
    'spl_autoload',	
    'spl_autoload_register',
    'sqlite_create_aggregate', 
    'sqlite_create_function', 
    'stream_wrapper_register', 
    'uasort',
    'uksort',
    'usort',
    'yaml_parse',
    'yaml_parse_file',
    'yaml_parse_url'
);

$OS_COMMAND_EXECUTING_FUNCTIONS = array(
    'backticks',
    'exec',
    'expect_popen',
    'passthru',
    'pcntl_exec',
    'popen',
    'proc_open',
    'shell_exec',
    'system',
    'mail', // http://esec-pentest.sogeti.com/web/using-mail-remote-code-execution
    'w32api_invoke_function',
    'w32api_register_function',
);