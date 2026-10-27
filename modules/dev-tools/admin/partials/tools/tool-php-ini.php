<?php defined( 'ABSPATH' ) || exit;

$directives = ini_get_all();
$rows       = array();

foreach ( $directives as $name => $values ) {
	$local  = $values['local_value']  ?? '';
	$global = $values['global_value'] ?? '';

	$rows[] = array(
		'cells' => array(
			'<code style="font-size:11px;">' . esc_html( $name ) . '</code>',
			esc_html( $local  === '' ? '(empty)' : $local ),
			esc_html( $global === '' ? '(empty)' : $global ),
		),
	);
}

at_dt_card( 'PHP.INI', function() use ( $rows ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' directives' ) . '</p>';
	at_dt_table(
		array( 'Directive', 'Local Value', 'Global Value' ),
		$rows,
		array( 'searchable' => true )
	);
}, 'All php.ini configuration directives for this PHP installation.' );
