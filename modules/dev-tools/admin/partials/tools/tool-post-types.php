<?php defined( 'ABSPATH' ) || exit;

$post_types = get_post_types( array(), 'objects' );

echo '<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0 0 14px;">Post Types <span style="font-weight:400; font-size:12px; color:#6b7280;">(' . count( $post_types ) . ')</span></h2>';

foreach ( $post_types as $pt ) :
	$taxonomies = get_object_taxonomies( $pt->name );
	$supports   = get_all_post_type_supports( $pt->name );
	?>
	<div style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:12px; overflow:hidden;">
		<div style="padding:12px 16px; background:#f9fafb; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
			<strong style="font-size:13px; color:#111827;"><?php echo esc_html( $pt->label ); ?></strong>
			<code style="font-size:11px; background:#e5e7eb; padding:1px 6px; border-radius:4px; color:#374151;"><?php echo esc_html( $pt->name ); ?></code>
			<?php if ( $pt->public ) at_dt_badge( 'public', 'green' ); ?>
			<?php if ( $pt->hierarchical ) at_dt_badge( 'hierarchical', 'blue' ); ?>
			<?php if ( $pt->_builtin ) at_dt_badge( 'built-in', 'gray' ); ?>
		</div>
		<div style="padding:12px 16px; display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:12px; color:#374151;">
			<div><strong style="color:#6b7280;">Singular:</strong> <?php echo esc_html( $pt->labels->singular_name ?? '—' ); ?></div>
			<div><strong style="color:#6b7280;">Plural:</strong> <?php echo esc_html( $pt->labels->name ?? '—' ); ?></div>
			<div><strong style="color:#6b7280;">Capability Type:</strong> <?php echo esc_html( is_array( $pt->capability_type ) ? implode( ', ', $pt->capability_type ) : $pt->capability_type ); ?></div>
			<div><strong style="color:#6b7280;">Has Archive:</strong> <?php echo $pt->has_archive ? 'Yes' : 'No'; ?></div>
			<div><strong style="color:#6b7280;">Taxonomies:</strong>
				<?php if ( $taxonomies ) {
					foreach ( $taxonomies as $tax ) {
						echo ' ';
						at_dt_badge( $tax, 'purple' );
					}
				} else {
					echo '<span style="color:#9ca3af;">none</span>';
				} ?>
			</div>
			<div><strong style="color:#6b7280;">Supports:</strong>
				<?php if ( $supports ) {
					foreach ( array_keys( $supports ) as $s ) {
						echo ' ';
						at_dt_badge( $s, 'blue' );
					}
				} else {
					echo '<span style="color:#9ca3af;">none</span>';
				} ?>
			</div>
		</div>
	</div>
<?php endforeach;
