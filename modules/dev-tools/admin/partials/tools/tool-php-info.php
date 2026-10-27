<?php defined( 'ABSPATH' ) || exit;

ob_start();
phpinfo();
$phpinfo_raw = ob_get_clean();

// Extract only the <body> content and strip the outer HTML wrapper.
preg_match( '/<body[^>]*>(.*?)<\/body>/si', $phpinfo_raw, $matches );
$phpinfo_body = $matches[1] ?? $phpinfo_raw;

// Remove the <style> block (we have our own scoped styles).
$phpinfo_body = preg_replace( '/<style[^>]*>.*?<\/style>/si', '', $phpinfo_body );
?>

<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0 0 14px;">PHP Info</h2>

<div id="at-dt-phpinfo-wrap" style="border:1px solid #e5e7eb; border-radius:6px; padding:16px; overflow-x:auto;">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $phpinfo_body;
	?>
</div>
