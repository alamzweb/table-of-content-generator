<?php
/**
 * Fired when the plugin is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Purge plugin configuration settings array
delete_option( 'table_of_content_generator_settings' );