<?php
/**
 * Plugin Name:       Table of Content Generator
 * Plugin URI:        https://sitecroftdev.vercel.app/plugins/table-of-content-generator
 * Description:       Automatically generate beautiful, customizable, and responsive tables of content from your content headings.
 * Version:           1.0.0
 * Author:            Saad
 * Author URI:        https://sitecroftdev.vercel.app/
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       table-of-content-generator
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Define Core Constants Safely
if ( ! defined( 'TOC_GENERATOR_VERSION' ) ) {
	define( 'TOC_GENERATOR_VERSION', '1.0.0' );
}
if ( ! defined( 'TOC_GENERATOR_PATH' ) ) {
	define( 'TOC_GENERATOR_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'TOC_GENERATOR_URL' ) ) {
	define( 'TOC_GENERATOR_URL', plugin_dir_url( __FILE__ ) );
}

// 2. Load Core Components using REQUIRE_ONCE (Prevents E_COMPILE_ERROR)
require_once TOC_GENERATOR_PATH . 'includes/class-settings.php';
require_once TOC_GENERATOR_PATH . 'includes/class-admin.php';
require_once TOC_GENERATOR_PATH . 'includes/class-toc-generator.php';

// 3. Initialize the components safely inside a single hook
add_action( 'plugins_loaded', 'toc_generator_init_plugin' );

function toc_generator_init_plugin() {
	// Create the settings instance
	$settings = new \TOC_Generator\Settings();

	// Initialize Admin dashboard panels
	if ( is_admin() ) {
		$admin = new \TOC_Generator\Admin( $settings );
		$admin->init();
	}

	// Initialize Frontend parsing engine
	$frontend = new \TOC_Generator\TOC_Core_Generator( $settings );
	$frontend->init();
}