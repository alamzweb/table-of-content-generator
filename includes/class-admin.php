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
				<p class="description"><?php esc_html_e( 'Premium-quality responsive documentation structure system.', 'table-of-content-generator' ); ?></p>
			</div>
			<div class="toc-dashboard-card welcome-card">
				<h2><?php esc_html_e( 'Getting Started', 'table-of-content-generator' ); ?></h2>
				<p><?php esc_html_e( 'By default, the global content output engine is disabled. You are in complete control of how your reading architectures render.', 'table-of-content-generator' ); ?></p>
				
				<div class="toc-methods-grid">
					<div class="toc-method-box">
						<span class="dashicons dashicons-admin-settings"></span>
						<h3><?php esc_html_e( '1. Global System Integration', 'table-of-content-generator' ); ?></h3>
						<p><?php esc_html_e( 'Head over to Settings, check "Enable Plugin Layout Automation Engine", map your targets, and deploy instantly across selected taxonomies.', 'table-of-content-generator' ); ?></p>
					</div>
					<div class="toc-method-box">
						<span class="dashicons dashicons-shortcode"></span>
						<h3><?php esc_html_e( '2. Theme Agnostic Shortcode', 'table-of-content-generator' ); ?></h3>
						<p><?php esc_html_e( 'Drop the following clean runtime markup snippet directly into any template, builder, or classic layout:', 'table-of-content-generator' ); ?></p>
						<code>[toc_generator]</code>
					</div>
					<div class="toc-method-box">
						<span class="dashicons dashicons-block-default"></span>
						<h3><?php esc_html_e( '3. Native Block Engine', 'table-of-content-generator' ); ?></h3>
						<p><?php esc_html_e( 'Insert our rich custom visual Block directly inside your Gutenberg canvas for independent rendering structures.', 'table-of-content-generator' ); ?></p>
					</div>
				</div>
				<hr>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=table-of-content-generator-settings' ) ); ?>" class="button button-primary button-large"><?php esc_html_e( 'Configure Settings Engine', 'table-of-content-generator' ); ?></a>
			</div>
		</div>
		<?php
	}

	public function render_settings_page(): void {
		if ( isset( $_POST['toc_settings_nonce_verify'] ) && wp_verify_nonce( $_POST['toc_settings_nonce_verify'], 'save_toc_settings_action' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				$this->settings->update_all( $_POST );
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configuration parameters successfully updated and flushed.', 'table-of-content-generator' ) . '</p></div>';
			}
		}

		$data       = $this->settings->get_all();
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		?>
		<div class="wrap toc-admin-wrap">
			<div class="toc-admin-header">
				<h1><?php esc_html_e( 'Engine Configuration Parameters', 'table-of-content-generator' ); ?></h1>
			</div>

			<form method="post" action="" class="toc-settings-form">
				<?php wp_nonce_field( 'save_toc_settings_action', 'toc_settings_nonce_verify' ); ?>
				
				<div class="toc-settings-layout">
					<div class="toc-settings-main">
						
						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Global Deployment Scope', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Global Automation Output', 'table-of-content-generator' ); ?></th>
										<td>
											<label class="toc-switch">
												<input type="checkbox" name="general_enable" value="1" <?php checked( $data['general_enable'], 1 ); ?>>
												<span class="toc-slider"></span>
											</label>
											<p class="description"><?php esc_html_e( 'Injects the layout dynamically via core post filters without altering standard template markup.', 'table-of-content-generator' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Automated Alignment Location', 'table-of-content-generator' ); ?></th>
										<td>
											<select name="global_location" class="regular-text">
												<option value="before_content" <?php selected( $data['global_location'], 'before_content' ); ?>><?php esc_html_e( 'Prepended Before Post Block Layout (Top)', 'table-of-content-generator' ); ?></option>
												<option value="after_content" <?php selected( $data['global_location'], 'after_content' ); ?>><?php esc_html_e( 'Appended Post Block Layout Base (Bottom)', 'table-of-content-generator' ); ?></option>
												<option value="before_first_heading" <?php selected( $data['global_location'], 'before_first_heading' ); ?>><?php esc_html_e( 'Positioned Explicitly Before First Active Heading Node', 'table-of-content-generator' ); ?></option>
											</select>
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Heading Structural Scanning Depth', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<p class="description"><?php esc_html_e( 'Select the individual structural DOM node weights that will populate the visual navigation tree map.', 'table-of-content-generator' ); ?></p>
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

						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Interaction and Functional Ergonomics', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Decimal Tree Structure Numbering', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="show_numbering" name="show_numbering" value="1" <?php checked( $data['show_numbering'], 1 ); ?>>
											<label for="show_numbering"><?php esc_html_e( 'Prepend structured nested dynamic decimals onto list indexes.', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Accordion Framework Collapsibility', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="enable_collapsible" name="enable_collapsible" value="1" <?php checked( $data['enable_collapsible'], 1 ); ?>>
											<label for="enable_collapsible"><?php esc_html_e( 'Allow continuous toggle operations on structural container maps.', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Persistent Sticky Sidebar Logic', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="enable_sticky" name="enable_sticky" value="1" <?php checked( $data['enable_sticky'], 1 ); ?>>
											<label for="enable_sticky"><?php esc_html_e( 'Pin the container inline inside viewport layers when execution boundaries shift.', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Intersection Observer Highlighting', 'table-of-content-generator' ); ?></th>
										<td>
											<input type="checkbox" id="enable_highlight" name="enable_highlight" value="1" <?php checked( $data['enable_highlight'], 1 ); ?>>
											<label for="enable_highlight"><?php esc_html_e( 'Dynamically trace reading focus paths onto viewport nodes in real-time.', 'table-of-content-generator' ); ?></label>
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Post Taxonomy Automation Targeting', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<p class="description"><?php esc_html_e( 'Choose structural content blocks mapped to automated execution threads.', 'table-of-content-generator' ); ?></p>
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

					<div class="toc-settings-sidebar">
						<div class="toc-card continuous-accent-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Theme Engine Presets', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body">
								<select name="style_preset" class="all-options-width">
									<option value="modern" <?php selected( $data['style_preset'], 'modern' ); ?>><?php esc_html_e( 'Modern SaaS System Minimalist', 'table-of-content-generator' ); ?></option>
									<option value="documentation" <?php selected( $data['style_preset'], 'documentation' ); ?>><?php esc_html_e( 'Developer Docs Ecosystem (GitBook)', 'table-of-content-generator' ); ?></option>
									<option value="minimal" <?php selected( $data['style_preset'], 'minimal' ); ?>><?php esc_html_e( 'Ultra Clean Plain Minimal', 'table-of-content-generator' ); ?></option>
									<option value="card" <?php selected( $data['style_preset'], 'card' ); ?>><?php esc_html_e( 'Structured Shadow Boxed Card', 'table-of-content-generator' ); ?></option>
								</select>
							</div>
						</div>

						<div class="toc-card">
							<div class="toc-card-header"><h3><?php esc_html_e( 'Color Palette Override Matrix', 'table-of-content-generator' ); ?></h3></div>
							<div class="toc-card-body custom-colors-flex">
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Container Canvas Base', 'table-of-content-generator' ); ?></label>
									<input type="text" name="bg_color" class="toc-color-field" value="<?php echo esc_attr( $data['bg_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Structural Border Vector', 'table-of-content-generator' ); ?></label>
									<input type="text" name="border_color" class="toc-color-field" value="<?php echo esc_attr( $data['border_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Primary Typography Node', 'table-of-content-generator' ); ?></label>
									<input type="text" name="text_color" class="toc-color-field" value="<?php echo esc_attr( $data['text_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Hyperlink State Vector', 'table-of-content-generator' ); ?></label>
									<input type="text" name="link_color" class="toc-color-field" value="<?php echo esc_attr( $data['link_color'] ); ?>">
								</div>
								<div class="color-picker-unit">
									<label><?php esc_html_e( 'Observer Focus Accent Accent', 'table-of-content-generator' ); ?></label>
									<input type="text" name="accent_color" class="toc-color-field" value="<?php echo esc_attr( $data['accent_color'] ); ?>">
								</div>
							</div>
						</div>

						<div class="toc-submit-wrapper">
							<?php submit_button( __( 'Flush Engine State Changes', 'table-of-content-generator' ), 'primary large', 'submit_toc_form', false ); ?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
}