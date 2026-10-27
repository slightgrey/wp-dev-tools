<?php

defined( 'ABSPATH' ) || exit;

class AT_Plugin_Scanner_Admin {

	const SLUG = 'at-plugin-scanner';

	private AT_Plugin_Scanner_Scanner $scanner;
	private string $hook_suffix = '';

	public function __construct( AT_Plugin_Scanner_Scanner $scanner ) {
		$this->scanner = $scanner;
	}

	public function add_plugin_menu(): void {
		$this->hook_suffix = add_submenu_page(
			'admin-tools',
			__( 'Plugin Scanner', 'admin-tools' ),
			__( 'Plugin Scanner', 'admin-tools' ),
			'manage_options',
			self::SLUG,
			array( $this, 'display_plugin_page' )
		);
	}

	public function enqueue_styles( string $hook_suffix ): void {
		if ( $this->hook_suffix !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style(
			'at-plugin-scanner',
			AT_MODULES_URL . 'plugin-scanner/admin/css/plugin-scanner-admin.css',
			array(),
			AT_VERSION
		);
	}

	public function enqueue_scripts( string $hook_suffix ): void {
		if ( $this->hook_suffix !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script(
			'at-plugin-scanner',
			AT_MODULES_URL . 'plugin-scanner/admin/js/plugin-scanner-admin.js',
			array( 'jquery' ),
			AT_VERSION,
			true
		);
		wp_localize_script( 'at-plugin-scanner', 'wps_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'wps_scan_nonce' ),
		) );
	}

	public function display_plugin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require_once AT_MODULES_DIR . 'plugin-scanner/admin/partials/at-plugin-scanner-admin-display.php';
	}

	public function ajax_scan_plugins(): void {
		check_ajax_referer( 'wps_scan_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}
		wp_send_json_success( $this->scanner->scan() );
	}
}
