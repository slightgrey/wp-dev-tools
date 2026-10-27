<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'admin_tools_enabled_modules' );
