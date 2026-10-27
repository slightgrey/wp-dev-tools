<?php defined( 'ABSPATH' ) || exit;

$meta_type = sanitize_key( $_GET['meta_type'] ?? 'post' );
if ( ! in_array( $meta_type, array( 'post', 'user', 'term' ), true ) ) {
	$meta_type = 'post';
}
$object_id = absint( $_GET['meta_id'] ?? 0 );

$base_url = add_query_arg( array( 'page' => 'at-dev-tools', 'tool' => 'metadata' ), admin_url( 'admin.php' ) );

// Tab labels.
$tabs = array(
	'post' => 'Post Meta',
	'user' => 'User Meta',
	'term' => 'Term Meta',
);

// Entity picker options.
function at_dt_entity_options( string $type, int $selected ): void {
	if ( 'post' === $type ) {
		$items = get_posts( array( 'posts_per_page' => 200, 'post_status' => 'any', 'fields' => 'ids' ) );
		foreach ( $items as $id ) {
			$title = get_the_title( $id ) ?: '#' . $id;
			echo '<option value="' . esc_attr( $id ) . '" ' . selected( $selected, $id, false ) . '>#' . esc_html( $id ) . ' — ' . esc_html( $title ) . '</option>';
		}
	} elseif ( 'user' === $type ) {
		$users = get_users( array( 'number' => 200, 'fields' => array( 'ID', 'display_name' ) ) );
		foreach ( $users as $u ) {
			echo '<option value="' . esc_attr( $u->ID ) . '" ' . selected( $selected, $u->ID, false ) . '>#' . esc_html( $u->ID ) . ' — ' . esc_html( $u->display_name ) . '</option>';
		}
	} elseif ( 'term' === $type ) {
		$terms = get_terms( array( 'hide_empty' => false, 'number' => 200 ) );
		foreach ( (array) $terms as $t ) {
			echo '<option value="' . esc_attr( $t->term_id ) . '" ' . selected( $selected, $t->term_id, false ) . '>#' . esc_html( $t->term_id ) . ' — ' . esc_html( $t->name ) . ' (' . esc_html( $t->taxonomy ) . ')</option>';
		}
	}
}

$meta_rows  = $object_id ? get_metadata( $meta_type, $object_id ) : array();
$table_rows = array();
foreach ( (array) $meta_rows as $key => $values ) {
	foreach ( $values as $v ) {
		$display = is_serialized( $v )
			? '<code style="font-size:10px;">' . esc_html( substr( $v, 0, 200 ) ) . '</code>'
			: '<code style="font-size:11px;">' . esc_html( strlen( $v ) > 150 ? substr( $v, 0, 150 ) . '…' : $v ) . '</code>';

		$table_rows[] = array(
			'cells'  => array(
				'<code style="font-size:11px;">' . esc_html( $key ) . '</code>',
				$display,
			),
			'action' => 'delete_meta',
			'data'   => array(
				'meta-type'  => $meta_type,
				'object-id'  => $object_id,
				'meta-key'   => $key,
			),
		);
	}
}
?>

<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0 0 14px;">Metadata</h2>

<!-- Tabs -->
<div style="display:flex; border-bottom:1px solid #e5e7eb; margin-bottom:16px; gap:0;">
	<?php foreach ( $tabs as $slug => $label ) :
		$is_active = $slug === $meta_type;
		$href      = add_query_arg( array( 'meta_type' => $slug, 'meta_id' => '' ), $base_url );
	?>
		<a href="<?php echo esc_url( $href ); ?>"
			style="padding:8px 16px; font-size:13px; font-weight:<?php echo $is_active ? '600' : '400'; ?>; color:<?php echo $is_active ? '#111827' : '#6b7280'; ?>; border-bottom:2px solid <?php echo $is_active ? '#111827' : 'transparent'; ?>; text-decoration:none; margin-bottom:-1px;">
			<?php echo esc_html( $label ); ?>
		</a>
	<?php endforeach; ?>
</div>

<!-- Entity picker -->
<form method="get" style="display:flex; gap:8px; align-items:center; margin-bottom:14px;">
	<input type="hidden" name="page" value="at-dev-tools">
	<input type="hidden" name="tool" value="metadata">
	<input type="hidden" name="meta_type" value="<?php echo esc_attr( $meta_type ); ?>">
	<select name="meta_id" style="padding:5px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; max-width:340px;">
		<option value="">— Select <?php echo esc_html( $meta_type ); ?> —</option>
		<?php at_dt_entity_options( $meta_type, $object_id ); ?>
	</select>
	<button type="submit" style="padding:5px 12px; background:#fff; border:1px solid #d1d5db; border-radius:6px; font-size:12px; cursor:pointer;">Load</button>
</form>

<!-- Meta table -->
<?php if ( $object_id ) : ?>
	<p style="font-size:12px; color:#6b7280; margin:0 0 10px;"><?php echo esc_html( count( $table_rows ) . ' meta rows for ' . $meta_type . ' #' . $object_id ); ?></p>
	<?php at_dt_table( array( 'Key', 'Value' ), $table_rows, array( 'searchable' => true, 'actions_col' => true ) ); ?>
<?php else : ?>
	<?php at_dt_empty( 'Select a ' . $meta_type . ' above to view its metadata.' ); ?>
<?php endif; ?>
