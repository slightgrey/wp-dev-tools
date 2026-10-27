<?php
defined( 'ABSPATH' ) || exit;

/**
 * Renders an inline pill badge.
 *
 * @param string $text
 * @param string $color  gray | green | yellow | red | blue | purple
 */
function at_dt_badge( string $text, string $color = 'gray' ): void {
	$map = array(
		'gray'   => 'background:#f3f4f6; color:#374151;',
		'green'  => 'background:#dcfce7; color:#166534;',
		'yellow' => 'background:#fef9c3; color:#854d0e;',
		'red'    => 'background:#fee2e2; color:#991b1b;',
		'blue'   => 'background:#dbeafe; color:#1e40af;',
		'purple' => 'background:#f3e8ff; color:#6b21a8;',
	);
	$style = $map[ $color ] ?? $map['gray'];
	echo '<span style="display:inline-block; padding:1px 7px; border-radius:9999px; font-size:11px; font-weight:500; ' . esc_attr( $style ) . '">' . esc_html( $text ) . '</span>';
}
