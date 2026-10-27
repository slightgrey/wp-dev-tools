<?php

defined( 'ABSPATH' ) || exit;

class AT_Post_Scanner implements Module_Interface {

	private AT_Post_Scanner_Admin $admin;

	public function __construct( AT_Post_Scanner_Admin $admin ) {
		$this->admin = $admin;
	}

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'rest_api_init',        $this->admin, 'register_rest_routes' );
		$loader->add_action( 'admin_menu',            $this->admin, 'add_admin_menu' );
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );
	}
}
