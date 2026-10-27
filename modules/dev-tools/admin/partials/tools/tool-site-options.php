<?php defined( 'ABSPATH' ) || exit;

global $wpdb;

$per_page = 100;
$offset   = max( 0, absint( $_GET['opt_offset'] ?? 0 ) );
$search   = sanitize_text_field( wp_unslash( $_GET['opt_search'] ?? '' ) );

$where      = '';
$where_args = array();
if ( $search ) {
	$where      = ' AND option_name LIKE %s';
	$where_args = array( '%' . $wpdb->esc_like( $search ) . '%' );
}

// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
$total = (int) $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name != '' $where",
	$where_args
) );

// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
$options = $wpdb->get_results( $wpdb->prepare(
	"SELECT option_name, LENGTH(option_value) AS size_bytes, autoload
	 FROM {$wpdb->options}
	 WHERE option_name != '' $where
	 ORDER BY option_name
	 LIMIT %d OFFSET %d",
	array_merge( $where_args, array( $per_page, $offset ) )
) );

$rows = array();
foreach ( (array) $options as $opt ) {
	$autoload_badge = $opt->autoload === 'yes'
		? '<span style="color:#854d0e; font-size:11px;">yes</span>'
		: '<span style="color:#9ca3af; font-size:11px;">no</span>';

	$rows[] = array(
		'cells'  => array(
			'<code style="font-size:11px;">' . esc_html( $opt->option_name ) . '</code>',
			esc_html( number_format( $opt->size_bytes ) . ' bytes' ),
			$autoload_badge,
		),
		'action' => 'delete_option',
		'data'   => array( 'name' => $opt->option_name ),
	);
}

$base_url     = add_query_arg( array( 'page' => 'at-dev-tools', 'tool' => 'site-options' ), admin_url( 'admin.php' ) );
$prev_offset  = max( 0, $offset - $per_page );
$next_offset  = $offset + $per_page;
?>

<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
	<h2 style="font-size:14px; font-weight:700; color:#111827; margin:0;">
		Site Options <span style="font-weight:400; font-size:12px; color:#6b7280;">(<?php echo esc_html( number_format( $total ) ); ?> total)</span>
	</h2>
	<form method="get" style="display:flex; gap:6px;">
		<input type="hidden" name="page" value="at-dev-tools">
		<input type="hidden" name="tool" value="site-options">
		<input type="text" name="opt_search" value="<?php echo esc_attr( $search ); ?>"
			placeholder="Search option name…"
			style="padding:5px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; width:200px;">
		<button type="submit" style="padding:5px 12px; background:#fff; border:1px solid #d1d5db; border-radius:6px; font-size:12px; cursor:pointer;">Search</button>
	</form>
</div>

<?php
at_dt_table(
	array( 'Option Name', 'Size', 'Autoload' ),
	$rows,
	array( 'actions_col' => true )
);

// Pagination.
if ( $total > $per_page ) : ?>
	<div style="margin-top:12px; display:flex; gap:8px; font-size:12px;">
		<?php if ( $offset > 0 ) : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'opt_offset' => $prev_offset, 'opt_search' => $search ), $base_url ) ); ?>"
				style="padding:4px 12px; border:1px solid #d1d5db; border-radius:4px; color:#374151; text-decoration:none;">&larr; Prev</a>
		<?php endif; ?>
		<span style="padding:4px 8px; color:#6b7280;">
			<?php echo esc_html( ( $offset + 1 ) . '–' . min( $offset + $per_page, $total ) . ' of ' . number_format( $total ) ); ?>
		</span>
		<?php if ( $next_offset < $total ) : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'opt_offset' => $next_offset, 'opt_search' => $search ), $base_url ) ); ?>"
				style="padding:4px 12px; border:1px solid #d1d5db; border-radius:4px; color:#374151; text-decoration:none;">Next &rarr;</a>
		<?php endif; ?>
	</div>
<?php endif;
