<?php defined( 'ABSPATH' ) || exit;

function at_autoload_module( string $dir ): void {
	spl_autoload_register( function( string $class ) use ( $dir ): void {
		if ( 0 !== strpos( $class, 'AT_' ) ) {
			return;
		}
		$file       = 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
		$candidates = [
			$dir . '/' . $file,
			$dir . '/admin/' . $file,
			$dir . '/public/' . $file,
		];
		foreach ( $candidates as $path ) {
			if ( file_exists( $path ) ) {
				require_once $path;
				return;
			}
		}
	} );
}
