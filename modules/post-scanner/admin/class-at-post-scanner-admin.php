<?php

defined( 'ABSPATH' ) || exit;

class AT_Post_Scanner_Admin {

	const SLUG = 'at-post-scanner';

	private string $hook_suffix = '';

	public function add_admin_menu(): void {
		$this->hook_suffix = add_submenu_page(
			'admin-tools',
			__( 'Post Scanner', 'admin-tools' ),
			__( 'Post Scanner', 'admin-tools' ),
			'manage_options',
			self::SLUG,
			array( $this, 'display_admin_page' )
		);
	}

	public function enqueue_scripts( string $hook_suffix ): void {
		if ( $this->hook_suffix !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script( 'wp-element' );

		wp_register_script( 'tailwindcss-cdn', AT_MODULES_URL . 'post-scanner/admin/js/tailwindcss.js', array(), AT_VERSION, false );
		wp_enqueue_script( 'tailwindcss-cdn' );

		wp_enqueue_script(
			'at-post-scanner-app',
			AT_MODULES_URL . 'post-scanner/admin/js/wp-post-scanner-app.js',
			array( 'wp-element' ),
			AT_VERSION,
			true
		);

		wp_localize_script( 'at-post-scanner-app', 'wppsConfig', array(
			'apiUrl'    => esc_url_raw( rest_url( 'wp-post-scanner/v1' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'pluginUrl' => AT_MODULES_URL . 'post-scanner/',
		) );
	}

	public function display_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require_once AT_MODULES_DIR . 'post-scanner/admin/partials/at-post-scanner-admin-display.php';
	}

	public function register_rest_routes(): void {
		register_rest_route( 'wp-post-scanner/v1', '/posts', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'rest_get_posts' ),
			'permission_callback' => array( $this, 'rest_permission_check' ),
		) );
		register_rest_route( 'wp-post-scanner/v1', '/scan/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'rest_scan_post' ),
			'permission_callback' => array( $this, 'rest_permission_check' ),
			'args'                => array(
				'id' => array(
					'validate_callback' => fn( $param ) => is_numeric( $param ) && (int) $param > 0,
					'sanitize_callback' => 'absint',
				),
			),
		) );
	}

	public function rest_permission_check(): bool {
		return current_user_can( 'manage_options' );
	}

	public function rest_get_posts( WP_REST_Request $request ): WP_REST_Response {
		$post_types = array_values( get_post_types( array( 'public' => true ), 'names' ) );
		$posts      = get_posts( array(
			'post_type'      => $post_types,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );
		$result = array();
		foreach ( $posts as $post ) {
			$result[] = array(
				'id'     => $post->ID,
				'title'  => get_the_title( $post ),
				'type'   => $post->post_type,
				'status' => $post->post_status,
			);
		}
		return rest_ensure_response( $result );
	}

	public function rest_scan_post( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error( 'wpps_not_found', __( 'Post not found.', 'admin-tools' ), array( 'status' => 404 ) );
		}
		$post_data = $post->to_array();
		$raw_meta  = get_post_meta( $post_id );
		$meta      = array();
		foreach ( $raw_meta as $key => $values ) {
			$meta[ $key ] = ( count( $values ) === 1 )
				? maybe_unserialize( $values[0] )
				: array_map( 'maybe_unserialize', $values );
		}
		$taxonomies = get_object_taxonomies( $post->post_type );
		$terms      = array();
		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_post_terms( $post_id, $taxonomy );
			if ( ! is_wp_error( $post_terms ) ) {
				$terms[ $taxonomy ] = array_map( fn( $term ) => array(
					'term_id' => $term->term_id,
					'name'    => $term->name,
					'slug'    => $term->slug,
					'count'   => $term->count,
				), $post_terms );
			}
		}
		$thumbnail_id  = get_post_thumbnail_id( $post_id );
		$thumbnail_url = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : '';
		$thumbnail_alt = $thumbnail_id ? get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) : '';
		$thumbnail     = array(
			'id'  => (int) $thumbnail_id,
			'url' => $thumbnail_url ? esc_url_raw( $thumbnail_url ) : '',
			'alt' => (string) $thumbnail_alt,
		);
		$attachment_posts = get_posts( array(
			'post_parent'    => $post_id,
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		) );
		$attachments = array_map( fn( $att ) => array(
			'id'        => $att->ID,
			'title'     => $att->post_title,
			'url'       => wp_get_attachment_url( $att->ID ),
			'mime_type' => $att->post_mime_type,
		), $attachment_posts );
		$author_id = (int) $post->post_author;
		$author    = array(
			'id'    => $author_id,
			'name'  => get_the_author_meta( 'display_name', $author_id ),
			'email' => get_the_author_meta( 'user_email', $author_id ),
		);
		return rest_ensure_response( array(
			'post'           => $post_data,
			'meta'           => $meta,
			'terms'          => $terms,
			'thumbnail'      => $thumbnail,
			'attachments'    => $attachments,
			'author'         => $author,
			'comments_count' => (int) get_comments_number( $post_id ),
		) );
	}
}
