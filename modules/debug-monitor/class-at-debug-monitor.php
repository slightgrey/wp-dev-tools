<?php

defined( 'ABSPATH' ) || exit;

class AT_Debug_Monitor implements Module_Interface {

	const SLUG           = 'at-debug-monitor';
	const LOG            = WP_CONTENT_DIR . '/debug.log';
	const NONCE          = 'at_debug_monitor_clear_log_action';
	const NONCE_F        = 'at_debug_monitor_nonce';
	const NONCE_TOGGLE   = 'at_debug_monitor_toggle_debug';
	const NONCE_TOGGLE_F = 'at_debug_monitor_toggle_nonce';

	private string $hook_suffix = '';

	public function boot( Admin_Tools_Loader $loader ): void {
		$loader->add_action( 'admin_menu', $this, 'register_menu' );
		$loader->add_action( 'admin_head', $this, 'admin_styles' );
		$loader->add_action( 'admin_post_at_debug_monitor_clear_log',    $this, 'handle_clear_log' );
		$loader->add_action( 'admin_post_at_debug_monitor_toggle_debug', $this, 'handle_toggle_debug' );
	}

	public function register_menu(): void {
		$this->hook_suffix = add_submenu_page(
			'admin-tools',
			__( 'WP Debug Monitor', 'admin-tools' ),
			__( 'Debug Monitor', 'admin-tools' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'admin-tools' ) );
		}

		$debug_on   = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$log_path   = self::LOG;
		$log_exists = $debug_on && file_exists( $log_path ) && is_readable( $log_path );
		$log_size   = $log_exists ? size_format( filesize( $log_path ) ) : null;
		$log_mtime  = $log_exists ? wp_date( 'j M Y, H:i', filemtime( $log_path ) ) : null;
		$viewing    = isset( $_GET['view_log'] ) && '1' === sanitize_key( $_GET['view_log'] );

		$page_url   = admin_url( 'admin.php?page=' . self::SLUG );
		$toggle_url = $viewing
			? remove_query_arg( 'view_log' )
			: add_query_arg( 'view_log', '1' );

		$config_path     = $this->find_wp_config();
		$config_writable = $config_path && is_writable( $config_path );

		$result  = isset( $_GET['wpdm_result'] ) ? sanitize_key( $_GET['wpdm_result'] ) : '';
		$notices = array(
			'cleared'         => array( 'success', 'Debug log cleared successfully.' ),
			'error'           => array( 'error', 'Could not clear the log file. Check file permissions.' ),
			'denied'          => array( 'error', 'Permission denied.' ),
			'debug_enabled'   => array( 'success', 'Debug mode enabled. wp-config.php updated successfully.' ),
			'debug_disabled'  => array( 'success', 'Debug mode disabled. wp-config.php updated successfully.' ),
			'toggle_error'    => array( 'error', 'Could not update wp-config.php. Check file permissions.' ),
			'toggle_readonly' => array( 'error', 'wp-config.php is not writable. Update it manually via FTP/cPanel.' ),
			'toggle_notfound' => array( 'error', 'wp-config.php could not be located.' ),
		);
		?>
		<div class="wrap wpdm-wrap">
			<h1><?php esc_html_e( 'WP Debug Monitor', 'admin-tools' ); ?></h1>
			<p class="wpdm-subtitle"><?php esc_html_e( 'WordPress debug status and log viewer.', 'admin-tools' ); ?></p>

			<?php if ( $result && isset( $notices[ $result ] ) ) : ?>
				<div class="wpdm-notice is-<?php echo esc_attr( $notices[ $result ][0] ); ?>">
					<?php echo esc_html( $notices[ $result ][1] ); ?>
				</div>
			<?php endif; ?>

			<div class="wpdm-card">
				<div class="wpdm-status-row">
					<span class="wpdm-status-label"><?php esc_html_e( 'Debug Status', 'admin-tools' ); ?></span>
					<span class="wpdm-badge <?php echo $debug_on ? 'is-on' : 'is-off'; ?>">
						<?php echo $debug_on ? esc_html__( 'ON', 'admin-tools' ) : esc_html__( 'OFF', 'admin-tools' ); ?>
					</span>
				</div>

