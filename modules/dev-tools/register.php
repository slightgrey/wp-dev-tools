<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_Dev_Tools_Ajax::class,  fn() => new AT_Dev_Tools_Ajax() );
	$c->bind( AT_Dev_Tools_Admin::class, fn( $c ) => new AT_Dev_Tools_Admin( $c->get( AT_Dev_Tools_Ajax::class ) ) );
	$c->bind( AT_Dev_Tools::class,       fn( $c ) => new AT_Dev_Tools( $c->get( AT_Dev_Tools_Admin::class ) ) );

	return [ 'id' => 'dev-tools', 'class' => AT_Dev_Tools::class ];
};
