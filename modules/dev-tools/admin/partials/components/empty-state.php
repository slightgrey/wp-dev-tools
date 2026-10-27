<?php
defined( 'ABSPATH' ) || exit;

/**
 * Renders a centered empty-state placeholder.
 *
 * @param string $message
 * @param string $icon  Unicode emoji shown above the message.
 */
function at_dt_empty( string $message, string $icon = "\xF0\x9F\x94\x8D" ): void {
	?>
	<div style="padding:40px 20px; text-align:center; color:#9ca3af;">
		<div style="font-size:28px; margin-bottom:8px; line-height:1;"><?php echo esc_html( $icon ); ?></div>
		<p style="margin:0; font-size:13px;"><?php echo esc_html( $message ); ?></p>
	</div>
	<?php
}
