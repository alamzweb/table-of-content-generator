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
			__( 'Table of Content Generator', 'table-of-content-generator' ),
			__( 'TOC Generator', 'table-of-content-generator' ),
			'manage_options',
			'table-of-content-generator',
			[ $this, 'render_dashboard_page' ],
			'dashicons-editor-ol',
			81
		);

		add_submenu_page(
			'table-of-content-generator',
			__( 'Dashboard', 'table-of-content-generator' ),
			__( 'Dashboard', 'table-of-content-generator' ),
			'manage_options',
			'table-of-content-generator',
			[ $this, 'render_dashboard_page' ]
		);

		add_submenu_page(
			'table-of-content-generator',
			__( 'Settings', 'table-of-content-generator' ),
			__( 'Settings', 'table-of-content-generator' ),
			'manage_options',
			'table-of-content-generator-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( str_contains( $hook, 'table-of-content-generator' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'toc-generator-admin', TOC_GENERATOR_URL . 'assets/css/admin.css', [], TOC_GENERATOR_VERSION );
			wp_enqueue_script( 'toc-generator-admin', TOC_GENERATOR_URL . 'assets/js/admin.js', [ 'jquery', 'wp-color-picker' ], TOC_GENERATOR_VERSION, true );
		}
	}

	public function render_dashboard_page(): void {
		?>
		<div class="wrap toc-admin-wrap">
			<div class="toc-admin-header">
				<h1><?php esc_html_e( 'Table of Content Generator', 'table-of-content-generator' ); ?></h1>
				<p class="description"><?php esc_html_e( 'Create modern, beautiful navigation for your articles and documentation.', 'table-of-content-generator' ); ?></p>
			</div>
			<div class="toc-dashboard-card welcome-card">
				<h2><?php esc_html_e( 'Getting Started', 'table-of-content-generator' ); ?></h2>
				<p><?php esc_html_e( 'By default, the table of contents is disabled globally. You can choose exactly how it appears on your site using one of the methods below:', 'table-of-content-generator' ); ?></p>
				
				<div class="toc-methods-grid">
					<div class="toc-method-box">
						<span class="dashicons dashicons-admin-settings"></span>
						<h3><?php esc_html_e( '1. Enable Globally', 'table-of-content-generator' ); ?></h3>
						<p><?php esc_html_e( 'Go to the Settings page, enable global display, and choose which post types should automatically show the table of contents.', 'table-of-content-generator' ); ?></p>
					</div>
					<div class="toc-method-box">
						<span class="dashicons dashicons-shortcode"></span>
						<h3><?php esc_html_e( '2. Use a Shortcode', 'table-of-content-generator' ); ?></h3>
						<p><?php esc_html_e( 'Paste this shortcode anywhere inside your post or page content to display the table of contents exactly where you want it:', 'table-of-content-generator' ); ?></p>
						<code>[toc_generator]</code>
					</div>
					<div class="toc-method-box">
						<span class="dashicons dashicons-block-default"></span>
						<h3><?php esc_html_e( '3. Use the Block Editor', 'table-of-content-generator' ); ?></h3>
						<p><?php esc_html_e( 'Add the native "Table of Content" block anywhere within the Gutenberg block editor for specific pages.', 'table-of-content-generator' ); ?></p>
					</div>
				</div>
				<hr>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=table-of-content-generator-settings' ) ); ?>" class="button button-primary button-large"><?php esc_html_e( 'Go to Settings Page', 'table-of-content-generator' ); ?></a>
			</div>
		</div>
		<?php
	}

	public function render_settings_page(): void {
		if ( isset( $_POST['toc_settings_nonce_verify'] ) && wp_verify_nonce( $_POST['toc_settings_nonce_verify'], 'save_toc_settings_action' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				$this->settings->update_all( $_POST );
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'table-of-content-generator' ) . '</p></div>';
			}
		}

		$data       = $this->settings->get_all();
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		?>
		<div class="wrap toc-admin-wrap">
			<div class="toc-admin-header">
				<h1><?php esc_html_e( 'Plugin Settings', 'table-of-content-generator' ); ?></h1>
			</div>

			<form method="post" action="" class="toc-settings-form">
				<?php wp_nonce_field( 'save_toc_settings_action', 'toc_settings_nonce_verify' ); ?>
				
				<div class="toc-settings-layout">
					<div class="toc-settings-main">
						
						<!-- General Section -->
						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Global Display Settings', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable Global Display', 'table-of-content-generator' ); ?></th>
										<td>
											<label class="toc-switch">
												<input type="checkbox" name="general_enable" value="1" <?php checked( $data['general_enable'], 1 ); ?>>
												<span class="toc-slider"></span>
											</label>
											<p class="description"><?php esc_html_e( 'Automatically display the table of contents on selected post types without needing shortcodes.', 'table-of-content-generator' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Global Location', 'table-of-content-generator' ); ?></th>
										<td>
											<select name="global_location" class="regular-text">
												<option value="before_content" <?php selected( $data['global_location'], 'before_content' ); ?>><?php esc_html_e( 'Before content (Top)', 'table-of-content-generator' ); ?></option>
												<option value="after_content" <?php selected( $data['global_location'], 'after_content' ); ?>><?php esc_html_e( 'After content (Bottom)', 'table-of-content-generator' ); ?></option>
												<option value="before_first_heading" <?php selected( $data['global_location'], 'before_first_heading' ); ?>><?php esc_html_e( 'Right before the first heading', 'table-of-content-generator' ); ?></option>
											</select>
										</td>
									</tr>
								</table>
							</div>
						</div>

						<!-- Headings Filter Scope -->
						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Headings to Include', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<p class="description"><?php esc_html_e( 'Select which heading levels should be scanned and added to the table of contents.', 'table-of-content-generator' ); ?></p>
								<div class="toc-checkbox-group">
									<?php foreach ( [ 'h2', 'h3', 'h4', 'h5', 'h6' ] as $h ) : ?>
										<label class="toc-checkbox-label">
											<input type="checkbox" name="headings_<?php echo esc_attr( $h ); ?>" value="1" <?php checked( $data[ 'headings_' . $h ], 1 ); ?>>
											<span><?php echo esc_html( strtoupper( $h ) ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<!-- Interface Interaction Engine -->
						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Display & Behavior Options', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Show Heading Numbers', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="show_numbering" name="show_numbering" value="1" <?php checked( $data['show_numbering'], 1 ); ?>>
											<label for="show_numbering"><?php esc_html_e( 'Add numbers before headings (e.g., 1., 1.1, 1.2).', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable Collapsible Layout', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="enable_collapsible" name="enable_collapsible" value="1" <?php checked( $data['enable_collapsible'], 1 ); ?>>
											<label for="enable_collapsible"><?php esc_html_e( 'Allow visitors to collapse or expand the table of contents.', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable Sticky Sidebar', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="enable_sticky" name="enable_sticky" value="1" <?php checked( $data['enable_sticky'], 1 ); ?>>
											<label for="enable_sticky"><?php esc_html_e( 'Keep the table of contents visible on the side while scrolling down.', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Sticky Sidebar Position', 'table-of-content-generator' ); ?></th>
										<td>
											<select name="sticky_position" class="regular-text">
												<option value="right" <?php selected( $data['sticky_position'], 'right' ); ?>><?php esc_html_e( 'Right Side', 'table-of-content-generator' ); ?></option>
												<option value="left" <?php selected( $data['sticky_position'], 'left' ); ?>><?php esc_html_e( 'Left Side', 'table-of-content-generator' ); ?></option>
											</select>
											<p class="description"><?php esc_html_e( 'Choose which side of the text the sidebar floats on when Sticky Mode is enabled.', 'table-of-content-generator' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Highlight Active Section', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="enable_highlight" name="enable_highlight" value="1" <?php checked( $data['enable_highlight'], 1 ); ?>>
											<label for="enable_highlight"><?php esc_html_e( 'Automatically highlight sections in the list as you scroll past them.', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
								</table>
							</div>
						</div>

						<!-- Content Boundaries mapping -->
						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Supported Content Post Types', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<p class="description"><?php esc_html_e( 'Select the post types where the table of contents can be used.', 'table-of-content-generator' ); ?></p>
								<div class="toc-checkbox-group">
									<?php foreach ( $post_types as $pt ) : ?>
										<label class="toc-checkbox-label">
											<input type="checkbox" name="content_types[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, (array) $data['content_types'], true ) ); ?>>
											<span><?php echo esc_html( $pt->label ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>

					<!-- Style Panel Sidebar -->
					<div class="toc-settings-sidebar">
						<div class="toc-card continuous-accent-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Style Presets', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<select name="style_preset" class="all-options-width">
									<option value="modern" <?php selected( $data['style_preset'], 'modern' ); ?>><?php esc_html_e( 'Modern SaaS (Clean)', 'table-of-content-generator' ); ?></option>
									<option value="documentation" <?php selected( $data['style_preset'], 'documentation' ); ?>><?php esc_html_e( 'Developer Docs (GitBook style)', 'table-of-content-generator' ); ?></option>
									<option value="minimal" <?php selected( $data['style_preset'], 'minimal' ); ?>><?php esc_html_e( 'Minimalist (Ultra clean)', 'table-of-content-generator' ); ?></option>
									<option value="card" <?php selected( $data['style_preset'], 'card' ); ?>><?php esc_html_e( 'Card Layout (Boxed with shadow)', 'table-of-content-generator' ); ?></option>
								</select>
							</div>
						</div>

						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Custom Color Theme', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body custom-colors-flex">
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Background Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="bg_color" class="toc-color-field" value="<?php echo esc_attr( $data['bg_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Border Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="border_color" class="toc-color-field" value="<?php echo esc_attr( $data['border_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Text Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="text_color" class="toc-color-field" value="<?php echo esc_attr( $data['text_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Link Hover Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="link_color" class="toc-color-field" value="<?php echo esc_attr( $data['link_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Accent Highlight Color', 'table-of-content-generator' ); ?></label>
									<input type="text" name="accent_color" class="toc-color-field" value="<?php echo esc_attr( $data['accent_color'] ); ?>">
								</div>
							</div>
						</div>

						<div class="toc-submit-wrapper">
							<?php submit_button( __( 'Save Changes', 'table-of-content-generator' ), 'primary large', 'submit_toc_form', false ); ?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
}