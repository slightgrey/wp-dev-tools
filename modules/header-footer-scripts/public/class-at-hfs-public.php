<?php

defined( 'ABSPATH' ) || exit;

class AT_HFS_Public {

	private bool $header_scripts_injected = false;

	public function inject_header_scripts(): void {
		if ( $this->header_scripts_injected ) {
			return;
		}
		$this->header_scripts_injected = true;

		$global = get_option( 'hfs_global_header_scripts', '' );
		if ( ! empty( trim( $global ) ) ) {
			echo "\n" . $global . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( is_singular() ) {
			$enabled_post_types = (array) get_option( 'hfs_enabled_post_types', array() );
			if ( in_array( get_post_type(), $enabled_post_types, true ) ) {
				$post_scripts = (string) get_post_meta( get_the_ID(), '_hfs_header_scripts', true );
				if ( ! empty( trim( $post_scripts ) ) ) {
					echo "\n" . $post_scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}

	public function inject_footer_scripts(): void {
		$global = get_option( 'hfs_global_footer_scripts', '' );
		if ( ! empty( trim( $global ) ) ) {
			echo "\n" . $global . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( is_singular() ) {
			$enabled_post_types = (array) get_option( 'hfs_enabled_post_types', array() );
			if ( in_array( get_post_type(), $enabled_post_types, true ) ) {
				$post_scripts = (string) get_post_meta( get_the_ID(), '_hfs_footer_scripts', true );
				if ( ! empty( trim( $post_scripts ) ) ) {
					echo "\n" . $post_scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}
}
