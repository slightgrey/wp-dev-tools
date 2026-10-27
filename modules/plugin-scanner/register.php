<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_Plugin_Scanner_Scanner::class, fn() => new AT_Plugin_Scanner_Scanner() );
	$c->bind( AT_Plugin_Scanner_Admin::class, fn( $c ) => new AT_Plugin_Scanner_Admin( $c->get( AT_Plugin_Scanner_Scanner::class ) ) );
	$c->bind( AT_Plugin_Scanner::class, fn( $c ) => new AT_Plugin_Scanner( $c->get( AT_Plugin_Scanner_Admin::class ) ) );

	return [ 'id' => 'plugin-scanner', 'class' => AT_Plugin_Scanner::class ];
};
