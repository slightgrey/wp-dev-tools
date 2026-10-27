<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_User_Switching_Admin::class, fn() => new AT_User_Switching_Admin() );
	$c->bind( AT_User_Switching::class, fn( $c ) => new AT_User_Switching( $c->get( AT_User_Switching_Admin::class ) ) );

	return [ 'id' => 'user-switching', 'class' => AT_User_Switching::class ];
};
