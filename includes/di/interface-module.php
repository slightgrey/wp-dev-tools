<?php

defined( 'ABSPATH' ) || exit;

interface Module_Interface {
	/**
	 * Register all WordPress hooks for this module via the shared loader.
	 */
	public function boot( Admin_Tools_Loader $loader ): void;
}
