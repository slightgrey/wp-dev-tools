<?php defined( 'ABSPATH' ) || exit;

$all        = get_defined_constants( true );
$user       = $all['user'] ?? array();
$rows       = array();

foreach ( $user as $name => $value ) {
	if ( is_array( $value ) ) {
		$display = '<em>array</em>';
	} elseif ( is_null( $value ) ) {
		$display = '<em>null</em>';
	} elseif ( is_bool( $value ) ) {
		$display = $value ? '<code>true</code>' : '<code>false</code>';
	} else {
		$str     = (string) $value;
		$display = '<code>' . esc_html( strlen( $str ) > 200 ? substr( $str, 0, 200 ) . '…' : $str ) . '</code>';
	}

	$rows[] = array( 'cells' => array(
		'<code style="font-size:11px;">' . esc_html( $name ) . '</code>',
		$display,
	) );
}

at_dt_card( 'Defined Constants', function() use ( $rows ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' user-defined constants' ) . '</p>';
	at_dt_table( array( 'Constant', 'Value' ), $rows, array( 'searchable' => true ) );
}, 'All constants defined via define() — user scope only.' );
