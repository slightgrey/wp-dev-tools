<?php defined( 'ABSPATH' ) || exit;

$file_path   = ABSPATH . '.htaccess';
$exists      = file_exists( $file_path );
$is_writable = $exists && is_writable( $file_path );
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
$content     = $exists ? file_get_contents( $file_path ) : '';
$editor_id   = 'at-dt-htaccess-editor';
?>

<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
	<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0;">.htaccess</h2>
	<div style="display:flex; gap:8px; align-items:center;">
		<?php if ( ! $exists ) : ?>
			<?php at_dt_badge( 'Not found', 'red' ); ?>
		<?php elseif ( $is_writable ) : ?>
			<?php at_dt_badge( 'Writable', 'green' ); ?>
		<?php else : ?>
			<?php at_dt_badge( 'Read-only', 'yellow' ); ?>
		<?php endif; ?>
		<code style="font-size:11px; color:#6b7280;"><?php echo esc_html( $file_path ); ?></code>
	</div>
</div>

<?php if ( ! $exists ) : ?>
	<?php at_dt_empty( '.htaccess file not found. This is normal on Nginx servers.' ); ?>
<?php else : ?>
	<textarea
		id="<?php echo esc_attr( $editor_id ); ?>"
		class="at-dt-file-editor"
		<?php echo ! $is_writable ? 'readonly' : ''; ?>
		spellcheck="false"
		autocomplete="off"
		autocorrect="off"
		autocapitalize="off"
	><?php echo esc_textarea( $content ); ?></textarea>

	<?php if ( $is_writable ) : ?>
		<div style="margin-top:10px;">
			<button
				class="at-dt-save-file-btn"
				data-target="<?php echo esc_attr( $editor_id ); ?>"
				data-file="<?php echo esc_attr( wp_normalize_path( $file_path ) ); ?>"
				style="padding:7px 18px; background:#111827; color:#fff; border:none; border-radius:6px; font-size:13px; cursor:pointer; font-weight:500;"
			>Save File</button>
			<span style="margin-left:10px; font-size:12px; color:#dc2626;">⚠ Changes are written directly to disk.</span>
		</div>
	<?php endif; ?>
<?php endif; ?>
