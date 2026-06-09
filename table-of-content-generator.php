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
	exit; // Exit if accessed directly.
}

// Define core constants
define( 'TOC_GENERATOR_VERSION', '1.0.0' );
define( 'TOC_GENERATOR_FILE', __FILE__ );
define( 'TOC_GENERATOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'TOC_GENERATOR_URL', plugin_dir_url( __FILE__ ) );

// Autoload core classes
require_once TOC_GENERATOR_PATH . 'includes/class-plugin.php';
require_once TOC_GENERATOR_PATH . 'includes/class-settings.php';
require_once TOC_GENERATOR_PATH . 'includes/class-admin.php';
require_once TOC_GENERATOR_PATH . 'includes/class-toc-generator.php';
require_once TOC_GENERATOR_PATH . 'includes/class-shortcode.php';
require_once TOC_GENERATOR_PATH . 'includes/class-gutenberg-block.php';

/**
 * Initialize the core plugin framework.
 */
function run_table_of_content_generator() {
	$plugin = new TOC_Generator\Plugin();
	$plugin->run();
}
run_table_of_content_generator();