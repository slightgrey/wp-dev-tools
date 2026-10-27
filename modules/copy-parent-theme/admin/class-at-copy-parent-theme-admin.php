<?php

defined( 'ABSPATH' ) || exit;

class AT_Copy_Parent_Theme_Admin {

	const CHILD_THEME_ZIP_URL = 'https://github.com/elementor/hello-theme-child/archive/refs/heads/master.zip';
	const CHILD_THEME_FOLDER  = 'hello-elementor-child';

	private function transient_key(): string {
		return 'at_cpt_notice_' . get_current_user_id();
	}

	// -------------------------------------------------------------------------
	// admin-post.php handlers (POST)
	// -------------------------------------------------------------------------

	public function handle_install(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'admin-tools' ) );
		}
		check_admin_referer( 'at_install_child_theme' );

		$result = $this->install_child_theme();
		if ( is_wp_error( $result ) ) {
			set_transient( $this->transient_key(), array(
				'type'    => 'install_error',
				'message' => $result->get_error_message(),
				'results' => array(),
			), 60 );
		} else {
			set_transient( $this->transient_key(), array(
				'type'    => 'install_success',
				'message' => '',
				'results' => array(),
			), 60 );
		}

		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		exit;
	}

	public function handle_migrate(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'admin-tools' ) );
		}
		check_admin_referer( 'at_run_theme_migration' );

		$results = $this->run_migration();
		set_transient( $this->transient_key(), array(
			'type'    => 'results',
			'message' => '',
			'results' => $results,
		), 60 );

		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		exit;
	}

	// -------------------------------------------------------------------------
	// admin_notices hook
	// -------------------------------------------------------------------------

	public function admin_notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$theme_root    = get_theme_root();
		$parent_exists = is_dir( $theme_root . '/hello-elementor' );
		$child_exists  = is_dir( $theme_root . '/' . self::CHILD_THEME_FOLDER );

		if ( ! $parent_exists ) {
			return;
		}

		// Read and immediately clear any pending result from a POST action.
		$pending = get_transient( $this->transient_key() );
		if ( $pending ) {
			delete_transient( $this->transient_key() );
			$notice_type     = $pending['type'];
			$install_message = $pending['message'];
			$results         = $pending['results'];
			include AT_MODULES_DIR . 'copy-parent-theme/admin/partials/at-copy-parent-theme-admin-display.php';
			return;
		}

		if ( ! $child_exists ) {
			$notice_type     = 'no_child';
			$install_message = '';
			$results         = array();
			include AT_MODULES_DIR . 'copy-parent-theme/admin/partials/at-copy-parent-theme-admin-display.php';
			return;
		}

		$notice_type     = 'run_link';
		$install_message = '';
		$results         = array();
		include AT_MODULES_DIR . 'copy-parent-theme/admin/partials/at-copy-parent-theme-admin-display.php';
	}

	// -------------------------------------------------------------------------
	// Core operations
	// -------------------------------------------------------------------------

	private function install_child_theme(): true|WP_Error {
		$response = wp_remote_get( self::CHILD_THEME_ZIP_URL, array( 'timeout' => 60 ) );
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'download_failed', 'Could not download child theme: ' . $response->get_error_message() );
		}
		$tmp_zip = sys_get_temp_dir() . '/hello-theme-child.zip';
		file_put_contents( $tmp_zip, wp_remote_retrieve_body( $response ) ); // phpcs:ignore
		$zip = new ZipArchive();
		if ( $zip->open( $tmp_zip ) !== true ) {
			unlink( $tmp_zip );
			return new WP_Error( 'zip_open_failed', 'Could not open downloaded zip.' );
		}
		$tmp_extract = sys_get_temp_dir() . '/hello-theme-child-extract';
		if ( is_dir( $tmp_extract ) ) {
			$this->delete_dir( $tmp_extract );
		}
		$zip->extractTo( $tmp_extract );
		$zip->close();
		unlink( $tmp_zip );
		$extracted_dirs = glob( $tmp_extract . '/hello-theme-child-*', GLOB_ONLYDIR );
		if ( empty( $extracted_dirs ) ) {
			$this->delete_dir( $tmp_extract );
			return new WP_Error( 'extract_failed', 'Could not locate extracted theme folder.' );
		}
		$dest = get_theme_root() . '/' . self::CHILD_THEME_FOLDER;
		$this->copy_dir( $extracted_dirs[0], $dest );
		$this->delete_dir( $tmp_extract );
		return true;
	}

	private function run_migration(): array {
		$results = array();

		$parent_mods = get_option( 'theme_mods_hello-elementor', array() );
		update_option( 'theme_mods_hello-elementor-child', $parent_mods );
		$results[] = '✅ Copied ' . count( $parent_mods ) . ' theme mods (CSS, logo, identity)';

		$menu_locations = $parent_mods['nav_menu_locations'] ?? array();
		$menu_report    = array();
		foreach ( $menu_locations as $location => $menu_id ) {
			$menu          = wp_get_nav_menu_object( $menu_id );
			$menu_report[] = "&nbsp;&nbsp;• " . esc_html( $location ) . " → " . ( $menu ? esc_html( $menu->name ) : "Unknown (ID: $menu_id)" );
		}
		if ( $menu_report ) {
			$results[] = '✅ Menu assignments migrated:<br>' . implode( '<br>', $menu_report );
		}

		$parent_dir    = get_theme_root() . '/hello-elementor';
		$child_dir     = get_theme_root() . '/' . self::CHILD_THEME_FOLDER;
		$theme_version = wp_get_theme( 'hello-elementor' )->get( 'Version' );
		$zip_url       = "https://downloads.wordpress.org/theme/hello-elementor.{$theme_version}.zip";

		$response = wp_remote_get( $zip_url, array( 'timeout' => 60 ) );
		if ( is_wp_error( $response ) ) {
			$results[] = '❌ Could not download original theme for comparison: ' . esc_html( $response->get_error_message() );
			return $results;
		}

		$tmp_zip = sys_get_temp_dir() . '/hello-elementor-original.zip';
		file_put_contents( $tmp_zip, wp_remote_retrieve_body( $response ) ); // phpcs:ignore

		$zip = new ZipArchive();
		if ( $zip->open( $tmp_zip ) !== true ) {
			unlink( $tmp_zip );
			$results[] = '❌ Could not open downloaded theme zip.';
			return $results;
		}

		$modified_files   = array();
		$functions_extras = array();

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$zip_name = $zip->getNameIndex( $i );
			$relative = preg_replace( '#^hello-elementor/#', '', $zip_name );
			if ( empty( $relative ) || str_ends_with( $relative, '/' ) || 'style.css' === $relative ) {
				continue;
			}
			$installed_file = $parent_dir . '/' . $relative;
			if ( ! file_exists( $installed_file ) ) {
				continue;
			}
			$original_content  = $zip->getFromIndex( $i );
			$installed_content = file_get_contents( $installed_file ); // phpcs:ignore
			if ( md5( $original_content ) === md5( $installed_content ) ) {
				continue;
			}
			if ( 'functions.php' === $relative ) {
				$added = $this->diff_added_lines( $original_content, $installed_content );
				if ( ! empty( $added ) ) {
					$functions_extras[] = $added;
				}
				continue;
			}
			$child_file   = $child_dir . '/' . $relative;
			$child_subdir = dirname( $child_file );
			if ( ! is_dir( $child_subdir ) ) {
				wp_mkdir_p( $child_subdir );
			}
			file_put_contents( $child_file, $installed_content ); // phpcs:ignore
			$modified_files[] = esc_html( $relative );
		}

		$zip->close();
		unlink( $tmp_zip );

		if ( $modified_files ) {
			$results[] = '✅ Modified files copied to child theme:<br>&nbsp;&nbsp;• ' . implode( '<br>&nbsp;&nbsp;• ', $modified_files );
		} else {
			$results[] = 'ℹ️ No modified parent theme files detected (other than functions.php)';
		}

		if ( $functions_extras ) {
			$child_functions = $child_dir . '/functions.php';
			$notice          = "\n\n// ---- Migrated from parent theme functions.php ----\n";
			file_put_contents( $child_functions, $notice . implode( "\n", $functions_extras ), FILE_APPEND ); // phpcs:ignore
			$results[] = '✅ <strong>functions.php:</strong> Custom code from parent\'s functions.php was extracted and added to the child theme\'s functions.php.';
		}

		// Link to Admin Tools dashboard to disable this module instead of deactivating the whole plugin.
		$disable_url = admin_url( 'admin.php?page=admin-tools' );
		$results[]   = '👉 Migration complete. This tool is no longer needed — <a href="' . esc_url( $disable_url ) . '">go to Admin Tools</a> and toggle it off to disable it.';

		return $results;
	}

	private function diff_added_lines( string $original, string $modified ): string {
		$original_trimmed = rtrim( $original );
		if ( strpos( $modified, $original_trimmed ) === 0 ) {
			return trim( substr( $modified, strlen( $original_trimmed ) ) );
		}
		$orig_lines     = array_flip( array_map( 'rtrim', explode( "\n", $original ) ) );
		$modified_lines = array_map( 'rtrim', explode( "\n", $modified ) );
		$added          = array();
		foreach ( $modified_lines as $line ) {
			if ( ! isset( $orig_lines[ $line ] ) ) {
				$added[] = $line;
			}
		}
		return trim( implode( "\n", $added ) );
	}

	private function copy_dir( string $src, string $dst ): void {
		wp_mkdir_p( $dst );
		$items = scandir( $src );
		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$src_path = $src . '/' . $item;
			$dst_path = $dst . '/' . $item;
			if ( is_dir( $src_path ) ) {
				$this->copy_dir( $src_path, $dst_path );
			} else {
				copy( $src_path, $dst_path );
			}
		}
	}

	private function delete_dir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$items = scandir( $dir );
		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$path = $dir . '/' . $item;
			if ( is_dir( $path ) ) {
				$this->delete_dir( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}
}
