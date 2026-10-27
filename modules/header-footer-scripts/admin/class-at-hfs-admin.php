<?php

defined( 'ABSPATH' ) || exit;

class AT_HFS_Admin {

	private string $hook_suffix = '';

	private function is_hfs_screen( string $hook ): bool {
		if ( $this->hook_suffix && $this->hook_suffix === $hook ) {
			return true;
		}
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			$enabled = get_option( 'hfs_enabled_post_types', array() );
			$screen  = get_current_screen();
			if ( $screen && in_array( $screen->post_type, (array) $enabled, true ) ) {
				return true;
			}
		}
		return false;
	}

	public function enqueue_styles( string $hook ): void {
		if ( ! $this->is_hfs_screen( $hook ) ) {
			return;
		}
		wp_enqueue_style(
			'at-hfs-admin',
			AT_MODULES_URL . 'header-footer-scripts/admin/css/hfs-admin.css',
			array(),
			AT_VERSION
		);
	}

	public function enqueue_scripts( string $hook ): void {
		if ( ! $this->is_hfs_screen( $hook ) ) {
			return;
		}
		wp_enqueue_script(
			'at-hfs-admin',
			AT_MODULES_URL . 'header-footer-scripts/admin/js/hfs-admin.js',
			array( 'jquery' ),
			AT_VERSION,
			true
		);
	}

	public function add_settings_page(): void {
		$this->hook_suffix = add_submenu_page(
			'admin-tools',
			__( 'Header Footer Scripts', 'admin-tools' ),
			__( 'Header Footer Scripts', 'admin-tools' ),
			'manage_options',
			'header-footer-scripts',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include AT_MODULES_DIR . 'header-footer-scripts/admin/partials/hfs-admin-settings.php';
	}

	public function register_settings(): void {
		register_setting( 'hfs_settings', 'hfs_enabled_post_types', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_enabled_post_types' ),
			'default'           => array( 'post', 'page' ),
		) );
		register_setting( 'hfs_settings', 'hfs_global_header_scripts', array(
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'sanitize_script_content' ),
			'default'           => '',
		) );
		register_setting( 'hfs_settings', 'hfs_global_footer_scripts', array(
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'sanitize_script_content' ),
			'default'           => '',
		) );
	}

	public function sanitize_enabled_post_types( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}
		$registered = get_post_types( array( 'public' => true ) );
		unset( $registered['attachment'] );
		return array_values( array_intersect( array_map( 'sanitize_key', $input ), array_keys( $registered ) ) );
	}

	public function sanitize_script_content( $input ): string {
		$input = (string) $input;
		return str_replace( "\0", '', $input );
	}

	public function add_meta_boxes(): void {
		$enabled_post_types = get_option( 'hfs_enabled_post_types', array() );
		foreach ( (array) $enabled_post_types as $post_type ) {
			add_meta_box(
				'hfs_scripts',
				__( 'Header and Footer Scripts', 'admin-tools' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	public function render_meta_box( $post ): void {
		include AT_MODULES_DIR . 'header-footer-scripts/admin/partials/hfs-admin-meta-box.php';
	}

	public function save_meta_box_data( int $post_id, $post ): void {
		if ( ! isset( $_POST['hfs_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hfs_meta_box_nonce'] ) ), 'hfs_save_meta_box_' . $post_id )
		) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$enabled = (array) get_option( 'hfs_enabled_post_types', array() );
		if ( ! in_array( $post->post_type, $enabled, true ) ) {
			return;
		}
		$header_scripts = isset( $_POST['hfs_header_scripts'] ) ? wp_unslash( $_POST['hfs_header_scripts'] ) : '';
		update_post_meta( $post_id, '_hfs_header_scripts', $header_scripts );
		$footer_scripts = isset( $_POST['hfs_footer_scripts'] ) ? wp_unslash( $_POST['hfs_footer_scripts'] ) : '';
		update_post_meta( $post_id, '_hfs_footer_scripts', $footer_scripts );
	}
}
