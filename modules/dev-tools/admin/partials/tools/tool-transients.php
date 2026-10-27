<?php defined( 'ABSPATH' ) || exit;

global $wpdb;

// Fetch non-timeout transient rows.
$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	"SELECT option_name, LENGTH(option_value) AS size_bytes
	 FROM {$wpdb->options}
	 WHERE option_name LIKE '_transient_%'
	   AND option_name NOT LIKE '_transient_timeout_%'
	 ORDER BY option_name
	 LIMIT 500"
);

$rows = array();
foreach ( (array) $results as $row ) {
	$name           = $row->option_name;
	$transient_key  = preg_replace( '/^_transient_/', '', $name );
	$timeout_key    = '_transient_timeout_' . $transient_key;
	$expiry         = get_option( $timeout_key );

	if ( $expiry ) {
		$diff    = (int) $expiry - time();
		$exp_str = $diff > 0
			? esc_html( 'Expires in ' . human_time_diff( time(), (int) $expiry ) )
			: '<span style="color:#dc2626;">Expired</span>';
	} else {
		$exp_str = '<span style="color:#9ca3af;">No expiry</span>';
	}

	$rows[] = array(
		'cells'  => array(
			'<code style="font-size:11px;">' . esc_html( $transient_key ) . '</code>',
			esc_html( number_format( $row->size_bytes ) . ' bytes' ),
			$exp_str,
		),
		'action' => 'delete_transient',
		'data'   => array( 'name' => $name ),
	);
}

at_dt_card( 'Transients', function() use ( $rows ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' transients' ) . ' — Deletes the transient option row immediately.</p>';
	at_dt_table(
		array( 'Transient Key', 'Size', 'Expiry' ),
		$rows,
		array( 'searchable' => true, 'actions_col' => true )
	);
}, 'Transients stored in wp_options. Site transients (multisite) are not shown here.' );
