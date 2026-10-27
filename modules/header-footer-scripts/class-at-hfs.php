<?php

defined( 'ABSPATH' ) || exit;

class AT_HFS implements Module_Interface {

	private AT_HFS_Admin  $admin;
	private AT_HFS_Public $public;

	public function __construct( AT_HFS_Admin $admin, AT_HFS_Public $public ) {
		$this->admin  = $admin;
		$this->public = $public;
	}

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_styles' );
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );
		$loader->add_action( 'admin_menu',             $this->admin, 'add_settings_page' );
		$loader->add_action( 'admin_init',             $this->admin, 'register_settings' );
		$loader->add_action( 'add_meta_boxes',         $this->admin, 'add_meta_boxes' );
		$loader->add_action( 'save_post',              $this->admin, 'save_meta_box_data', 10, 2 );

		$loader->add_action( 'wp_head',   $this->public, 'inject_header_scripts', 999 );
		$loader->add_action( 'wp_footer', $this->public, 'inject_footer_scripts', 999 );
	}
}
