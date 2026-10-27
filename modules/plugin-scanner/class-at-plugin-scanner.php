<?php

defined( 'ABSPATH' ) || exit;

class AT_Plugin_Scanner implements Module_Interface {

	private AT_Plugin_Scanner_Admin $admin;

	public function __construct( AT_Plugin_Scanner_Admin $admin ) {
		$this->admin = $admin;
	}

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'admin_menu',           $this->admin, 'add_plugin_menu' );
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_styles' );
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );
		$loader->add_action( 'wp_ajax_at_wps_scan_plugins', $this->admin, 'ajax_scan_plugins' );
	}
}
