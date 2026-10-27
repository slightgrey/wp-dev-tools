<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_Send_Mail_Admin::class, fn() => new AT_Send_Mail_Admin() );
	$c->bind( AT_Send_Mail::class, fn( $c ) => new AT_Send_Mail( $c->get( AT_Send_Mail_Admin::class ) ) );

	return [ 'id' => 'send-mail', 'class' => AT_Send_Mail::class ];
};
