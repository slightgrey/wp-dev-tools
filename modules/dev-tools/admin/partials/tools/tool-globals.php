<?php defined( 'ABSPATH' ) || exit;

$rows = array();
foreach ( $GLOBALS as $key => $value ) {
	$type = gettype( $value );

	if ( is_object( $value ) ) {
		$display = '<em>' . esc_html( get_class( $value ) ) . ' object</em>';
	} elseif ( is_array( $value ) ) {
		$display = '<em>array (' . count( $value ) . ' items)</em>';
	} elseif ( is_null( $value ) ) {
		$display = '<em>null</em>';
	} elseif ( is_bool( $value ) ) {
		$display = $value ? '<code>true</code>' : '<code>false</code>';
	} else {
		$str     = (string) $value;
		$display = '<code>' . esc_html( strlen( $str ) > 120 ? substr( $str, 0, 120 ) . '…' : $str ) . '</code>';
	}

	$rows[] = array(
		'cells' => array(
			'<code style="font-size:11px;">' . esc_html( '$' . $key ) . '</code>',
			'<span style="color:#6b7280;">' . esc_html( $type ) . '</span>',
			$display,
		),
	);
}

at_dt_card( 'Global Variables', function() use ( $rows ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' globals' ) . '</p>';
	at_dt_table( array( 'Variable', 'Type', 'Value' ), $rows, array( 'searchable' => true ) );
}, 'All entries currently in $GLOBALS.' );
