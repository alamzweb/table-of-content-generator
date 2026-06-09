<?php
namespace TOC_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	private string $option_key = 'table_of_content_generator_settings';
	private array $defaults;

	public function __construct() {
		$this->defaults = [
			'general_enable'     => 0,
			'global_location'    => 'before_content',
			'headings_h2'        => 1,
			'headings_h3'        => 1,
			'headings_h4'        => 0,
			'headings_h5'        => 0,
			'headings_h6'        => 0,
			'show_numbering'     => 0,
			'enable_collapsible' => 1,
			'enable_sticky'      => 0,
			'sticky_position'    => 'right',
			'enable_highlight'   => 1,
			'style_preset'       => 'modern',
			'bg_color'           => '#ffffff',
			'border_color'       => '#e2e8f0',
			'text_color'         => '#1e293b',
			'link_color'         => '#2563eb',
			'accent_color'       => '#3b82f6',
			'content_types'      => [ 'post', 'page' ],
		];
	}

	public function get_all(): array {
		$saved = get_option( $this->option_key, [] );
		return array_merge( $this->defaults, is_array( $saved ) ? $saved : [] );
	}

	public function get( string $key, mixed $default = null ): mixed {
		$settings = $this->get_all();
		return $settings[ $key ] ?? ( $default ?? ( $this->defaults[ $key ] ?? null ) );
	}

	public function update_all( array $settings ): bool {
		$sanitized = [];
		
		$sanitized['general_enable']     = isset( $settings['general_enable'] ) ? 1 : 0;
		$sanitized['global_location']    = sanitize_text_field( $settings['global_location'] ?? 'before_content' );
		$sanitized['headings_h2']        = isset( $settings['headings_h2'] ) ? 1 : 0;
		$sanitized['headings_h3']        = isset( $settings['headings_h3'] ) ? 1 : 0;
		$sanitized['headings_h4']        = isset( $settings['headings_h4'] ) ? 1 : 0;
		$sanitized['headings_h5']        = isset( $settings['headings_h5'] ) ? 1 : 0;
		$sanitized['headings_h6']        = isset( $settings['headings_h6'] ) ? 1 : 0;
		$sanitized['show_numbering']     = isset( $settings['show_numbering'] ) ? 1 : 0;
		$sanitized['enable_collapsible'] = isset( $settings['enable_collapsible'] ) ? 1 : 0;
		$sanitized['enable_sticky']      = isset( $settings['enable_sticky'] ) ? 1 : 0;
		$sanitized['sticky_position']    = sanitize_text_field( $settings['sticky_position'] ?? 'right' );
		$sanitized['enable_highlight']   = isset( $settings['enable_highlight'] ) ? 1 : 0;
		$sanitized['style_preset']       = sanitize_text_field( $settings['style_preset'] ?? 'modern' );
		
		$sanitized['bg_color']     = sanitize_hex_color( $settings['bg_color'] ?? '#ffffff' );
		$sanitized['border_color'] = sanitize_hex_color( $settings['border_color'] ?? '#e2e8f0' );
		$sanitized['text_color']   = sanitize_hex_color( $settings['text_color'] ?? '#1e293b' );
		$sanitized['link_color']   = sanitize_hex_color( $settings['link_color'] ?? '#2563eb' );
		$sanitized['accent_color'] = sanitize_hex_color( $settings['accent_color'] ?? '#3b82f6' );

		if ( isset( $settings['content_types'] ) && is_array( $settings['content_types'] ) ) {
			$sanitized['content_types'] = array_map( 'sanitize_key', $settings['content_types'] );
		} else {
			$sanitized['content_types'] = [];
		}

		return update_option( $this->option_key, $sanitized );
	}
}