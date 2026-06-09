<?php
namespace TOC_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TOC_Core_Generator {
	private Settings $settings;
	private array $active_tags = [];

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public function init(): void {
		add_filter( 'the_content', [ $this, 'process_content_hijack' ], 100 );
	}

	public function process_content_hijack( string $content ): string {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( ! $this->settings->get( 'general_enable' ) ) {
			return $content;
		}

		$allowed_types = (array) $this->settings->get( 'content_types', [] );
		if ( ! in_array( get_post_type(), $allowed_types, true ) ) {
			return $content;
		}

		if ( str_contains( $content, 'class="table-of-content-generator-container"' ) ) {
			return $content;
		}

		return $this->parse_and_inject( $content, false );
	}

	public function parse_and_inject( string $content, bool $forced = false ): string {
		$tags = [];
		foreach ( [ 'h2', 'h3', 'h4', 'h5', 'h6' ] as $tag ) {
			if ( $forced || $this->settings->get( 'headings_' . $tag ) ) {
				$tags[] = $tag;
			}
		}

		if ( empty( $tags ) ) {
			return $content;
		}

		$this->active_tags = $tags;
		
		$pattern = '/<h([' . implode( '', array_map( function( $t ) {
			return substr( $t, 1 );
		}, $tags ) ) . '])([^>]*)>(.*?)<\/h\1>/is';

		if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			return $content;
		}

		$headings_tree = [];
		$slug_counts   = [];
		$modified_map  = [];

		foreach ( $matches as $match ) {
			$full_raw_html = $match[0];
			$tag_level     = (int) $match[1];
			$attributes    = $match[2];
			$inner_text    = strip_tags( $match[3] );
			$cleaned_text  = trim( $inner_text );

			if ( empty( $cleaned_text ) ) {
				continue;
			}

			if ( preg_match( '/id=["\']([^"\']+)["\']/i', $attributes, $id_match ) ) {
				$slug_anchor = sanitize_title( $id_match[1] );
			} else {
				$slug_anchor = sanitize_title( $cleaned_text );
				if ( empty( $slug_anchor ) ) {
					$slug_anchor = 'heading-' . md5( $cleaned_text );
				}
				
				if ( isset( $slug_counts[ $slug_anchor ] ) ) {
					$slug_counts[ $slug_anchor ]++;
					$slug_anchor .= '-' . $slug_counts[ $slug_anchor ];
				} else {
					$slug_counts[ $slug_anchor ] = 1;
				}

				$new_attribute_markup = ' id="' . esc_attr( $slug_anchor ) . '"' . $attributes;
				$replacement_heading  = "<h{$tag_level}{$new_attribute_markup}>{$match[3]}</h{$tag_level}>";
				$modified_map[ $full_raw_html ] = $replacement_heading;
			}

			$headings_tree[] = [
				'level' => $tag_level,
				'text'  => esc_html( $cleaned_text ),
				'link'  => $slug_anchor
			];
		}

		if ( empty( $headings_tree ) ) {
			return $content;
		}

		if ( ! empty( $modified_map ) ) {
			$content = str_replace( array_keys( $modified_map ), array_values( $modified_map ), $content );
		}

		$toc_html = $this->render_toc_markup( $headings_tree );
		
		if ( $forced ) {
			return $toc_html;
		}

		$placement = $this->settings->get( 'global_location', 'before_content' );

		if ( 'before_content' === $placement ) {
			return $toc_html . $content;
		} elseif ( 'after_content' === $placement ) {
			return $content . $toc_html;
		} elseif ( 'before_first_heading' === $placement ) {
			$first_heading_raw = array_key_first( $modified_map ) ? array_key_first( $modified_map ) : $matches[0][0];
			$pos = strpos( $content, $first_heading_raw );
			if ( false !== $pos ) {
				return substr_replace( $content, $toc_html . $first_heading_raw, $pos, strlen( $first_heading_raw ) );
			}
		}

		return $toc_html . $content;
	}

	public function render_toc_markup( array $tree ): string {
		$preset             = $this->settings->get( 'style_preset', 'modern' );
		$is_collapsible     = $this->settings->get( 'enable_collapsible', 1 );
		$is_sticky          = $this->settings->get( 'enable_sticky', 0 );
		$sticky_side        = $this->settings->get( 'sticky_position', 'right' );
		$is_highlight_ready = $this->settings->get( 'enable_highlight', 1 );
		$show_numbers       = $this->settings->get( 'show_numbering', 0 );

		$bg     = $this->settings->get( 'bg_color', '#ffffff' );
		$border = $this->settings->get( 'border_color', '#e2e8f0' );
		$text   = $this->settings->get( 'text_color', '#1e293b' );
		$link   = $this->settings->get( 'link_color', '#2563eb' );
		$accent = $this->settings->get( 'accent_color', '#3b82f6' );

		$inline_styles = "style=\"--toc-bg:{$bg}; --toc-border:{$border}; --toc-text:{$text}; --toc-link:{$link}; --toc-accent:{$accent};\"";

		$tree_html       = '';
		$current_depth   = 0;
		$counters        = [ 0, 0, 0, 0, 0, 0, 0 ];

		$min_level = 6;
		foreach ( $tree as $node ) {
			if ( $node['level'] < $min_level ) {
				$min_level = $node['level'];
			}
		}

		foreach ( $tree as $node ) {
			$depth = $node['level'] - $min_level;

			if ( $show_numbers ) {
				$counters[ $node['level'] ]++;
				for ( $i = $node['level'] + 1; $i <= 6; $i++ ) {
					$counters[ $i ] = 0;
				}
				$num_str = '';
				for ( $j = $min_level; $j <= $node['level']; $j++ ) {
					$num_str .= $counters[ $j ] . '.';
				}
				$prefix = '<span class="toc-node-index-number">' . $num_str . ' </span>';
			} else {
				$prefix = '';
			}

			if ( $depth > $current_depth ) {
				$tree_html    .= str_repeat( '<ul class="toc-nested-sub-node-layer">', $depth - $current_depth );
			} elseif ( $depth < $current_depth ) {
				$tree_html    .= str_repeat( '</ul>', $current_depth - $depth );
			}

			$current_depth = $depth;

			$tree_html .= sprintf(
				'<li class="toc-nav-item item-depth-h%1$d"><a href="#%2$s" class="toc-navigation-link">%3$s%4$s</a></li>',
				intval( $node['level'] ),
				esc_attr( $node['link'] ),
				$prefix,
				$node['text']
			);
		}

		if ( $current_depth > 0 ) {
			$tree_html .= str_repeat( '</ul>', $current_depth );
		}

		$container_classes = array_filter([
			'table-of-content-generator-container',
			'toc-preset-' . $preset,
			$is_sticky ? 'toc-runtime-sticky-active' : '',
			$is_sticky ? 'toc-sticky-side-' . $sticky_side : '',
			$is_highlight_ready ? 'toc-intersection-tracking' : ''
		]);

		$container_class_str = esc_attr( implode( ' ', $container_classes ) );

		ob_start();
		include TOC_GENERATOR_PATH . 'templates/toc-template.php';
		return ob_get_clean();
	}
}