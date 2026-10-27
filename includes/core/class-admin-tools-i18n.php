<?php

defined( 'ABSPATH' ) || exit;

class Admin_Tools_i18n {

	public function load_plugin_textdomain(): void {
		load_plugin_textdomain( 'admin-tools', false, dirname( plugin_basename( AT_PLUGIN_FILE ) ) . '/languages/' );
	}
}
