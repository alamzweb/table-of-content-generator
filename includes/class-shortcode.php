<?php
namespace TOC_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcode {
	private TOC_Core_Generator $generator;

	public function __construct( TOC_Core_Generator $generator ) {
		$this->generator = $generator;
	}

	public function init(): void {
		add_shortcode( 'toc_generator', [ $this, 'render_shortcode' ] );
	}

	public function render_shortcode( array|string $atts = '' ): string {
		global $post;
		if ( ! isset( $post->post_content ) ) {
			return '';
		}

		// Pull current buffer state, force generation structure bypass controls
		return $this->generator->parse_and_inject( $post->post_content, true );
	}
}