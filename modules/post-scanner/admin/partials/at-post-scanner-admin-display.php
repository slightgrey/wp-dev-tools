<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>

<div class="wrap">
	<div id="wpps-app">
		<p style="padding: 20px; color: #666;">
			<?php esc_html_e( 'Loading Post Scanner\u2026', 'wp-post-scanner' ); ?>
		</p>
	</div>
</div>
