<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wrap at-wrap">

	<h1><?php esc_html_e( 'WP Dev Tools', 'admin-tools' ); ?></h1>
	<p class="at-subtitle"><?php esc_html_e( 'Enable or disable your developer tools individually. Hit Save to apply changes.', 'admin-tools' ); ?></p>

	<?php if ( isset( $_GET['at_saved'] ) && '1' === sanitize_key( $_GET['at_saved'] ) ) : ?>
		<div class="at-notice at-notice--success">
			<?php esc_html_e( 'Settings saved.', 'admin-tools' ); ?>
		</div>
	<?php endif; ?>

	<?php
	$enabled_modules = (array) get_option( 'admin_tools_enabled_modules', array() );
	$is_on           = fn( string $id ): bool => in_array( $id, $enabled_modules, true );
	$is_admin        = in_array( 'administrator', (array) wp_get_current_user()->roles, true );

	$admin_tool_modules = array(
		array(
			'id'   => 'debug-monitor',
			'name' => 'WP Debug Monitor',
			'desc' => 'View WP_DEBUG status and browse the debug.log file from the WordPress admin.',
		),
		array(
			'id'   => 'send-mail',
			'name' => 'Send Mail',
			'desc' => 'Compose and send test emails via wp_mail() in plain text or HTML format.',
		),
		array(
			'id'   => 'plugin-scanner',
			'name' => 'Plugin Scanner',
			'desc' => 'Scan, categorize, filter, and export all installed WordPress plugins.',
		),
		array(
			'id'   => 'post-scanner',
			'name' => 'Post Scanner',
			'desc' => 'Inspect any post — meta, terms, thumbnail, attachments, and more.',
		),
		array(
			'id'   => 'user-switching',
			'name' => 'User Switching',
			'desc' => 'Switch to any non-admin user account instantly and switch back with one click.',
		),
		array(
			'id'   => 'dev-tools',
			'name' => 'Debug Tools',
			'desc' => '24 developer tools — transients, cron, globals, DB tables, logs, file editors, PHP sandbox &amp; more.',
		),
	);

	$site_tool_modules = array(
		array(
			'id'   => 'header-footer-scripts',
			'name' => 'Header Footer Scripts',
			'desc' => 'Inject custom scripts into the &lt;head&gt; or footer, globally or per page/post.',
		),
		array(
			'id'   => 'copy-parent-theme',
			'name' => 'Copy Elementor Parent to Child Theme',
			'desc' => 'Migrate theme mods, menu assignments, and modified parent theme files to a child theme.',
		),
	);

	$sections = array(
		array( 'label' => 'Admin Tools', 'modules' => $admin_tool_modules, 'locked' => ! $is_admin ),
		array( 'label' => 'Site Tools',  'modules' => $site_tool_modules,  'locked' => false ),
	);
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="admin_tools_save_settings">
		<?php wp_nonce_field( 'admin_tools_save_settings' ); ?>

		<?php foreach ( $sections as $section ) :
			$locked = ! empty( $section['locked'] );
		?>
			<div class="at-section">
				<h2 class="at-section__heading">
					<?php echo esc_html( $section['label'] ); ?>
					<?php if ( $locked ) : ?>
						<span class="at-section__lock" title="<?php esc_attr_e( 'Administrator access required', 'admin-tools' ); ?>">&#128274;</span>
					<?php endif; ?>
				</h2>
				<div class="at-modules-grid">
					<?php foreach ( $section['modules'] as $module ) : ?>
						<div class="at-module-card <?php echo $is_on( $module['id'] ) ? 'is-enabled' : ''; ?> <?php echo $locked ? 'is-locked' : ''; ?>">
							<div class="at-module-info">
								<h3 class="at-module-name"><?php echo esc_html( $module['name'] ); ?></h3>
								<p class="at-module-desc"><?php echo wp_kses( $module['desc'], array( 'code' => array() ) ); ?></p>
							</div>
							<label class="at-toggle" aria-label="<?php echo esc_attr( sprintf( __( 'Enable %s', 'admin-tools' ), $module['name'] ) ); ?>">
								<input
									type="checkbox"
									name="at_modules[]"
									value="<?php echo esc_attr( $module['id'] ); ?>"
									<?php checked( $is_on( $module['id'] ) ); ?>
									<?php disabled( $locked ); ?>
								>
								<span class="at-toggle__track">
									<span class="at-toggle__thumb"></span>
								</span>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<div class="at-actions">
			<button type="submit" class="at-save-btn">
				<?php esc_html_e( 'Save Settings', 'admin-tools' ); ?>
			</button>
		</div>

	</form>

</div>
