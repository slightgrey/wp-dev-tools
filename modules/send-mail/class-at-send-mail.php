<?php

defined( 'ABSPATH' ) || exit;

class AT_Send_Mail implements Module_Interface {

	private AT_Send_Mail_Admin $admin;

	public function __construct( AT_Send_Mail_Admin $admin ) {
		$this->admin = $admin;
	}

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'admin_menu',           $this->admin, 'add_admin_menu' );
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_styles' );
		$loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );
		$loader->add_action( 'admin_post_smwm_send_mail', $this->admin, 'handle_send_mail' );
	}
}
