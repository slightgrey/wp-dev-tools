<?php defined( 'ABSPATH' ) || exit;

$auto_drafts = get_posts( array(
	'post_status'    => 'auto-draft',
	'posts_per_page' => -1,
	'orderby'        => 'modified',
	'order'          => 'DESC',
) );

$rows = array();
foreach ( $auto_drafts as $post ) {
	$title = trim( $post->post_title ) ?: '<em style="color:#9ca3af;">Untitled</em>';
	$rows[] = array(
		'cells'  => array(
			is_string( $title ) ? esc_html( $title ) : $title,
			esc_html( $post->post_type ),
			esc_html( wp_date( 'Y-m-d H:i', strtotime( $post->post_modified ) ) ),
		),
		'action' => 'delete_post',
		'data'   => array( 'post-id' => $post->ID ),
		'id'     => 'at-dt-draft-' . $post->ID,
	);
}

at_dt_card( 'Auto Drafts', function() use ( $rows, $auto_drafts ) {
	echo '<p style="font-size:12px; color:#6b7280; margin:0 0 12px;">' . esc_html( count( $auto_drafts ) . ' auto-draft posts' ) . ' — Deleted immediately (not sent to Trash).</p>';
	at_dt_table(
		array( 'Title', 'Post Type', 'Last Modified' ),
		$rows,
		array( 'actions_col' => true )
	);
}, 'WordPress auto-saves create auto-draft entries that accumulate over time.' );
