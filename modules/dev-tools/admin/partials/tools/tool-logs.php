<?php defined( 'ABSPATH' ) || exit;

// Resolve log path — reuse AJAX class logic inline.
if ( defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) && WP_DEBUG_LOG ) {
	$log_path = WP_DEBUG_LOG;
} else {
	$log_path = WP_CONTENT_DIR . '/debug.log';
}

$log_exists = file_exists( $log_path );
$log_size   = $log_exists ? filesize( $log_path ) : 0;

// Read last 300 lines.
$lines = array();
if ( $log_exists && $log_size > 0 ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_file
	$all_lines = file( $log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	if ( $all_lines ) {
		$lines = array_slice( $all_lines, -300 );
		$lines = array_reverse( $lines );
	}
}
?>

<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
	<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0;">Debug Log</h2>
	<div style="display:flex; gap:8px; align-items:center;">
		<?php if ( $log_exists && $log_size > 0 ) : ?>
			<?php at_dt_badge( size_format( $log_size ), 'gray' ); ?>
			<?php at_dt_badge( count( $lines ) . ' lines shown', 'blue' ); ?>
			<button id="at-dt-download-log" style="padding:4px 12px; background:#fff; border:1px solid #d1d5db; border-radius:5px; font-size:12px; cursor:pointer;">Download</button>
			<button id="at-dt-clear-log" style="padding:4px 12px; background:#fff; border:1px solid #fca5a5; border-radius:5px; font-size:12px; color:#dc2626; cursor:pointer;">Clear</button>
		<?php else : ?>
			<?php at_dt_badge( 'Empty', 'yellow' ); ?>
		<?php endif; ?>
	</div>
</div>

<p style="font-size:12px; color:#6b7280; margin:0 0 10px;">
	Path: <code style="font-size:11px;"><?php echo esc_html( $log_path ); ?></code>
	&nbsp;|&nbsp;
	WP_DEBUG: <?php echo ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '<span style="color:#166534;">enabled</span>' : '<span style="color:#9ca3af;">disabled</span>'; ?>
	&nbsp;|&nbsp;
	WP_DEBUG_LOG: <?php echo ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ? '<span style="color:#166534;">enabled</span>' : '<span style="color:#9ca3af;">disabled</span>'; ?>
</p>

<?php if ( ! $log_exists ) : ?>
	<?php at_dt_empty( 'No debug.log file found. Enable WP_DEBUG_LOG in wp-config.php to start logging.' ); ?>
<?php elseif ( empty( $lines ) ) : ?>
	<?php at_dt_empty( 'Log file is empty.' ); ?>
<?php else : ?>
	<pre id="at-dt-log-content"><?php echo esc_html( implode( "\n", $lines ) ); ?></pre>
<?php endif; ?>
