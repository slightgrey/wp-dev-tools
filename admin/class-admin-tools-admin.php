<?php

defined( 'ABSPATH' ) || exit;

class Admin_Tools_Admin {

	public function add_menu_page(): void {
		add_menu_page(
			__( 'WP Dev Tools', 'admin-tools' ),
			__( 'WP Dev Tools', 'admin-tools' ),
			'manage_options',
			'admin-tools',
			array( $this, 'render_dashboard' ),
			'dashicons-admin-tools',
			3
		);
	}

	public function render_dashboard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'admin-tools' ) );
		}
		include AT_PLUGIN_DIR . 'admin/partials/admin-tools-admin-display.php';
	}

	public function enqueue_assets( string $hook ): void {
		if ( 'toplevel_page_admin-tools' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'admin-tools',
			AT_PLUGIN_URL . 'assets/admin-tools-admin.css',
			array(),
			AT_VERSION
		);
	}

	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'admin-tools' ) );
		}

		check_admin_referer( 'admin_tools_save_settings' );

		$admin_tool_ids = array( 'debug-monitor', 'dev-tools', 'plugin-scanner', 'post-scanner', 'send-mail', 'user-switching' );
		$site_tool_ids  = array( 'copy-parent-theme', 'header-footer-scripts' );
		$valid_ids      = array_merge( $admin_tool_ids, $site_tool_ids );

		$is_admin = in_array( 'administrator', (array) wp_get_current_user()->roles, true );
		$posted   = array_map( 'sanitize_key', (array) ( $_POST['at_modules'] ?? array() ) );
		$posted   = array_intersect( $posted, $valid_ids );

		if ( $is_admin ) {
			$enabled = array_values( $posted );
		} else {
			// Non-admins may only change site tools; admin tool states are preserved.
			$current = (array) get_option( 'admin_tools_enabled_modules', array() );
			$enabled = array_values( array_unique( array_merge(
				array_intersect( $current, $admin_tool_ids ),
				array_intersect( $posted, $site_tool_ids )
			) ) );
		}

		update_option( 'admin_tools_enabled_modules', $enabled );

		wp_safe_redirect(
			add_query_arg( array( 'page' => 'admin-tools', 'at_saved' => '1' ), admin_url( 'admin.php' ) )
		);
		exit;
	}
}
