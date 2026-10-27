<?php defined( 'ABSPATH' ) || exit;
?>

<div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
	<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0;">PHP Testing</h2>
	<?php at_dt_badge( 'Admin Only', 'red' ); ?>
</div>

<p style="font-size:12px; color:#6b7280; margin:0 0 14px;">
	Execute PHP code directly on this server. Output is captured via <code>ob_start()</code>. Exceptions are caught and displayed.
	<strong style="color:#dc2626;">Use with caution — code runs with full WordPress context.</strong>
</p>

<div style="margin-bottom:10px;">
	<textarea
		id="at-dt-php-code"
		class="at-dt-file-editor"
		style="min-height:200px; height:200px;"
		placeholder="<?php echo esc_attr( "// Example:\necho 'Hello ' . get_bloginfo('name');\nreturn get_option('siteurl');" ); ?>"
		spellcheck="false"
		autocomplete="off"
	></textarea>
</div>

<div style="display:flex; gap:8px; margin-bottom:14px;">
	<button
		id="at-dt-run-php"
		style="padding:7px 20px; background:#111827; color:#fff; border:none; border-radius:6px; font-size:13px; cursor:pointer; font-weight:500;"
	>&#9658; Run</button>
	<button
		onclick="document.getElementById('at-dt-php-code').value=''; document.getElementById('at-dt-php-output').textContent='';"
		style="padding:7px 14px; background:#fff; border:1px solid #d1d5db; border-radius:6px; font-size:13px; cursor:pointer; color:#374151;"
	>Clear</button>
</div>

<div style="margin-bottom:6px; font-size:12px; font-weight:600; color:#374151;">Output</div>
<pre id="at-dt-php-output">// Output will appear here after running code.</pre>

<div style="margin-top:16px; padding:12px 14px; background:#fef9c3; border:1px solid #fde047; border-radius:6px; font-size:12px; color:#713f12;">
	<strong>Snippets to try:</strong><br>
	<code>echo PHP_VERSION;</code> &nbsp;|&nbsp;
	<code>var_dump(get_option('blogname'));</code> &nbsp;|&nbsp;
	<code>print_r(wp_load_alloptions());</code>
</div>
