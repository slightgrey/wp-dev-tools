<?php defined( 'ABSPATH' ) || exit;

return function( $c ) {
	at_autoload_module( __DIR__ );

	$c->bind( AT_Copy_Parent_Theme_Admin::class, fn() => new AT_Copy_Parent_Theme_Admin() );
	$c->bind( AT_Copy_Parent_Theme::class, fn( $c ) => new AT_Copy_Parent_Theme( $c->get( AT_Copy_Parent_Theme_Admin::class ) ) );

	return [ 'id' => 'copy-parent-theme', 'class' => AT_Copy_Parent_Theme::class ];
};
