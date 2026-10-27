<?php defined( 'ABSPATH' ) || exit;

$crons = _get_cron_array();
$rows  = array();
$now   = time();

if ( is_array( $crons ) ) {
	foreach ( $crons as $timestamp => $hooks ) {
		foreach ( $hooks as $hook => $events ) {
			foreach ( $events as $event ) {
				$next_run   = $timestamp - $now;
				$next_label = $next_run < 0
					? '<span style="color:#dc2626;">Overdue by ' . esc_html( human_time_diff( $timestamp, $now ) ) . '</span>'
					: esc_html( 'In ' . human_time_diff( $timestamp, $now ) );

				$recurrence = $event['schedule'] ?? '';
				if ( $recurrence ) {
					$schedules  = wp_get_schedules();
					$rec_label  = $schedules[ $recurrence ]['display'] ?? $recurrence;
				} else {
					$rec_label  = 'Single event';
				}

				$args_str = empty( $event['args'] )
					? '<span style="color:#9ca3af;">none</span>'
					: '<code style="font-size:10px;">' . esc_html( wp_json_encode( $event['args'] ) ) . '</code>';

				$rows[] = array( 'cells' => array(
					'<code style="font-size:11px;">' . esc_html( $hook ) . '</code>',
					$next_label . '<br><span style="font-size:10px; color:#9ca3af;">' . esc_html( wp_date( 'Y-m-d H:i:s', $timestamp ) ) . '</span>',
					esc_html( $rec_label ),
					$args_str,
				) );
			}
		}
	}
}

at_dt_card( 'Cron Jobs', function() use ( $rows ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $rows ) . ' scheduled events' ) . '</p>';
	at_dt_table( array( 'Hook', 'Next Run', 'Recurrence', 'Args' ), $rows, array( 'searchable' => true ) );
}, 'All events currently queued in the WordPress cron array.' );
