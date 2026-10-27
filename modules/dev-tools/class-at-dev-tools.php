<?php
defined( 'ABSPATH' ) || exit;

class AT_Dev_Tools implements Module_Interface {

	private AT_Dev_Tools_Admin $admin;

	public function __construct( AT_Dev_Tools_Admin $admin ) {
		$this->admin = $admin;
	}

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'admin_menu',            $this->admin, 'add_admin_menu' );
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_assets' );
		$loader->add_action( 'wp_ajax_at_dt_action',  $this->admin, 'handle_ajax' );
		$loader->add_action( 'admin_init',            $this->admin, 'track_online_user' );
	}
}
