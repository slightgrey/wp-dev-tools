<?php
defined( 'ABSPATH' ) || exit;

function at_dt_card( string $title, callable $content, string $description = '' ): void {
	?>
	<div style="border:1px solid #e5e7eb; border-radius:8px; background:#fff; margin-bottom:16px; overflow:hidden;">
		<div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
			<h3 style="margin:0; font-size:13px; font-weight:600; color:#111827;"><?php echo esc_html( $title ); ?></h3>
			<?php if ( $description ) : ?>
				<p style="margin:4px 0 0; font-size:12px; color:#6b7280;"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
		<div style="padding:16px 20px;">
			<?php $content(); ?>
		</div>
	</div>
	<?php
}
