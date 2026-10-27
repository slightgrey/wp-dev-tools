<?php defined( 'ABSPATH' ) || exit;

global $wpdb;

// Find all at_dt_online_* transients that haven't expired yet.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$rows_db = $wpdb->get_results(
	"SELECT option_name, option_value AS last_seen
	 FROM {$wpdb->options}
	 WHERE option_name LIKE '_transient_at_dt_online_%'
	 ORDER BY option_value DESC"
);

$online_users = array();
$now          = time();

foreach ( (array) $rows_db as $row ) {
	$user_id   = (int) str_replace( '_transient_at_dt_online_', '', $row->option_name );
	$last_seen = (int) $row->last_seen;

	if ( $user_id && ( $now - $last_seen ) <= 5 * MINUTE_IN_SECONDS ) {
		$user = get_user_by( 'ID', $user_id );
		if ( $user ) {
			$online_users[] = array(
				'user'      => $user,
				'last_seen' => $last_seen,
			);
		}
	}
}

$rows = array();
foreach ( $online_users as $entry ) {
	$u         = $entry['user'];
	$ago       = human_time_diff( $entry['last_seen'], $now );
	$roles_str = implode( ', ', $u->roles );
	$avatar    = get_avatar( $u->ID, 24, '', '', array( 'class' => '' ) );

	$rows[] = array( 'cells' => array(
		$avatar . ' <a href="' . esc_url( get_edit_user_link( $u->ID ) ) . '" style="color:#1e40af; margin-left:6px;">' . esc_html( $u->display_name ) . '</a>',
		esc_html( $u->user_login ),
		esc_html( $roles_str ),
		esc_html( $ago . ' ago' ),
	) );
}
?>

<div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
	<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0;">Online Users</h2>
	<?php if ( ! empty( $online_users ) ) : ?>
		<?php at_dt_badge( count( $online_users ) . ' online', 'green' ); ?>
	<?php else : ?>
		<?php at_dt_badge( 'None online', 'gray' ); ?>
	<?php endif; ?>
</div>

<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">
	Tracks users active in the last 5 minutes via admin page loads. Data refreshes on every admin page visit.
</p>

<?php if ( empty( $rows ) ) : ?>
	<?php at_dt_empty( 'No users active in the last 5 minutes.' ); ?>
<?php else : ?>
	<?php at_dt_table( array( 'User', 'Login', 'Role', 'Last Seen' ), $rows ); ?>
<?php endif; ?>
