<?php defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/components/card.php';
require_once __DIR__ . '/components/table.php';
require_once __DIR__ . '/components/badge.php';
require_once __DIR__ . '/components/empty-state.php';

$tool_groups = array(
	'System Info' => array(
		array( 'slug' => 'server',     'label' => 'Server Info' ),
		array( 'slug' => 'php-info',   'label' => 'PHP Info' ),
		array( 'slug' => 'php-ini',    'label' => 'PHP.INI' ),
		array( 'slug' => 'globals',    'label' => 'Globals' ),
		array( 'slug' => 'defines',    'label' => 'Constants' ),
		array( 'slug' => 'post-types', 'label' => 'Post Types' ),
		array( 'slug' => 'taxonomies', 'label' => 'Taxonomies' ),
		array( 'slug' => 'shortcodes', 'label' => 'Shortcodes' ),
		array( 'slug' => 'apis',       'label' => 'REST APIs' ),
		array( 'slug' => 'cron-jobs',  'label' => 'Cron Jobs' ),
	),
	'Data' => array(
		array( 'slug' => 'transients',     'label' => 'Transients' ),
		array( 'slug' => 'cookies',        'label' => 'Cookies' ),
		array( 'slug' => 'sessions',       'label' => 'Sessions' ),
		array( 'slug' => 'site-options',   'label' => 'Site Options' ),
		array( 'slug' => 'auto-drafts',    'label' => 'Auto Drafts' ),
		array( 'slug' => 'db-tables',      'label' => 'DB Tables' ),
		array( 'slug' => 'metadata',       'label' => 'Metadata' ),
		array( 'slug' => 'inactive-users', 'label' => 'Inactive Users' ),
		array( 'slug' => 'signups',        'label' => 'Signups' ),
	),
	'Files' => array(
		array( 'slug' => 'wpconfig', 'label' => 'WP-CONFIG' ),
		array( 'slug' => 'htaccess', 'label' => '.htaccess' ),
		array( 'slug' => 'logs',     'label' => 'Logs' ),
	),
	'Dev' => array(
		array( 'slug' => 'online-users', 'label' => 'Online Users' ),
		array( 'slug' => 'testing',      'label' => 'Testing' ),
	),
);

$valid_slugs = array();
foreach ( $tool_groups as $tools ) {
	foreach ( $tools as $t ) {
		$valid_slugs[] = $t['slug'];
	}
}

$active_slug = sanitize_key( $_GET['tool'] ?? 'server' );
if ( ! in_array( $active_slug, $valid_slugs, true ) ) {
	$active_slug = 'server';
}

$tool_file = AT_MODULES_DIR . 'dev-tools/admin/partials/tools/tool-' . $active_slug . '.php';
?>

<div style="display:flex; gap:0; margin:16px 20px 16px 0; min-height:640px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

	<!-- Sidebar -->
	<nav style="width:185px; flex-shrink:0; background:#fff; border:1px solid #e5e7eb; border-right:none; border-radius:8px 0 0 8px; overflow-y:auto;">
		<div style="padding:14px 16px; border-bottom:1px solid #e5e7eb;">
			<h1 style="font-size:13px; font-weight:700; color:#111827; margin:0; letter-spacing:.01em;">Debug Tools</h1>
		</div>
		<?php foreach ( $tool_groups as $group_label => $tools ) : ?>
			<div style="padding:6px 0;">
				<div style="padding:6px 14px 3px; font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em;">
					<?php echo esc_html( $group_label ); ?>
				</div>
				<?php foreach ( $tools as $tool ) :
					$is_active = ( $tool['slug'] === $active_slug );
					$link      = add_query_arg( array( 'page' => 'at-dev-tools', 'tool' => $tool['slug'] ), admin_url( 'admin.php' ) );
				?>
					<a
						href="<?php echo esc_url( $link ); ?>"
						class="at-dt-nav-link"
						style="display:block; padding:5px 14px; font-size:12px; color:<?php echo $is_active ? '#111827' : '#374151'; ?>; font-weight:<?php echo $is_active ? '600' : '400'; ?>; background:<?php echo $is_active ? '#f3f4f6' : 'transparent'; ?>; border-left:<?php echo $is_active ? '2px solid #111827' : '2px solid transparent'; ?>;"
						onmouseover="if(!<?php echo $is_active ? 'true' : 'false'; ?>)this.style.background='#f9fafb'"
						onmouseout="if(!<?php echo $is_active ? 'true' : 'false'; ?>)this.style.background='transparent'"
					>
						<?php echo esc_html( $tool['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</nav>

	<!-- Content -->
	<div style="flex:1; min-width:0; background:#fff; border:1px solid #e5e7eb; border-radius:0 8px 8px 0; padding:20px 24px; overflow:auto;">
		<?php
		if ( file_exists( $tool_file ) ) {
			include $tool_file;
		} else {
			at_dt_empty( 'Tool file not found: ' . $active_slug );
		}
		?>
	</div>

</div>