				<?php if ( $debug_on ) : ?>
					<div class="wpdm-meta">
						<p>
							<strong><?php esc_html_e( 'Log file:', 'admin-tools' ); ?></strong>
							<code><?php echo esc_html( $log_path ); ?></code>
						</p>
						<?php if ( $log_exists ) : ?>
							<p>
								<strong><?php esc_html_e( 'Size:', 'admin-tools' ); ?></strong> <?php echo esc_html( $log_size ); ?>
								&nbsp;&nbsp;
								<strong><?php esc_html_e( 'Last modified:', 'admin-tools' ); ?></strong> <?php echo esc_html( $log_mtime ); ?>
							</p>
						<?php else : ?>
							<p class="wpdm-muted"><?php esc_html_e( 'Log file does not exist yet — no errors have been logged.', 'admin-tools' ); ?></p>
						<?php endif; ?>
						<?php if ( defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) ) : ?>
							<p class="wpdm-muted">
								<?php esc_html_e( 'Note: WP_DEBUG_LOG is set to a custom path. This tool only reads the default wp-content/debug.log location.', 'admin-tools' ); ?>
							</p>
						<?php endif; ?>
					</div>

					<div class="wpdm-actions">
						<a href="<?php echo esc_url( $toggle_url ); ?>" class="wpdm-btn">
							<?php echo $viewing ? esc_html__( 'Hide Log', 'admin-tools' ) : esc_html__( 'Show Log', 'admin-tools' ); ?>
						</a>
						<?php if ( $log_exists ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
								<input type="hidden" name="action" value="at_debug_monitor_clear_log">
								<?php wp_nonce_field( self::NONCE, self::NONCE_F ); ?>
								<button type="submit" class="wpdm-btn wpdm-btn-danger"
									onclick="return confirm('<?php esc_attr_e( 'Clear the debug log? This cannot be undone.', 'admin-tools' ); ?>');">
									<?php esc_html_e( 'Clear Log', 'admin-tools' ); ?>
								</button>
							</form>
						<?php endif; ?>
						<?php if ( $config_writable ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
								<input type="hidden" name="action" value="at_debug_monitor_toggle_debug">
								<input type="hidden" name="wpdm_debug_enable" value="0">
								<?php wp_nonce_field( self::NONCE_TOGGLE, self::NONCE_TOGGLE_F ); ?>
								<button type="submit" class="wpdm-btn wpdm-btn-muted"
									onclick="return confirm('<?php esc_attr_e( 'Disable debug mode? This will update wp-config.php.', 'admin-tools' ); ?>');">
									<?php esc_html_e( 'Disable Debug Mode', 'admin-tools' ); ?>
								</button>
							</form>
						<?php endif; ?>
					</div>

				<?php else : ?>
					<div class="wpdm-info-box">
						<p><?php esc_html_e( 'WP_DEBUG is currently disabled. Enable it to start capturing PHP errors to debug.log.', 'admin-tools' ); ?></p>
						<pre class="wpdm-code-sample">define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );</pre>
					</div>
					<div class="wpdm-actions" style="margin-top:16px;">
						<?php if ( $config_writable ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<input type="hidden" name="action" value="at_debug_monitor_toggle_debug">
								<input type="hidden" name="wpdm_debug_enable" value="1">
								<?php wp_nonce_field( self::NONCE_TOGGLE, self::NONCE_TOGGLE_F ); ?>
								<button type="submit" class="wpdm-btn wpdm-btn-success">
									<?php esc_html_e( 'Enable Debug Mode', 'admin-tools' ); ?>
								</button>
							</form>
						<?php else : ?>
							<p class="wpdm-muted">
								<?php
								printf(
									esc_html__( 'wp-config.php is not writable (%s). Update it manually via FTP/cPanel.', 'admin-tools' ),
									'<code>' . esc_html( $config_path ?: 'not found' ) . '</code>'
								);
								?>
							</p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $debug_on && $viewing ) : ?>
				<?php if ( $log_exists ) : ?>
					<div class="wpdm-card wpdm-log-card">
						<h2><?php esc_html_e( 'debug.log', 'admin-tools' ); ?></h2>
						<?php
						$raw = file_get_contents( $log_path ); // phpcs:ignore
						if ( false === $raw ) {
							echo '<p class="wpdm-muted">' . esc_html__( 'Could not read the log file.', 'admin-tools' ) . '</p>';
						} elseif ( '' === $raw ) {
							echo '<p class="wpdm-muted">' . esc_html__( 'The log file is empty.', 'admin-tools' ) . '</p>';
						} else {
							$truncated = false;
							if ( strlen( $raw ) > 51200 ) {
								$raw       = substr( $raw, -51200 );
								$truncated = true;
							}
							if ( $truncated ) {
								echo '<p class="wpdm-muted wpdm-truncated-notice">' . esc_html__( '[ Log truncated — showing last 50 KB ]', 'admin-tools' ) . '</p>';
							}
							echo '<pre class="wpdm-log-viewer">' . esc_html( $raw ) . '</pre>';
						}
						?>
					</div>
				<?php else : ?>
					<div class="wpdm-notice is-warning">
						<?php
						printf(
							esc_html__( 'No debug log file found at %s. This is normal when WP_DEBUG_LOG is enabled but no errors have occurred yet.', 'admin-tools' ),
							'<code>' . esc_html( $log_path ) . '</code>'
						);
						?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function handle_clear_log(): void {
		$page_url = admin_url( 'admin.php?page=' . self::SLUG );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( add_query_arg( 'wpdm_result', 'denied', $page_url ) );
			exit;
		}

		check_admin_referer( self::NONCE, self::NONCE_F );

		$log_path = self::LOG;
		if ( ! file_exists( $log_path ) || ! is_writable( $log_path ) ) {
			wp_safe_redirect( add_query_arg( 'wpdm_result', 'error', $page_url ) );
			exit;
		}

		file_put_contents( $log_path, '' ); // phpcs:ignore
		wp_safe_redirect( add_query_arg( 'wpdm_result', 'cleared', $page_url ) );
		exit;
	}

	public function handle_toggle_debug(): void {
		$page_url = admin_url( 'admin.php?page=' . self::SLUG );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( add_query_arg( 'wpdm_result', 'denied', $page_url ) );
			exit;
		}

		check_admin_referer( self::NONCE_TOGGLE, self::NONCE_TOGGLE_F );

		$enable = isset( $_POST['wpdm_debug_enable'] ) && '1' === $_POST['wpdm_debug_enable'];
		$result = $this->apply_wp_config_debug( $enable );

		if ( true === $result ) {
			$key = $enable ? 'debug_enabled' : 'debug_disabled';
		} elseif ( 'not_writable' === $result ) {
			$key = 'toggle_readonly';
		} elseif ( 'not_found' === $result ) {
			$key = 'toggle_notfound';
		} else {
			$key = 'toggle_error';
		}

		wp_safe_redirect( add_query_arg( 'wpdm_result', $key, $page_url ) );
		exit;
	}

	private function apply_wp_config_debug( bool $enable ): true|string {
		$config_path = $this->find_wp_config();
		if ( ! $config_path ) {
			return 'not_found';
		}
		if ( ! is_writable( $config_path ) ) {
			return 'not_writable';
		}

		$content = file_get_contents( $config_path ); // phpcs:ignore
		if ( false === $content ) {
			return 'read_error';
		}

		// Toggle WP_DEBUG.
		$content = $this->set_define( $content, 'WP_DEBUG', $enable ? 'true' : 'false', 'WP_DEBUG' );

		// Toggle WP_DEBUG_LOG — insert after WP_DEBUG if missing.
		$content = $this->set_define( $content, 'WP_DEBUG_LOG', $enable ? 'true' : 'false', 'WP_DEBUG' );

		// Toggle WP_DEBUG_DISPLAY — insert after WP_DEBUG_LOG if missing.
		$content = $this->set_define( $content, 'WP_DEBUG_DISPLAY', $enable ? 'false' : 'true', 'WP_DEBUG_LOG' );

		if ( false === file_put_contents( $config_path, $content ) ) { // phpcs:ignore
			return 'write_error';
		}

		return true;
	}

	private function set_define( string $content, string $constant, string $value, string $insert_after ): string {
		$pattern = '/define\s*\(\s*[\'"]' . preg_quote( $constant, '/' ) . '[\'"]\s*,\s*(true|false)\s*\)\s*;/i';

		if ( preg_match( $pattern, $content ) ) {
			return preg_replace( $pattern, "define( '{$constant}', {$value} );", $content );
		}

		// Not found — insert a new line immediately after the $insert_after define.
		$after_pattern = '/(define\s*\(\s*[\'"]' . preg_quote( $insert_after, '/' ) . '[\'"]\s*,\s*(?:true|false)\s*\)\s*;)/i';
		if ( preg_match( $after_pattern, $content ) ) {
			return preg_replace( $after_pattern, "$1\ndefine( '{$constant}', {$value} );", $content, 1 );
		}

		return $content;
	}

	private function find_wp_config(): string {
		$path = ABSPATH . 'wp-config.php';
		if ( file_exists( $path ) ) {
			return $path;
		}
		// Some installs place wp-config.php one directory above ABSPATH.
		$parent = dirname( ABSPATH ) . '/wp-config.php';
		if ( file_exists( $parent ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
			return $parent;
		}
		return '';
	}

	public function admin_styles(): void {
		$screen = get_current_screen();
		if ( ! $screen || $this->hook_suffix !== $screen->id ) {
			return;
		}
		?>
		<style>
		.wpdm-wrap { max-width: 900px; }
		.wpdm-wrap .wpdm-subtitle { margin-top: -8px; color: #71717a; font-size: 13px; }
		.wpdm-card { background: #fff; border: 1px solid #e4e4e7; border-radius: 10px; padding: 24px 28px; margin-top: 20px; }
		.wpdm-log-card { margin-top: 16px; }
		.wpdm-log-card h2 { margin-top: 0; font-size: 14px; font-weight: 600; color: #3f3f46; margin-bottom: 12px; }
		.wpdm-status-row { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
		.wpdm-status-label { font-size: 15px; font-weight: 600; color: #18181b; }
		.wpdm-badge { display: inline-block; padding: 3px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
		.wpdm-badge.is-on { background: #16a34a; color: #fff; }
		.wpdm-badge.is-off { background: #71717a; color: #fff; }
		.wpdm-meta { margin-bottom: 16px; }
		.wpdm-meta p { margin: 4px 0; font-size: 13px; color: #3f3f46; }
		.wpdm-meta code { background: #f4f4f5; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
		.wpdm-muted { color: #71717a !important; font-size: 12px !important; }
		.wpdm-truncated-notice { margin-bottom: 8px; font-style: italic; }
		.wpdm-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
		.wpdm-btn { display: inline-block; padding: 7px 16px; background: #18181b; color: #fff !important; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; line-height: 1.4; }
		.wpdm-btn:hover { background: #3f3f46; color: #fff !important; }
		.wpdm-btn-danger { background: #dc2626; }
		.wpdm-btn-danger:hover { background: #b91c1c; }
		.wpdm-btn-success { background: #16a34a; }
		.wpdm-btn-success:hover { background: #15803d; }
		.wpdm-btn-muted { background: #71717a; }
		.wpdm-btn-muted:hover { background: #52525b; }
		.wpdm-info-box { background: #f4f4f5; border-radius: 8px; padding: 16px 20px; margin-top: 8px; }
		.wpdm-info-box p { margin: 0 0 12px; font-size: 13px; color: #3f3f46; }
		.wpdm-code-sample { background: #18181b; color: #e4e4e7; padding: 14px 16px; border-radius: 6px; font-size: 12px; line-height: 1.7; margin: 0; overflow-x: auto; }
		.wpdm-log-viewer { background: #18181b; color: #e4e4e7; padding: 16px; border-radius: 6px; font-size: 12px; line-height: 1.6; max-height: 500px; overflow: auto; margin: 0; white-space: pre-wrap; word-break: break-all; }
		.wpdm-notice { padding: 10px 16px; border-radius: 6px; font-size: 13px; margin-top: 16px; margin-bottom: 4px; }
		.wpdm-notice.is-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
		.wpdm-notice.is-error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
		.wpdm-notice.is-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
		</style>
		<?php
	}
}
