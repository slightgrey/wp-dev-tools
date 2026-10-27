<?php defined( 'ABSPATH' ) || exit;

$taxonomies = get_taxonomies( array(), 'objects' );

echo '<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0 0 14px;">Taxonomies <span style="font-weight:400; font-size:12px; color:#6b7280;">(' . count( $taxonomies ) . ')</span></h2>';

foreach ( $taxonomies as $tax ) :
	$object_types = (array) $tax->object_type;
	?>
	<div style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:12px; overflow:hidden;">
		<div style="padding:12px 16px; background:#f9fafb; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
			<strong style="font-size:13px; color:#111827;"><?php echo esc_html( $tax->label ); ?></strong>
			<code style="font-size:11px; background:#e5e7eb; padding:1px 6px; border-radius:4px; color:#374151;"><?php echo esc_html( $tax->name ); ?></code>
			<?php if ( $tax->public ) at_dt_badge( 'public', 'green' ); ?>
			<?php if ( $tax->hierarchical ) at_dt_badge( 'hierarchical', 'blue' ); ?>
			<?php if ( $tax->_builtin ) at_dt_badge( 'built-in', 'gray' ); ?>
			<?php if ( $tax->show_ui ) at_dt_badge( 'show_ui', 'yellow' ); ?>
		</div>
		<div style="padding:12px 16px; display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:12px; color:#374151;">
			<div><strong style="color:#6b7280;">Singular:</strong> <?php echo esc_html( $tax->labels->singular_name ?? '—' ); ?></div>
			<div><strong style="color:#6b7280;">Plural:</strong> <?php echo esc_html( $tax->labels->name ?? '—' ); ?></div>
			<div><strong style="color:#6b7280;">Post Types:</strong>
				<?php if ( $object_types ) {
					foreach ( $object_types as $ot ) {
						echo ' ';
						at_dt_badge( $ot, 'purple' );
					}
				} else {
					echo '<span style="color:#9ca3af;">none</span>';
				} ?>
			</div>
			<div><strong style="color:#6b7280;">Rewrite Slug:</strong>
				<?php
				$slug = $tax->rewrite['slug'] ?? '';
				echo $slug ? esc_html( $slug ) : '<span style="color:#9ca3af;">disabled</span>';
				?>
			</div>
			<div><strong style="color:#6b7280;">Query Var:</strong> <?php echo $tax->query_var ? esc_html( $tax->query_var ) : '<span style="color:#9ca3af;">false</span>'; ?></div>
			<div><strong style="color:#6b7280;">REST Base:</strong> <?php echo $tax->rest_base ? esc_html( $tax->rest_base ) : '<span style="color:#9ca3af;">—</span>'; ?></div>
		</div>
	</div>
<?php endforeach;
