<?php defined( 'ABSPATH' ) || exit;

$rows = array(
	array( 'cells' => array( 'WordPress Version',     get_bloginfo( 'version' ) ) ),
	array( 'cells' => array( 'PHP Version',           PHP_VERSION ) ),
	array( 'cells' => array( 'PHP OS',                PHP_OS ) ),
	array( 'cells' => array( 'PHP SAPI',              php_sapi_name() ) ),
	array( 'cells' => array( 'Server Software',       $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ) ),
	array( 'cells' => array( 'Server Protocol',       $_SERVER['SERVER_PROTOCOL'] ?? 'N/A' ) ),
	array( 'cells' => array( 'Document Root',         $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ) ),
	array( 'cells' => array( 'PHP Memory Limit',      ini_get( 'memory_limit' ) ) ),
	array( 'cells' => array( 'WP Memory Limit',       WP_MEMORY_LIMIT ) ),
	array( 'cells' => array( 'WP Max Memory Limit',   WP_MAX_MEMORY_LIMIT ) ),
	array( 'cells' => array( 'Max Execution Time',    ini_get( 'max_execution_time' ) . 's' ) ),
	array( 'cells' => array( 'Max Input Time',        ini_get( 'max_input_time' ) . 's' ) ),
	array( 'cells' => array( 'Max Input Vars',        ini_get( 'max_input_vars' ) ) ),
	array( 'cells' => array( 'Upload Max Filesize',   ini_get( 'upload_max_filesize' ) ) ),
	array( 'cells' => array( 'Post Max Size',         ini_get( 'post_max_size' ) ) ),
	array( 'cells' => array( 'Default Charset',       ini_get( 'default_charset' ) ) ),
	array( 'cells' => array( 'Allow URL Fopen',       ini_get( 'allow_url_fopen' ) ? 'On' : 'Off' ) ),
	array( 'cells' => array( 'cURL Version',          function_exists( 'curl_version' ) ? ( curl_version()['version'] ?? 'N/A' ) : 'Not available' ) ),
	array( 'cells' => array( 'OpenSSL Version',       defined( 'OPENSSL_VERSION_TEXT' ) ? OPENSSL_VERSION_TEXT : 'N/A' ) ),
	array( 'cells' => array( 'MySQL Client Version',  function_exists( 'mysqli_get_client_info' ) ? mysqli_get_client_info() : 'N/A' ) ),
	array( 'cells' => array( 'Database Host',         DB_HOST ) ),
	array( 'cells' => array( 'Database Name',         DB_NAME ) ),
	array( 'cells' => array( 'Database Charset',      DB_CHARSET ) ),
	array( 'cells' => array( 'Table Prefix',          $GLOBALS['wpdb']->prefix ) ),
	array( 'cells' => array( 'WordPress Debug',       defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled' ) ),
	array( 'cells' => array( 'WordPress Debug Log',   defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? 'Enabled' : 'Disabled' ) ),
	array( 'cells' => array( 'ABSPATH',               ABSPATH ) ),
	array( 'cells' => array( 'WP_CONTENT_DIR',        WP_CONTENT_DIR ) ),
	array( 'cells' => array( 'WPINC',                 WPINC ) ),
);

at_dt_card( 'Server Info', function() use ( $rows ) {
	at_dt_table( array( 'Parameter', 'Value' ), $rows, array( 'searchable' => true ) );
} );
