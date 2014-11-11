<?php
namespace Phortress\Dephenses;
/* *
 * Contains the predefined PHP variables and function names
 */

$FILE_WRITE_FUNCTIONS = array(
    'fwrite',
    'fpassthru',
    'rewind'
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