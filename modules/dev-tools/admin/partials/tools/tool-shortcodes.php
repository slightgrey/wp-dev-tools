<?php defined( 'ABSPATH' ) || exit;

global $shortcode_tags;

$rows = array();
foreach ( (array) $shortcode_tags as $tag => $callback ) {
	if ( is_array( $callback ) ) {
		$cb_label = esc_html( get_class( $callback[0] ) . '::' . $callback[1] );
	} elseif ( is_string( $callback ) ) {
		$cb_label = esc_html( $callback );
	} else {
		$cb_label = '<em>closure</em>';
	}

	$rows[] = array( 'cells' => array(
		'<code style="font-size:11px;">[' . esc_html( $tag ) . ']</code>',
		'<code style="font-size:11px;">' . $cb_label . '</code>',
	) );
}

at_dt_card( 'Registered Shortcodes', function() use ( $rows ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' registered shortcodes' ) . '</p>';
	at_dt_table( array( 'Tag', 'Callback' ), $rows, array( 'searchable' => true ) );
} );
