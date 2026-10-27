<?php defined( 'ABSPATH' ) || exit;

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$status_rows = $wpdb->get_results( 'SHOW TABLE STATUS' );

$rows = array();
foreach ( (array) $status_rows as $t ) {
	$engine  = $t->Engine  ?? 'N/A';
	$charset = $t->Collation ? explode( '_', $t->Collation )[0] : 'N/A';
	$rows[]  = array( 'cells' => array(
		'<code style="font-size:11px;">' . esc_html( $t->Name ) . '</code>',
		esc_html( number_format( (int) $t->Rows ) ),
		esc_html( size_format( (int) ( $t->Data_length + $t->Index_length ) ) ),
		esc_html( $engine ),
		esc_html( $charset ),
	) );
}

at_dt_card( 'Database Tables', function() use ( $rows ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' tables in database <strong>' . DB_NAME . '</strong>' ) . '</p>';
	at_dt_table(
		array( 'Table', 'Rows', 'Size', 'Engine', 'Charset' ),
		$rows,
		array( 'searchable' => true )
	);
}, 'All tables in the current database including row counts and disk usage.' );
