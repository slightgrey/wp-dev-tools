<?php

defined( 'ABSPATH' ) || exit;

class Admin_Tools_Container {

	private array $bindings  = [];
	private array $instances = [];

	public function bind( string $id, callable $factory ): void {
		$this->bindings[ $id ] = $factory;
	}

	public function get( string $id ): mixed {
		if ( ! isset( $this->instances[ $id ] ) ) {
			if ( ! isset( $this->bindings[ $id ] ) ) {
				throw new \RuntimeException( "Admin Tools DI: No binding registered for '{$id}'." );
			}
			$this->instances[ $id ] = ( $this->bindings[ $id ] )( $this );
		}
		return $this->instances[ $id ];
	}

	public function has( string $id ): bool {
		return isset( $this->bindings[ $id ] ) || isset( $this->instances[ $id ] );
	}
}
