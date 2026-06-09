<?php
namespace TOC_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {
	private Settings $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	public function register_menus(): void {
		add_menu_page(
			__( 'TOC Generator', 'table-of-content-generator' ),
			__( 'TOC Generator', 'table-of-content-generator' ),
			'manage_options',
			'table-of-content-generator',
			[ $this, 'render_settings_page' ], // Directly load settings for a faster workflow
			'dashicons-editor-ol',
			81
		);
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( str_contains( $hook, 'table-of-content-generator' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			
			// Dynamically matching the correct path inside your plugin directory
			wp_enqueue_style( 'toc-generator-admin', TOC_GENERATOR_URL . 'assets/css/admin.css', [], TOC_GENERATOR_VERSION );
			wp_enqueue_script( 'toc-generator-admin', TOC_GENERATOR_URL . 'assets/js/admin.js', [ 'jquery', 'wp-color-picker' ], TOC_GENERATOR_VERSION, true );
		}
	}

	public function render_settings_page(): void {
		if ( isset( $_POST['toc_settings_nonce_verify'] ) && wp_verify_nonce( $_POST['toc_settings_nonce_verify'], 'save_toc_settings_action' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				$this->settings->update_all( $_POST );
				echo '<div class="notice notice-success is-dismissible" style="margin: 20px 0 0 0;"><p>' . esc_html__( 'Settings saved successfully.', 'table-of-content-generator' ) . '</p></div>';
			}
		}

		$data       = $this->settings->get_all();
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		?>
		<div class="wrap toc-premium-admin-dashboard">
			<div class="toc-dashboard-header">
				<div class="toc-brand-title">
					<h1><?php esc_html_e( 'Table of Content Generator', 'table-of-content-generator' ); ?></h1>
					<p class="toc-subtitle"><?php esc_html_e( 'Configure your clean, high-performance navigation and structural sidebars.', 'table-of-content-generator' ); ?></p>
				</div>
			</div>

			<form method="post" action="" class="toc-modern-form-wrapper">
				<?php wp_nonce_field( 'save_toc_settings_action', 'toc_settings_nonce_verify' ); ?>
				
				<div class="toc-dashboard-layout-grid">
					
					<!-- Left Main Controls Panel -->
					<div class="toc-panel-main-column">
						
						<!-- Section 1: Global Visibility -->
						<div class="toc-premium-card">
							<div class="toc-card-header-block">
								<h3><?php esc_html_e( 'Global Visibility Settings', 'table-of-content-generator' ); ?></h3>
							</div>
							<div class="toc-card-body-block">
								<div class="toc-form-row-flex">
									<div class="toc-label-column">
										<label class="toc-main-input-label"><?php esc_html_e( 'Enable Automatically', 'table-of-content-generator' ); ?></label>
										<span class="toc-field-hint"><?php esc_html_e( 'Display the table of contents across selected content styles globally without manually adding shortcodes.', 'table-of-content-generator' ); ?></span>
									</div>
									<div class="toc-input-column">
										<label class="toc-toggle-switch">
											<input type="checkbox" name="general_enable" value="1" <?php checked( $data['general_enable'], 1 ); ?>>
											<span class="toc-toggle-slider"></span>
										</label>
									</div>
								</div>

								<div class="toc-form-row-flex border-top-divider">
									<div class="toc-label-column">
										<label class="toc-main-input-label"><?php esc_html_e( 'Default Placement Location', 'table-of-content-generator' ); ?></label>
										<span class="toc-field-hint"><?php esc_html_e( 'Where exactly inside the article layout should the generated index block inject itself?', 'table-of-content-generator' ); ?></span>
									</div>
									<div class="toc-input-column">
										<select name="global_location" class="toc-premium-select-field">
											<option value="before_content" <?php selected( $data['global_location'], 'before_content' ); ?>><?php esc_html_e( 'Top of Content (Before everything)', 'table-of-content-generator' ); ?></option>
											<option value="after_content" <?php selected( $data['global_location'], 'after_content' ); ?>><?php esc_html_e( 'Bottom of Content (After everything)', 'table-of-content-generator' ); ?></option>
											<option value="before_first_heading" <?php selected( $data['global_location'], 'before_first_heading' ); ?>><?php esc_html_e( 'Directly above the first Heading tag', 'table-of-content-generator' ); ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<!-- Section 2: Structure & Scans -->
						<div class="toc-premium-card">
							<div class="toc-card-header-block">
								<h3><?php esc_html_e( 'Heading Tag Scanner Filter', 'table-of-content-generator' ); ?></h3>
							</div>
							<div class="toc-card-body-block">
								<span class="toc-field-hint" style="margin-bottom: 15px; display: block;"><?php esc_html_e( 'Check the heading levels that should automatically build your navigation index list.', 'table-of-content-generator' ); ?></span>
								<div class="toc-modern-checkbox-row">
									<?php foreach ( [ 'h2', 'h3', 'h4', 'h5', 'h6' ] as $h ) : ?>
										<label class="toc-checkbox-pill">
											<input type="checkbox" name="headings_<?php echo esc_attr( $h ); ?>" value="1" <?php checked( $data[ 'headings_' . $h ], 1 ); ?>>
											<span class="toc-pill-label"><?php echo esc_html( strtoupper( $h ) ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<!-- Section 3: Interface Behavior -->
						<div class="toc-premium-card">
							<div class="toc-card-header-block">
								<h3><?php esc_html_e( 'Display Styles & Layout Behaviors', 'table-of-content-generator' ); ?></h3>
							</div>
							<div class="toc-card-body-block">
								<div class="toc-form-row-flex">
									<div class="toc-label-column">
										<label class="toc-main-input-label"><?php esc_html_e( 'Sequential Decimal Numbering', 'table-of-content-generator' ); ?></label>
										<span class="toc-field-hint"><?php esc_html_e( 'Prefix list elements dynamically with ordered indices (e.g., 1., 1.1, 1.2).', 'table-of-content-generator' ); ?></span>
									</div>
									<div class="toc-input-column">
										<input type="checkbox" id="show_numbering" name="show_numbering" value="1" <?php checked( $data['show_numbering'], 1 ); ?> class="toc-modern-checkbox">
									</div>
								</div>

								<div class="toc-form-row-flex border-top-divider">
									<div class="toc-label-column">
										<label class="toc-main-input-label"><?php esc_html_e( 'Allow Collapsible Box Toggle', 'table-of-content-generator' ); ?></label>
										<span class="toc-field-hint"><?php esc_html_e( 'Add an interactive minimize button so visitors can collapse the index container box.', 'table-of-content-generator' ); ?></span>
									</div>
									<div class="toc-input-column">
										<input type="checkbox" id="enable_collapsible" name="enable_collapsible" value="1" <?php checked( $data['enable_collapsible'], 1 ); ?> class="toc-modern-checkbox">
									</div>
								</div>

								<div class="toc-form-row-flex border-top-divider">
									<div class="toc-label-column">
										<label class="toc-main-input-label"><?php esc_html_e( 'Enable Sticky Sidebar Mode', 'table-of-content-generator' ); ?></label>
										<span class="toc-field-hint"><?php esc_html_e( 'Transform the table into a floating navigation bar that moves with the reader during scroll.', 'table-of-content-generator' ); ?></span>
									</div>
									<div class="toc-input-column">
										<input type="checkbox" id="enable_sticky" name="enable_sticky" value="1" <?php checked( $data['enable_sticky'], 1 ); ?> class="toc-modern-checkbox">
									</div>
								</div>

								<div class="toc-form-row-flex border-top-divider">
									<div class="toc-label-column">
										<label class="toc-main-input-label"><?php esc_html_e( 'Sticky Sidebar Fixed Side', 'table-of-content-generator' ); ?></label>
										<span class="toc-field-hint"><?php esc_html_e( 'When Sticky Mode above is active, should the floating index stick to the left side or the right side of the text body?', 'table-of-content-generator' ); ?></span>
									</div>
									<div class="toc-input-column">
										<select name="sticky_position" class="toc-premium-select-field">
											<option value="right" <?php selected( $data['sticky_position'], 'right' ); ?>><?php esc_html_e( 'Right Side Sidebar Layout', 'table-of-content-generator' ); ?></option>
											<option value="left" <?php selected( $data['sticky_position'], 'left' ); ?>><?php esc_html_e( 'Left Side Sidebar Layout', 'table-of-content-generator' ); ?></option>
										</select>
									</div>
								</div>

								<div class="toc-form-row-flex border-top-divider">
									<div class="toc-label-column">
										<label class="toc-main-input-label"><?php esc_html_e( 'Highlight Live Viewport Section', 'table-of-content-generator' ); ?></label>
										<span class="toc-field-hint"><?php esc_html_e( 'Watch scrolling behavior and visually style the active link item as the user scrolls past sections.', 'table-of-content-generator' ); ?></span>
									</div>
									<div class="toc-input-column">
										<input type="checkbox" id="enable_highlight" name="enable_highlight" value="1" <?php checked( $data['enable_highlight'], 1 ); ?> class="toc-modern-checkbox">
									</div>
								</div>
							</div>
						</div>

						<!-- Section 4: Content Post Types -->
						<div class="toc-premium-card">
							<div class="toc-card-header-block">
								<h3><?php esc_html_e( 'Supported Content Management Nodes', 'table-of-content-generator' ); ?></h3>
							</div>
							<div class="toc-card-body-block">
								<span class="toc-field-hint" style="margin-bottom: 15px; display: block;"><?php esc_html_e( 'Restrict the dynamic table engine to specific post variations across your active database.', 'table-of-content-generator' ); ?></span>
								<div class="toc-modern-checkbox-row">
									<?php foreach ( $post_types as $pt ) : ?>
										<label class="toc-checkbox-pill">
											<input type="checkbox" name="content_types[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, (array) $data['content_types'], true ) ); ?>>
											<span class="toc-pill-label"><?php echo esc_html( $pt->label ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>

					<!-- Right Theme Sidebar Control Column Panel -->
					<div class="toc-panel-side-column">
						<div class="toc-premium-card toc-sticky-sidebar-save-box">
							<div class="toc-card-header-block text-accent-gradient">
								<h3><?php esc_html_e( 'Visual Preset Layouts', 'table-of-content-generator' ); ?></h3>
							</div>
							<div class="toc-card-body-block">
								<select name="style_preset" class="toc-premium-select-field full-width-element">
									<option value="modern" <?php selected( $data['style_preset'], 'modern' ); ?>><?php esc_html_e( 'Modern SaaS Interface', 'table-of-content-generator' ); ?></option>
									<option value="documentation" <?php selected( $data['style_preset'], 'documentation' ); ?>><?php esc_html_e( 'GitBook Documentation Minimalist', 'table-of-content-generator' ); ?></option>
									<option value="minimal" <?php selected( $data['style_preset'], 'minimal' ); ?>><?php esc_html_e( 'Ultra Minimalist Line Separation', 'table-of-content-generator' ); ?></option>
									<option value="card" <?php selected( $data['style_preset'], 'card' ); ?>><?php esc_html_e( 'Elevated Shadows Block Card Layout', 'table-of-content-generator' ); ?></option>
								</select>
							</div>
						</div>

						<div class="toc-premium-card">
							<div class="toc-card-header-block">
								<h3><?php esc_html_e( 'Custom Palette Overrides', 'table-of-content-generator' ); ?></h3>
							</div>
							<div class="toc-card-body-block palette-picker-stack">
								<div class="toc-color-picker-item">
									<label class="toc-color-label"><?php esc_html_e( 'Background Hex Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="bg_color" class="toc-color-picker-input-field" value="<?php echo esc_attr( $data['bg_color'] ); ?>">
								</div>
								<div class="toc-color-picker-item">
									<label class="toc-color-label"><?php esc_html_e( 'Border Hex Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="border_color" class="toc-color-picker-input-field" value="<?php echo esc_attr( $data['border_color'] ); ?>">
								</div>
								<div class="toc-color-picker-item">
									<label class="toc-color-label"><?php esc_html_e( 'Typography Core Base Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="text_color" class="toc-color-picker-input-field" value="<?php echo esc_attr( $data['text_color'] ); ?>">
								</div>
								<div class="toc-color-picker-item">
									<label class="toc-color-label"><?php esc_html_e( 'Link Interaction Hover State', 'table-of-content-generator' ); ?></label>
									<input type="text" name="link_color" class="toc-color-picker-input-field" value="<?php echo esc_attr( $data['link_color'] ); ?>">
								</div>
								<div class="toc-color-picker-item">
									<label class="toc-color-label"><?php esc_html_e( 'Active Highlight Color Accent', 'table-of-content-generator' ); ?></label>
									<input type="text" name="accent_color" class="toc-color-picker-input-field" value="<?php echo esc_attr( $data['accent_color'] ); ?>">
								</div>
							</div>
						</div>

						<!-- Standard, Easy to Read Action Button -->
						<div class="toc-save-action-footer-container">
							<?php submit_button( __( 'Save Changes', 'table-of-content-generator' ), 'primary large full-width-submit-btn', 'submit_toc_form', false ); ?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
}