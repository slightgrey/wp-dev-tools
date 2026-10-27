<?php defined( 'ABSPATH' ) || exit;

class AT_User_Switching implements Module_Interface {

	private AT_User_Switching_Admin $admin;

	public function __construct( AT_User_Switching_Admin $admin ) {
		$this->admin = $admin;
	}

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'init',             $this->admin, 'handle_actions' );
		$loader->add_action( 'admin_bar_menu',   $this->admin, 'admin_bar_node', 100 );
		$loader->add_action( 'admin_notices',    $this->admin, 'switched_notice' );
		$loader->add_action( 'admin_head',       $this->admin, 'inline_styles' );
		$loader->add_action( 'wp_head',          $this->admin, 'inline_styles' );
		$loader->add_filter( 'user_row_actions', $this->admin, 'user_row_action', 10, 2 );
	}
}
