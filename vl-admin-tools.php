<?php
/**
 * Plugin Name: WP Dev Tools
 * Description: Unified admin dashboard to enable/disable custom WordPress developer tools.
 * Version:     1.0.0
 * Author:      VinZoy
 * License:     GPL-2.0+
 * Text Domain: admin-tools
 */

defined( 'ABSPATH' ) || exit;

define( 'AT_VERSION',     '1.0.0' );
define( 'AT_PLUGIN_FILE', __FILE__ );
define( 'AT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'AT_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'AT_MODULES_DIR', AT_PLUGIN_DIR . 'modules/' );
define( 'AT_MODULES_URL', AT_PLUGIN_URL . 'modules/' );

require_once AT_PLUGIN_DIR . 'includes/core/class-admin-tools-activator.php';
require_once AT_PLUGIN_DIR . 'includes/core/class-admin-tools-deactivator.php';

register_activation_hook( __FILE__, array( 'Admin_Tools_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Admin_Tools_Deactivator', 'deactivate' ) );

require_once AT_PLUGIN_DIR . 'includes/class-admin-tools.php';

add_action( 'plugins_loaded', array( 'Admin_Tools', 'init' ) );
