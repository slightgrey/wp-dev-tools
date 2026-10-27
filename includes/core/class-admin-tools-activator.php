<?php

defined( 'ABSPATH' ) || exit;

class Admin_Tools_Activator {

	public static function activate(): void {
		// Initialize modules option.
		if ( false === get_option( 'admin_tools_enabled_modules', false ) ) {
			add_option( 'admin_tools_enabled_modules', array() );
		}

		flush_rewrite_rules();
	}
}
