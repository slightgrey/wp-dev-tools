<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_HFS_Admin::class,  fn() => new AT_HFS_Admin() );
	$c->bind( AT_HFS_Public::class, fn() => new AT_HFS_Public() );
	$c->bind( AT_HFS::class, fn( $c ) => new AT_HFS( $c->get( AT_HFS_Admin::class ), $c->get( AT_HFS_Public::class ) ) );

	return [ 'id' => 'header-footer-scripts', 'class' => AT_HFS::class ];
};
