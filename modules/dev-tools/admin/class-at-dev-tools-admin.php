<?php
defined( 'ABSPATH' ) || exit;

class AT_Dev_Tools_Admin {

	const SLUG = 'at-dev-tools';

	private string $hook_suffix = '';
	private AT_Dev_Tools_Ajax $ajax;

	public function __construct( AT_Dev_Tools_Ajax $ajax ) {
		$this->ajax = $ajax;
	}

	public function add_admin_menu(): void {
		$this->hook_suffix = add_submenu_page(
			'admin-tools',
			__( 'Debug Tools', 'admin-tools' ),
			__( 'Debug Tools', 'admin-tools' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render_page' )
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( $this->hook_suffix !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'at-dt-tailwind',
			AT_MODULES_URL . 'dev-tools/admin/js/tailwindcss.js',
			array(),
			AT_VERSION,
			false
		);

		wp_enqueue_style(
			'at-dt-admin',
			AT_MODULES_URL . 'dev-tools/admin/css/dev-tools-admin.css',
			array(),
			AT_VERSION
		);

		wp_enqueue_script(
			'at-dt-admin',
			AT_MODULES_URL . 'dev-tools/admin/js/dev-tools-admin.js',
			array( 'jquery' ),
			AT_VERSION,
			true
		);

		wp_localize_script( 'at-dt-admin', 'atDt', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'at_dt_nonce' ),
		) );
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'admin-tools' ) );
		}
		include AT_MODULES_DIR . 'dev-tools/admin/partials/at-dev-tools-display.php';
	}

	public function handle_ajax(): void {
		$this->ajax->dispatch();
	}

	public function track_online_user(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}
		set_transient( 'at_dt_online_' . get_current_user_id(), time(), 5 * MINUTE_IN_SECONDS );
	}
}
