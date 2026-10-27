<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_Post_Scanner_Admin::class, fn() => new AT_Post_Scanner_Admin() );
	$c->bind( AT_Post_Scanner::class, fn( $c ) => new AT_Post_Scanner( $c->get( AT_Post_Scanner_Admin::class ) ) );

	return [ 'id' => 'post-scanner', 'class' => AT_Post_Scanner::class ];
};
