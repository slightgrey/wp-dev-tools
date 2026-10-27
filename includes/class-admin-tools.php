<?php

defined( 'ABSPATH' ) || exit;

class Admin_Tools {

	private static ?Admin_Tools $instance = null;

	private Admin_Tools_Container $container;
	private Admin_Tools_Loader    $loader;
	private array                 $module_map = [];

	public static function init(): void {
		if ( null !== self::$instance ) {
			return;
		}
		self::$instance = new self();
		self::$instance->boot();
	}

	private function __construct() {
		require_once AT_PLUGIN_DIR . 'includes/di/class-admin-tools-container.php';
		require_once AT_PLUGIN_DIR . 'includes/di/interface-module.php';
		require_once AT_PLUGIN_DIR . 'includes/di/at-module-autoloader.php';
		require_once AT_PLUGIN_DIR . 'includes/core/class-admin-tools-loader.php';
		require_once AT_PLUGIN_DIR . 'includes/core/class-admin-tools-i18n.php';
		require_once AT_PLUGIN_DIR . 'admin/class-admin-tools-admin.php';

		$this->container = new Admin_Tools_Container();
		$this->loader    = new Admin_Tools_Loader();

		$this->configure_container();
	}

	private function configure_container(): void {
		$c = $this->container;

		$c->bind( Admin_Tools_Loader::class, fn() => $this->loader );
		$c->bind( Admin_Tools_i18n::class,   fn() => new Admin_Tools_i18n() );
		$c->bind( Admin_Tools_Admin::class,  fn() => new Admin_Tools_Admin() );

		foreach ( glob( AT_MODULES_DIR . '*/register.php' ) as $file ) {
			$register = require $file;
			if ( is_callable( $register ) ) {
				$entry = $register( $c );
				if ( is_array( $entry ) && isset( $entry['id'], $entry['class'] ) ) {
					$this->module_map[ $entry['id'] ] = $entry['class'];
				}
			}
		}
	}

	private function boot(): void {
		// i18n
		$i18n = $this->container->get( Admin_Tools_i18n::class );
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );

		// Boot enabled modules.
		$enabled = (array) get_option( 'admin_tools_enabled_modules', array() );

		foreach ( $enabled as $id ) {
			if ( isset( $this->module_map[ $id ] ) ) {
				$this->container->get( $this->module_map[ $id ] )->boot( $this->loader );
			}
		}

		// Dashboard always boots — priority 5 so the parent menu exists
		// before any module calls add_submenu_page('admin-tools', ...) at priority 10.
		$dashboard = $this->container->get( Admin_Tools_Admin::class );
		$this->loader->add_action( 'admin_menu',           $dashboard, 'add_menu_page', 5 );
		$this->loader->add_action( 'admin_enqueue_scripts', $dashboard, 'enqueue_assets' );
		$this->loader->add_action( 'admin_post_admin_tools_save_settings', $dashboard, 'handle_save' );

		$this->loader->run();
	}
}
