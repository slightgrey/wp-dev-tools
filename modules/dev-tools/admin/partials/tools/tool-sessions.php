<?php defined( 'ABSPATH' ) || exit;

if ( session_status() === PHP_SESSION_NONE ) {
	session_start();
}

$session_data = $_SESSION ?? array();
$session_id   = session_id();
$rows         = array();

foreach ( $session_data as $key => $value ) {
	if ( is_array( $value ) || is_object( $value ) ) {
		$display = '<code style="font-size:10px;">' . esc_html( wp_json_encode( $value ) ) . '</code>';
	} else {
		$str     = (string) $value;
		$display = '<code style="font-size:11px;">' . esc_html( strlen( $str ) > 150 ? substr( $str, 0, 150 ) . '…' : $str ) . '</code>';
	}
	$rows[] = array( 'cells' => array(
		'<code style="font-size:11px;">' . esc_html( $key ) . '</code>',
		$display,
	) );
}
?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
	<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0;">Session</h2>
	<?php if ( ! empty( $session_data ) ) : ?>
		<button id="at-dt-destroy-session"
			style="padding:5px 14px; background:#fff; border:1px solid #e5e7eb; border-radius:6px; font-size:12px; color:#dc2626; cursor:pointer;">
			Destroy Session
		</button>
	<?php endif; ?>
</div>

<?php if ( $session_id ) : ?>
	<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">
		Session ID: <code style="font-size:11px;"><?php echo esc_html( $session_id ); ?></code>
		&nbsp;&nbsp;
		Save path: <code style="font-size:11px;"><?php echo esc_html( session_save_path() ?: ini_get( 'session.save_path' ) ?: 'default' ); ?></code>
	</p>
<?php endif; ?>

<div id="at-dt-session-content">
	<?php if ( empty( $rows ) ) : ?>
		<?php at_dt_empty( 'Session is empty or no active session.' ); ?>
	<?php else : ?>
		<?php at_dt_table( array( 'Key', 'Value' ), $rows ); ?>
	<?php endif; ?>
</div>
