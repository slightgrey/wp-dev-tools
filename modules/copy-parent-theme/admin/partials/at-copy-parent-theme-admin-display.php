<?php

defined( 'ABSPATH' ) || exit;

if ( 'no_child' === $notice_type ) :
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'Copy Elementor Parent Theme to Child: No child theme found.', 'admin-tools' ); ?></strong><br>
			<?php esc_html_e( 'A Hello Elementor child theme is required before running the migration.', 'admin-tools' ); ?>
		</p>
		<p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<input type="hidden" name="action" value="at_install_child_theme">
				<?php wp_nonce_field( 'at_install_child_theme' ); ?>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Install Child Theme', 'admin-tools' ); ?>
				</button>
			</form>
			&nbsp;
			<a href="https://github.com/elementor/hello-theme-child" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'View on GitHub', 'admin-tools' ); ?>
			</a>
		</p>
	</div>

<?php elseif ( 'install_success' === $notice_type ) : ?>
	<div class="notice notice-success">
		<p>
			<strong><?php esc_html_e( '✅ Hello Elementor child theme installed successfully.', 'admin-tools' ); ?></strong><br>
			<?php esc_html_e( 'You can now migrate your parent theme customizations.', 'admin-tools' ); ?>
		</p>
		<p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<input type="hidden" name="action" value="at_run_theme_migration">
				<?php wp_nonce_field( 'at_run_theme_migration' ); ?>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Run Migration Now', 'admin-tools' ); ?>
				</button>
			</form>
		</p>
	</div>

<?php elseif ( 'install_error' === $notice_type ) : ?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( '❌ Child theme installation failed.', 'admin-tools' ); ?></strong><br>
			<?php echo esc_html( $install_message ); ?>
		</p>
		<p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<input type="hidden" name="action" value="at_install_child_theme">
				<?php wp_nonce_field( 'at_install_child_theme' ); ?>
				<button type="submit" class="button">
					<?php esc_html_e( 'Try Again', 'admin-tools' ); ?>
				</button>
			</form>
			&nbsp;
			<a href="https://github.com/elementor/hello-theme-child" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Install Manually from GitHub', 'admin-tools' ); ?>
			</a>
		</p>
	</div>

<?php elseif ( 'run_link' === $notice_type ) : ?>
	<div class="notice notice-warning">
		<p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<input type="hidden" name="action" value="at_run_theme_migration">
				<?php wp_nonce_field( 'at_run_theme_migration' ); ?>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Run Child Theme Migration', 'admin-tools' ); ?>
				</button>
			</form>
		</p>
	</div>

<?php elseif ( 'results' === $notice_type ) : ?>
	<div class="notice notice-success">
		<p>
			<strong><?php esc_html_e( 'Migration Results:', 'admin-tools' ); ?></strong>
			<br><br>
			<?php echo implode( '<br><br>', $results ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
	</div>

<?php endif;
