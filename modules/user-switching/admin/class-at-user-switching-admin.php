<?php defined( 'ABSPATH' ) || exit;

class AT_User_Switching_Admin {

	private const COOKIE = 'at_us_origin';

	// -------------------------------------------------------------------------
	// Action handler (runs on init — before any output)
	// -------------------------------------------------------------------------

	public function handle_actions(): void {
		$action = sanitize_key( $_GET['at_us_action'] ?? '' );
		if ( ! $action ) {
			return;
		}

		if ( 'switch_to' === $action ) {
			$user_id = absint( $_GET['user_id'] ?? 0 );
			if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'at_us_switch_to_' . $user_id ) ) {
				wp_die( esc_html__( 'Security check failed.', 'admin-tools' ) );
			}
			$this->do_switch_to( $user_id );
		} elseif ( 'switch_back' === $action ) {
			if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'at_us_switch_back' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'admin-tools' ) );
			}
			$this->do_switch_back();
		}
	}

	// -------------------------------------------------------------------------
	// Switch logic
	// -------------------------------------------------------------------------

	private function do_switch_to( int $user_id ): void {
		if ( $this->read_cookie() !== null ) {
			wp_die( esc_html__( 'Already in a switched session. Switch back first.', 'admin-tools' ) );
		}

		if ( ! in_array( 'administrator', (array) wp_get_current_user()->roles, true ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'admin-tools' ) );
		}

		$target = get_userdata( $user_id );
		if ( ! $target ) {
			wp_die( esc_html__( 'User not found.', 'admin-tools' ) );
		}

		if ( in_array( 'administrator', (array) $target->roles, true ) ) {
			wp_die( esc_html__( 'Cannot switch to an administrator account.', 'admin-tools' ) );
		}

		setcookie( self::COOKIE, $this->make_cookie( get_current_user_id() ), 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

		wp_clear_auth_cookie();
		wp_set_auth_cookie( $user_id, false );

		wp_safe_redirect( admin_url() );
		exit;
	}

	private function do_switch_back(): void {
		$origin_id = $this->read_cookie();
		if ( null === $origin_id ) {
			wp_die( esc_html__( 'No switch session found.', 'admin-tools' ) );
		}

		$this->clear_cookie();
		wp_clear_auth_cookie();
		wp_set_auth_cookie( $origin_id, false );

		wp_safe_redirect( admin_url( 'users.php' ) );
		exit;
	}

	// -------------------------------------------------------------------------
	// Admin bar node
	// -------------------------------------------------------------------------

	public function admin_bar_node( WP_Admin_Bar $wp_admin_bar ): void {
		$origin_id = $this->read_cookie();
		if ( null === $origin_id ) {
			return;
		}

		$origin = get_userdata( $origin_id );
		if ( ! $origin ) {
			return;
		}

		$wp_admin_bar->add_node( array(
			'id'    => 'at-us-back',
			'title' => '&#8617; Switch back to ' . esc_html( $origin->display_name ),
			'href'  => $this->back_url(),
			'meta'  => array( 'class' => 'at-us-back-node' ),
		) );
	}

	// -------------------------------------------------------------------------
	// Admin notice (shown on all admin pages while switched)
	// -------------------------------------------------------------------------

	public function switched_notice(): void {
		$origin_id = $this->read_cookie();
		if ( null === $origin_id ) {
			return;
		}

		$origin  = get_userdata( $origin_id );
		$current = wp_get_current_user();
		if ( ! $origin ) {
			return;
		}

		printf(
			'<div class="notice notice-warning at-us-notice"><p><strong>%s</strong> %s <strong>%s</strong> &mdash; <a href="%s">%s</a></p></div>',
			esc_html__( 'User Switching:', 'admin-tools' ),
			esc_html__( 'You are currently browsing as', 'admin-tools' ),
			esc_html( $current->display_name ),
			esc_url( $this->back_url() ),
			/* translators: %s: original admin display name */
			esc_html( sprintf( __( 'Switch back to %s', 'admin-tools' ), $origin->display_name ) )
		);
	}

	// -------------------------------------------------------------------------
	// Users list row action
	// -------------------------------------------------------------------------

	public function user_row_action( array $actions, WP_User $user ): array {
		if ( ! in_array( 'administrator', (array) wp_get_current_user()->roles, true ) ) {
			return $actions;
		}

		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			return $actions;
		}

		if ( $this->read_cookie() !== null ) {
			return $actions;
		}

		$actions['at_switch_to'] = sprintf(
			'<a href="%s">&#8644; %s</a>',
			esc_url( $this->to_url( $user->ID ) ),
			esc_html__( 'Switch To', 'admin-tools' )
		);

		return $actions;
	}

	// -------------------------------------------------------------------------
	// Inline styles for the admin bar highlight
	// -------------------------------------------------------------------------

	public function inline_styles(): void {
		if ( $this->read_cookie() === null ) {
			return;
		}
		echo '<style>
			#wpadminbar #wp-admin-bar-at-us-back > .ab-item {
				background: #f59e0b !important;
				color: #fff !important;
				font-weight: 600;
			}
			#wpadminbar #wp-admin-bar-at-us-back > .ab-item:hover {
				background: #d97706 !important;
				color: #fff !important;
			}
		</style>';
	}

	// -------------------------------------------------------------------------
	// URL helpers
	// -------------------------------------------------------------------------

	private function to_url( int $user_id ): string {
		return wp_nonce_url(
			add_query_arg( array( 'at_us_action' => 'switch_to', 'user_id' => $user_id ), admin_url( 'users.php' ) ),
			'at_us_switch_to_' . $user_id
		);
	}

	private function back_url(): string {
		return wp_nonce_url(
			add_query_arg( 'at_us_action', 'switch_back', admin_url() ),
			'at_us_switch_back'
		);
	}

	// -------------------------------------------------------------------------
	// Cookie helpers
	// -------------------------------------------------------------------------

	private function make_cookie( int $user_id ): string {
		$user = get_userdata( $user_id );
		return $user_id . '|' . wp_hash( $user_id . '|' . $user->user_pass . '|' . AUTH_SALT );
	}

	private function read_cookie(): ?int {
		$raw = $_COOKIE[ self::COOKIE ] ?? '';
		if ( ! $raw ) {
			return null;
		}

		$parts = explode( '|', $raw, 2 );
		if ( 2 !== count( $parts ) ) {
			return null;
		}

		$user_id = absint( $parts[0] );
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return null;
		}

		$expected = wp_hash( $user_id . '|' . $user->user_pass . '|' . AUTH_SALT );
		if ( ! hash_equals( $expected, $parts[1] ) ) {
			return null;
		}

		return $user_id;
	}

	private function clear_cookie(): void {
		setcookie( self::COOKIE, '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		unset( $_COOKIE[ self::COOKIE ] );
	}
}
