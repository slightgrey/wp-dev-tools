<?php
defined( 'ABSPATH' ) || exit;

class AT_Dev_Tools_Ajax {

	public function dispatch(): void {
		check_ajax_referer( 'at_dt_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'admin-tools' ) ), 403 );
		}

		$action_type = sanitize_key( $_POST['action_type'] ?? '' );

		switch ( $action_type ) {
			case 'delete_option':
				$this->delete_option();
				break;
			case 'delete_post':
				$this->delete_post();
				break;
			case 'delete_meta':
				$this->delete_meta();
				break;
			case 'destroy_session':
				$this->destroy_session();
				break;
			case 'save_file':
				$this->save_file();
				break;
			case 'clear_log':
				$this->clear_log();
				break;
			case 'download_log':
				$this->download_log();
				break;
			case 'run_php':
				$this->run_php();
				break;
			default:
				wp_send_json_error( array( 'message' => __( 'Unknown action.', 'admin-tools' ) ) );
		}
	}

	private function delete_option(): void {
		$name = sanitize_key( $_POST['name'] ?? '' );

		$protected = array( 'siteurl', 'blogname', 'blogdescription', 'admin_email', 'active_plugins', 'stylesheet', 'template', 'upload_path' );
		if ( in_array( $name, $protected, true ) ) {
			wp_send_json_error( array( 'message' => __( 'This option is protected and cannot be deleted.', 'admin-tools' ) ) );
		}

		delete_option( $name );
		wp_send_json_success();
	}

	private function delete_post(): void {
		$post_id = absint( $_POST['post_id'] ?? 0 );

		if ( ! $post_id || ! wp_delete_post( $post_id, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not delete post.', 'admin-tools' ) ) );
		}

		wp_send_json_success();
	}

	private function delete_meta(): void {
		$type      = sanitize_key( $_POST['meta_type'] ?? '' );
		$object_id = absint( $_POST['object_id'] ?? 0 );
		$meta_key  = sanitize_text_field( wp_unslash( $_POST['meta_key'] ?? '' ) );

		if ( ! in_array( $type, array( 'post', 'user', 'term' ), true ) || ! $object_id || '' === $meta_key ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'admin-tools' ) ) );
		}

		delete_metadata( $type, $object_id, $meta_key );
		wp_send_json_success();
	}

	private function destroy_session(): void {
		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
		$_SESSION = array();
		session_destroy();
		wp_send_json_success();
	}

	private function save_file(): void {
		$target = sanitize_text_field( wp_unslash( $_POST['file'] ?? '' ) );

		$allowed = array(
			wp_normalize_path( ABSPATH . 'wp-config.php' ),
			wp_normalize_path( ABSPATH . '.htaccess' ),
		);

		if ( ! in_array( wp_normalize_path( $target ), $allowed, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file path.', 'admin-tools' ) ) );
		}

		$content = wp_unslash( $_POST['content'] ?? '' );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->put_contents( $target, $content, FS_CHMOD_FILE ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not write to file.', 'admin-tools' ) ) );
		}

		wp_send_json_success();
	}

	private function clear_log(): void {
		$log_path = $this->get_log_path();

		if ( ! $log_path || ! file_exists( $log_path ) ) {
			wp_send_json_error( array( 'message' => __( 'Log file not found.', 'admin-tools' ) ) );
		}

		file_put_contents( $log_path, '' );
		wp_send_json_success();
	}

	private function download_log(): void {
		$log_path = $this->get_log_path();

		if ( ! $log_path || ! file_exists( $log_path ) ) {
			wp_send_json_error( array( 'message' => __( 'Log file not found.', 'admin-tools' ) ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		wp_send_json_success( array( 'content' => file_get_contents( $log_path ) ) );
	}

	private function run_php(): void {
		$code   = wp_unslash( $_POST['code'] ?? '' );
		$output = '';
		$return = null;

		try {
			ob_start();
			// phpcs:ignore Squiz.PHP.Eval.Discouraged
			$return = eval( $code );
			$output = ob_get_clean();
		} catch ( \Throwable $e ) {
			ob_end_clean();
			wp_send_json_success( array(
				'output' => '',
				'error'  => $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
			) );
		}

		wp_send_json_success( array(
			'output' => $output,
			'return' => var_export( $return, true ),
		) );
	}

	public function get_log_path(): string {
		if ( defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) && WP_DEBUG_LOG ) {
			$path = WP_DEBUG_LOG;
		} else {
			$path = WP_CONTENT_DIR . '/debug.log';
		}

		return realpath( $path ) ?: '';
	}
}
