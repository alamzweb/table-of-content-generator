<?php
namespace TOC_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gutenberg_Block {
	private TOC_Core_Generator $generator;

	public function __construct( TOC_Core_Generator $generator ) {
		$this->generator = $generator;
	}

	public function init(): void {
		add_action( 'init', [ $this, 'register_native_gutenberg_block' ] );
	}

	public function register_native_gutenberg_block(): void {
		wp_register_script(
			'toc-generator-block-engine',
			TOC_GENERATOR_URL . 'assets/js/block.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor' ],
			TOC_GENERATOR_VERSION
		);

		register_block_type( 'table-of-content-generator/navigation-block', [
			'editor_script'   => 'toc-generator-block-engine',
			'render_callback' => [ $this, 'render_block_callback' ]
		]);
	}

	public function render_block_callback( array $attributes ): string {
		global $post;
		if ( ! isset( $post->post_content ) ) {
			return '';
		}
		return $this->generator->parse_and_inject( $post->post_content, true );
	}
}