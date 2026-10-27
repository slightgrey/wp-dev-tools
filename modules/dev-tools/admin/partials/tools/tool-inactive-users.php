<?php defined( 'ABSPATH' ) || exit;

$threshold_days = absint( $_GET['inactive_days'] ?? 90 );
if ( ! in_array( $threshold_days, array( 30, 60, 90, 180, 365 ), true ) ) {
	$threshold_days = 90;
}

$cutoff     = time() - ( $threshold_days * DAY_IN_SECONDS );
$all_users  = get_users( array( 'number' => -1, 'fields' => array( 'ID', 'user_login', 'user_email', 'user_registered', 'display_name' ) ) );
$inactive   = array();

foreach ( $all_users as $user ) {
	$sessions   = get_user_meta( $user->ID, 'session_tokens', true );
	$last_login = 0;

	if ( is_array( $sessions ) && ! empty( $sessions ) ) {
		foreach ( $sessions as $session ) {
			if ( isset( $session['login'] ) && $session['login'] > $last_login ) {
				$last_login = $session['login'];
			}
		}
	}

	// Include user if: no sessions (never logged in) or last login is before cutoff.
	if ( 0 === $last_login || $last_login < $cutoff ) {
		$inactive[] = array(
			'user'       => $user,
			'last_login' => $last_login,
		);
	}
}

$base_url = add_query_arg( array( 'page' => 'at-dev-tools', 'tool' => 'inactive-users' ), admin_url( 'admin.php' ) );
?>

<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
	<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0;">
		Inactive Users
		<span style="font-weight:400; font-size:12px; color:#6b7280;">(<?php echo esc_html( count( $inactive ) ); ?> found)</span>
	</h2>
	<div style="display:flex; gap:6px; font-size:12px;">
		Threshold:
		<?php foreach ( array( 30, 60, 90, 180, 365 ) as $days ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'inactive_days', $days, $base_url ) ); ?>"
				style="padding:3px 8px; border-radius:4px; text-decoration:none; <?php echo $days === $threshold_days ? 'background:#111827; color:#fff;' : 'background:#f3f4f6; color:#374151;'; ?>">
				<?php echo esc_html( $days . 'd' ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</div>

<?php if ( empty( $inactive ) ) : ?>
	<?php at_dt_empty( 'No inactive users found for the selected threshold.' ); ?>
<?php else : ?>
	<?php
	$rows = array();
	foreach ( $inactive as $entry ) {
		$u           = $entry['user'];
		$last_login  = $entry['last_login'];
		$login_label = $last_login
			? esc_html( wp_date( 'Y-m-d', $last_login ) . ' (' . human_time_diff( $last_login ) . ' ago)' )
			: '<span style="color:#9ca3af;">Never logged in</span>';

		$user_url = get_edit_user_link( $u->ID );
		$rows[] = array( 'cells' => array(
			'<a href="' . esc_url( $user_url ) . '" style="color:#1e40af;">' . esc_html( $u->display_name ) . '</a>',
			esc_html( $u->user_login ),
			esc_html( $u->user_email ),
			esc_html( wp_date( 'Y-m-d', strtotime( $u->user_registered ) ) ),
			$login_label,
		) );
	}
	at_dt_table( array( 'Name', 'Login', 'Email', 'Registered', 'Last Login' ), $rows, array( 'searchable' => true ) );
	?>
<?php endif; ?>
