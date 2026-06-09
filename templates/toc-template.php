<?php
/**
 * Isolated Component View Template Matrix.
 * Variables configured contextually inside compilation scopes:
 * - $inline_styles (safe key values string containing structured color bindings)
 * - $container_class_str (css state tracking strings)
 * - $is_collapsible (boolean expression state)
 * - $tree_html (fully sanitized string layout branches)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<nav class="<?php echo $container_class_str; ?>" <?php echo $inline_styles; ?> aria-label="<?php esc_attr_e( 'Table of contents navigation architecture', 'table-of-content-generator' ); ?>">
	<div class="toc-header-navigation-wrapper">
		<span class="toc-header-title-node">
			<span class="dashicons dashicons-format-aside structural-icon-accent"></span>
			<?php esc_html_e( 'Table of Contents', 'table-of-content-generator' ); ?>
		</span>
		<?php if ( $is_collapsible ) : ?>
			<button class="toc-collapse-trigger-element" aria-expanded="true" aria-label="<?php esc_attr_e( 'Toggle continuous content visibility matrix map', 'table-of-content-generator' ); ?>">
				<span class="toc-trigger-text text-state-expanded"><?php esc_html_e( 'Hide', 'table-of-content-generator' ); ?></span>
				<span class="toc-trigger-text text-state-collapsed"><?php esc_html_e( 'Show', 'table-of-content-generator' ); ?></span>
				<svg class="toc-chevron-vector-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
			</button>
		<?php endif; ?>
	</div>
	<div class="toc-collapsible-wrapper-panel">
		<ul class="toc-root-navigation-node-list">
			<?php echo $tree_html; ?>
		</ul>
	</div>
</nav>