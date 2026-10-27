<?php defined( 'ABSPATH' ) || exit;

global $wpdb;

$rows = array();

if ( is_multisite() && $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->signups}'" ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	// Multisite: use wp_signups table.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$signups = $wpdb->get_results(
		"SELECT user_login, user_email, registered, active, active_signup
		 FROM {$wpdb->signups}
		 ORDER BY registered DESC
		 LIMIT 200"
	);

	foreach ( (array) $signups as $s ) {
		$status  = $s->active ? 'Active' : 'Pending';
		$color   = $s->active ? 'green' : 'yellow';

		ob_start(); at_dt_badge( $status, $color ); $badge = ob_get_clean();

		$rows[] = array( 'cells' => array(
			esc_html( $s->user_login ),
			esc_html( $s->user_email ),
			esc_html( wp_date( 'Y-m-d H:i', strtotime( $s->registered ) ) ),
			$badge,
			esc_html( $s->active_signup ),
		) );
	}

	at_dt_card( 'Signups (Multisite)', function() use ( $rows ) {
		echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' signups' ) . '</p>';
		at_dt_table( array( 'Login', 'Email', 'Registered', 'Status', 'Type' ), $rows, array( 'searchable' => true ) );
	} );

} else {
	// Single site: show users with user_status != 0 (spam/not yet approved).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$pending = $wpdb->get_results(
		"SELECT ID, user_login, user_email, user_registered, user_status
		 FROM {$wpdb->users}
		 WHERE user_status != 0
		 ORDER BY user_registered DESC
		 LIMIT 200"
	);

	if ( $pending ) {
		foreach ( $pending as $u ) {
			$rows[] = array( 'cells' => array(
				esc_html( $u->user_login ),
				esc_html( $u->user_email ),
				esc_html( wp_date( 'Y-m-d H:i', strtotime( $u->user_registered ) ) ),
				esc_html( $u->user_status ),
			) );
		}

		at_dt_card( 'Non-Active Users', function() use ( $rows ) {
			echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' users with non-zero user_status' ) . '</p>';
			at_dt_table( array( 'Login', 'Email', 'Registered', 'Status Code' ), $rows );
		}, 'user_status != 0 in wp_users (spam, awaiting approval, etc.)' );
	} else {
		at_dt_card( 'Signups', function() {
			at_dt_empty( 'No pending signups. On a single-site install, this shows users with non-zero user_status.' );
		} );
	}
}
