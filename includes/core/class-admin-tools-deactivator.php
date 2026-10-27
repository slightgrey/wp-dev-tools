<?php

defined( 'ABSPATH' ) || exit;

class Admin_Tools_Deactivator {

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
