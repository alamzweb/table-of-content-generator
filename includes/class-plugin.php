<?php
namespace TOC_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	public function run(): void {
		// Initialize Subsystems
		$settings        = new Settings();
		$admin           = new Admin( $settings );
		$toc_generator   = new TOC_Core_Generator( $settings );
		$shortcode       = new Shortcode( $toc_generator );
		$gutenberg_block = new Gutenberg_Block( $toc_generator );

		// Register Hooks
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
		
		// Hook into core formatting lifecycle
		$admin->init();
		$toc_generator->init();
		$shortcode->init();
		$gutenberg_block->init();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'table-of-content-generator',
			false,
			dirname( plugin_basename( TOC_GENERATOR_FILE ) ) . '/languages/'
		);
	}

	public function enqueue_frontend_assets(): void {
		wp_enqueue_style(
			'toc-generator-frontend',
			TOC_GENERATOR_URL . 'assets/css/frontend.css',
			[],
			TOC_GENERATOR_VERSION
		);

		wp_enqueue_script(
			'toc-generator-frontend',
			TOC_GENERATOR_URL . 'assets/js/frontend.js',
			[],
			TOC_GENERATOR_VERSION,
			true
		);
	}
}