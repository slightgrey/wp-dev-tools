<?php

defined( 'ABSPATH' ) || exit;

class AT_Copy_Parent_Theme implements Module_Interface {

	private AT_Copy_Parent_Theme_Admin $admin;

	public function __construct( AT_Copy_Parent_Theme_Admin $admin ) {
		$this->admin = $admin;
	}

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'admin_notices',                    $this->admin, 'admin_notices' );
		$loader->add_action( 'admin_post_at_install_child_theme', $this->admin, 'handle_install' );
		$loader->add_action( 'admin_post_at_run_theme_migration', $this->admin, 'handle_migrate' );
	}
}
