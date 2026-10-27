<?php

defined( 'ABSPATH' ) || exit;

class AT_Plugin_Scanner_Scanner {

	private static array $premium_uri_patterns = array(
		'themeforest.net',
		'codecanyon.net',
		'woocommerce.com',
		'yoast.com',
		'gravityforms.com',
		'wpmudev.org',
		'advancedcustomfields.com',
		'wpengine.com',
		'wpbakery.com',
		'layerslider.com',
		'wpml.org',
		'beplus.me',
	);

	public function scan(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins  = get_plugins();
		$update_data  = get_site_transient( 'update_plugins' );

		$wp_org_plugins = array_merge(
			isset( $update_data->no_update ) ? (array) $update_data->no_update : array(),
			isset( $update_data->response )  ? (array) $update_data->response  : array()
		);

		$plugins_needing_update = isset( $update_data->response ) ? array_keys( $update_data->response ) : array();

		$results = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_uri   = isset( $plugin_data['PluginURI'] )   ? trim( $plugin_data['PluginURI'] )   : '';
			$author_uri   = isset( $plugin_data['AuthorURI'] )   ? trim( $plugin_data['AuthorURI'] )   : '';
			$requires_php = isset( $plugin_data['RequiresPHP'] ) ? $plugin_data['RequiresPHP']         : '';

			$results[] = array(
				'name'         => $plugin_data['Name'],
				'version'      => $plugin_data['Version'],
				'plugin_uri'   => $plugin_uri,
				'author'       => wp_strip_all_tags( $plugin_data['Author'] ),
				'requires_php' => $requires_php,
				'is_active'    => is_plugin_active( $plugin_file ),
				'needs_update' => in_array( $plugin_file, $plugins_needing_update, true ),
				'type'         => $this->detect_type( $plugin_file, $plugin_data['Name'], $plugin_uri, $author_uri, $wp_org_plugins ),
				'plugin_file'  => $plugin_file,
			);
		}

		usort( $results, function( $a, $b ) {
			if ( $a['is_active'] !== $b['is_active'] ) {
				return $a['is_active'] ? -1 : 1;
			}
			return strcmp( $a['name'], $b['name'] );
		} );

		return $results;
	}

	private function detect_type( string $plugin_file, string $name, string $plugin_uri, string $author_uri, array $wp_org_plugins ): string {
		if ( array_key_exists( $plugin_file, $wp_org_plugins ) ) {
			return 'free';
		}
		if ( preg_match( '/\b(pro|premium|full)\b/i', $name ) ) {
			return 'premium';
		}
		$combined = strtolower( $plugin_uri . ' ' . $author_uri );
		foreach ( self::$premium_uri_patterns as $pattern ) {
			if ( false !== strpos( $combined, $pattern ) ) {
				return 'premium';
			}
		}
		if ( false !== strpos( $combined, 'github.com' ) ) {
			return 'custom';
		}
		if ( '' === $plugin_uri && '' === $author_uri ) {
			return 'custom';
		}
		return 'free_or_premium';
	}
}
