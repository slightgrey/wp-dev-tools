<?php

defined( 'ABSPATH' ) || exit;

class AT_Send_Mail_Admin {

	const SLUG = 'at-send-mail';

	private string $hook_suffix = '';

	public function add_admin_menu(): void {
		$this->hook_suffix = add_submenu_page(
			'admin-tools',
			__( 'Send Mail', 'admin-tools' ),
			__( 'Send Mail', 'admin-tools' ),
			'manage_options',
			self::SLUG,
			array( $this, 'display_admin_page' )
		);
	}

	public function enqueue_styles( string $hook ): void {
		if ( $this->hook_suffix !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'at-send-mail',
			AT_MODULES_URL . 'send-mail/admin/css/send-mail-admin.css',
			array(),
			AT_VERSION
		);
	}

	public function enqueue_scripts( string $hook ): void {
		if ( $this->hook_suffix !== $hook ) {
			return;
		}
		wp_enqueue_script(
			'at-send-mail',
			AT_MODULES_URL . 'send-mail/admin/js/send-mail-admin.js',
			array( 'jquery' ),
			AT_VERSION,
			true
		);
	}

	public function display_admin_page(): void {
		include AT_MODULES_DIR . 'send-mail/admin/partials/at-send-mail-admin-display.php';
	}

	public function handle_send_mail(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'admin-tools' ) );
		}

		check_admin_referer( 'smwm_send_mail_action', 'smwm_nonce' );

		$to      = sanitize_email( $_POST['smwm_to'] ?? '' );
		$subject = sanitize_text_field( $_POST['smwm_subject'] ?? '' );
		$format  = isset( $_POST['smwm_format'] ) && 'html' === $_POST['smwm_format'] ? 'html' : 'plain';

		if ( 'html' === $format ) {
			$message = wp_kses_post( $_POST['smwm_message_html'] ?? '' );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		} else {
			$message = sanitize_textarea_field( $_POST['smwm_message_plain'] ?? '' );
			$headers = array();
		}

		$redirect_url = add_query_arg( array( 'page' => self::SLUG ), admin_url( 'admin.php' ) );

		if ( empty( $to ) || empty( $subject ) || empty( $message ) ) {
			wp_safe_redirect( add_query_arg( 'smwm_error', 'missing_fields', $redirect_url ) );
			exit;
		}

		$sent = wp_mail( $to, $subject, $message, $headers );

		if ( $sent ) {
			wp_safe_redirect( add_query_arg( 'smwm_sent', '1', $redirect_url ) );
		} else {
			wp_safe_redirect( add_query_arg( 'smwm_error', 'send_failed', $redirect_url ) );
		}
		exit;
	}
}
