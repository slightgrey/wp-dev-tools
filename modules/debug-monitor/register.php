<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_Debug_Monitor::class, fn() => new AT_Debug_Monitor() );

	return [ 'id' => 'debug-monitor', 'class' => AT_Debug_Monitor::class ];
};
