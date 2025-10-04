<?php
/**
 * Admin Interface
 *
 * Provides WordPress admin interface for the MCP Adapter Plugin.
 *
 * @package mcp-adapter-plugin
 */

/**
 * Admin interface class.
 */
class MCP_Adapter_Admin {

	/**
	 * Initialize the admin interface.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Add admin menu items.
	 */
	public static function add_admin_menu() {
		add_menu_page(
			__( 'MCP Adapter', 'mcp-adapter-plugin' ),
			__( 'MCP Adapter', 'mcp-adapter-plugin' ),
			'manage_options',
			'mcp-adapter',
			array( __CLASS__, 'render_main_page' ),
			'dashicons-admin-generic',
			80
		);

		add_submenu_page(
			'mcp-adapter',
			__( 'Overview', 'mcp-adapter-plugin' ),
			__( 'Overview', 'mcp-adapter-plugin' ),
			'manage_options',
			'mcp-adapter',
			array( __CLASS__, 'render_main_page' )
		);

		add_submenu_page(
			'mcp-adapter',
			__( 'Registered Abilities', 'mcp-adapter-plugin' ),
			__( 'Abilities', 'mcp-adapter-plugin' ),
			'manage_options',
			'mcp-adapter-abilities',
			array( __CLASS__, 'render_abilities_page' )
		);

		add_submenu_page(
			'mcp-adapter',
			__( 'MCP Servers', 'mcp-adapter-plugin' ),
			__( 'MCP Servers', 'mcp-adapter-plugin' ),
			'manage_options',
			'mcp-adapter-servers',
			array( __CLASS__, 'render_servers_page' )
		);

		add_submenu_page(
			'mcp-adapter',
			__( 'Settings', 'mcp-adapter-plugin' ),
			__( 'Settings', 'mcp-adapter-plugin' ),
			'manage_options',
			'mcp-adapter-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public static function register_settings() {
		register_setting( 'mcp_adapter_settings', 'mcp_adapter_enable_examples' );
		register_setting( 'mcp_adapter_settings', 'mcp_adapter_enable_example_servers' );
		register_setting( 'mcp_adapter_settings', 'mcp_adapter_debug_mode' );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_scripts( $hook ) {
		// Only load on our admin pages
		if ( strpos( $hook, 'mcp-adapter' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'mcp-adapter-admin',
			MCP_ADAPTER_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			MCP_ADAPTER_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'mcp-adapter-admin',
			MCP_ADAPTER_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			MCP_ADAPTER_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'mcp-adapter-admin',
			'mcpAdapterAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mcp_adapter_admin' ),
			)
		);
	}

	/**
	 * Render main admin page.
	 */
	public static function render_main_page() {
		$plugin = MCP_Adapter_Plugin::instance();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( ! $plugin->is_loaded() ) : ?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'The plugin is not fully loaded. Please check the errors above.', 'mcp-adapter-plugin' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-success">
					<p><?php esc_html_e( 'MCP Adapter Plugin is active and ready.', 'mcp-adapter-plugin' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="mcp-adapter-dashboard">
				<div class="mcp-adapter-card">
					<h2><?php esc_html_e( 'About MCP Adapter', 'mcp-adapter-plugin' ); ?></h2>
					<p>
						<?php
						esc_html_e(
							'The MCP Adapter Plugin integrates the WordPress Abilities API with the Model Context Protocol (MCP), enabling WordPress capabilities to be exposed to AI agents.',
							'mcp-adapter-plugin'
						);
						?>
					</p>
					<p>
						<strong><?php esc_html_e( 'Components:', 'mcp-adapter-plugin' ); ?></strong>
					</p>
					<ul>
						<li>
							<strong><?php esc_html_e( 'Abilities API:', 'mcp-adapter-plugin' ); ?></strong>
							<?php esc_html_e( 'Provides a standardized way to register WordPress capabilities.', 'mcp-adapter-plugin' ); ?>
							<?php if ( $plugin->is_loaded() ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: green;"></span>
							<?php else : ?>
								<span class="dashicons dashicons-dismiss" style="color: red;"></span>
							<?php endif; ?>
						</li>
						<li>
							<strong><?php esc_html_e( 'MCP Adapter:', 'mcp-adapter-plugin' ); ?></strong>
							<?php esc_html_e( 'Converts abilities into MCP tools, resources, and prompts.', 'mcp-adapter-plugin' ); ?>
							<?php if ( $plugin->is_loaded() ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: green;"></span>
							<?php else : ?>
								<span class="dashicons dashicons-dismiss" style="color: red;"></span>
							<?php endif; ?>
						</li>
					</ul>
				</div>

				<div class="mcp-adapter-card">
					<h2><?php esc_html_e( 'Quick Stats', 'mcp-adapter-plugin' ); ?></h2>
					<?php
					$abilities_count = 0;
					$servers_count   = 0;

					if ( $plugin->is_loaded() ) {
						if ( function_exists( 'wp_get_abilities' ) ) {
							$abilities = wp_get_abilities();
							$abilities_count = count( $abilities );
						}

						$adapter = \WP\MCP\Core\McpAdapter::instance();
						$servers = $adapter->get_servers();
						$servers_count = count( $servers );
					}
					?>
					<p>
						<strong><?php esc_html_e( 'Registered Abilities:', 'mcp-adapter-plugin' ); ?></strong>
						<?php echo esc_html( $abilities_count ); ?>
					</p>
					<p>
						<strong><?php esc_html_e( 'MCP Servers:', 'mcp-adapter-plugin' ); ?></strong>
						<?php echo esc_html( $servers_count ); ?>
					</p>
				</div>

				<div class="mcp-adapter-card">
					<h2><?php esc_html_e( 'Documentation', 'mcp-adapter-plugin' ); ?></h2>
					<p><?php esc_html_e( 'Learn how to use the MCP Adapter:', 'mcp-adapter-plugin' ); ?></p>
					<ul>
						<li><a href="<?php echo esc_url( MCP_ADAPTER_PLUGIN_DIR . 'abilities-api-repo/docs/1.intro.md' ); ?>" target="_blank"><?php esc_html_e( 'Abilities API Introduction', 'mcp-adapter-plugin' ); ?></a></li>
						<li><a href="<?php echo esc_url( MCP_ADAPTER_PLUGIN_DIR . 'abilities-api-repo/docs/3.registering-abilities.md' ); ?>" target="_blank"><?php esc_html_e( 'Registering Abilities', 'mcp-adapter-plugin' ); ?></a></li>
						<li><a href="<?php echo esc_url( MCP_ADAPTER_PLUGIN_DIR . 'mcp-adapter-repo/docs/README.md' ); ?>" target="_blank"><?php esc_html_e( 'MCP Adapter Documentation', 'mcp-adapter-plugin' ); ?></a></li>
						<li><a href="<?php echo esc_url( MCP_ADAPTER_PLUGIN_DIR . 'mcp-adapter-repo/docs/getting-started/basic-examples.md' ); ?>" target="_blank"><?php esc_html_e( 'Basic Examples', 'mcp-adapter-plugin' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render abilities page.
	 */
	public static function render_abilities_page() {
		$plugin = MCP_Adapter_Plugin::instance();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( ! $plugin->is_loaded() || ! function_exists( 'wp_get_abilities' ) ) : ?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'Abilities API is not available.', 'mcp-adapter-plugin' ); ?></p>
				</div>
			<?php else : ?>
				<?php
				$abilities = wp_get_abilities();
				?>
				<p>
					<?php
					printf(
						/* translators: %d: number of abilities */
						esc_html( _n( '%d ability registered', '%d abilities registered', count( $abilities ), 'mcp-adapter-plugin' ) ),
						count( $abilities )
					);
					?>
				</p>

				<?php if ( empty( $abilities ) ) : ?>
					<div class="notice notice-info">
						<p><?php esc_html_e( 'No abilities are currently registered. Enable example abilities in the settings to see examples.', 'mcp-adapter-plugin' ); ?></p>
					</div>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Ability ID', 'mcp-adapter-plugin' ); ?></th>
								<th><?php esc_html_e( 'Label', 'mcp-adapter-plugin' ); ?></th>
								<th><?php esc_html_e( 'Description', 'mcp-adapter-plugin' ); ?></th>
								<th><?php esc_html_e( 'Has Permission Callback', 'mcp-adapter-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $abilities as $ability ) : ?>
								<tr>
									<td><code><?php echo esc_html( $ability->get_id() ); ?></code></td>
									<td><?php echo esc_html( $ability->get_label() ); ?></td>
									<td><?php echo esc_html( wp_trim_words( $ability->get_description(), 20 ) ); ?></td>
									<td>
										<?php if ( $ability->has_permission_callback() ) : ?>
											<span class="dashicons dashicons-yes-alt" style="color: green;"></span>
										<?php else : ?>
											<span class="dashicons dashicons-minus" style="color: gray;"></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render servers page.
	 */
	public static function render_servers_page() {
		$plugin = MCP_Adapter_Plugin::instance();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( ! $plugin->is_loaded() ) : ?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'MCP Adapter is not available.', 'mcp-adapter-plugin' ); ?></p>
				</div>
			<?php else : ?>
				<?php
				$adapter = \WP\MCP\Core\McpAdapter::instance();
				$servers = $adapter->get_servers();
				?>
				<p>
					<?php
					printf(
						/* translators: %d: number of servers */
						esc_html( _n( '%d MCP server configured', '%d MCP servers configured', count( $servers ), 'mcp-adapter-plugin' ) ),
						count( $servers )
					);
					?>
				</p>

				<?php if ( empty( $servers ) ) : ?>
					<div class="notice notice-info">
						<p><?php esc_html_e( 'No MCP servers are currently configured. Enable example servers in the settings to see examples.', 'mcp-adapter-plugin' ); ?></p>
					</div>
				<?php else : ?>
					<?php foreach ( $servers as $server ) : ?>
						<div class="mcp-adapter-card">
							<h2><?php echo esc_html( $server->get_server_name() ); ?></h2>
							<p><strong><?php esc_html_e( 'ID:', 'mcp-adapter-plugin' ); ?></strong> <code><?php echo esc_html( $server->get_server_id() ); ?></code></p>
							<p><strong><?php esc_html_e( 'Description:', 'mcp-adapter-plugin' ); ?></strong> <?php echo esc_html( $server->get_server_description() ); ?></p>
							<p><strong><?php esc_html_e( 'Version:', 'mcp-adapter-plugin' ); ?></strong> <?php echo esc_html( $server->get_server_version() ); ?></p>
							<p><strong><?php esc_html_e( 'Endpoint:', 'mcp-adapter-plugin' ); ?></strong> <code><?php echo esc_url( rest_url( $server->get_server_route_namespace() . '/' . $server->get_server_route() ) ); ?></code></p>
							<p>
								<strong><?php esc_html_e( 'Tools:', 'mcp-adapter-plugin' ); ?></strong>
								<?php echo esc_html( count( $server->get_tools() ) ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( 'Resources:', 'mcp-adapter-plugin' ); ?></strong>
								<?php echo esc_html( count( $server->get_resources() ) ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( 'Prompts:', 'mcp-adapter-plugin' ); ?></strong>
								<?php echo esc_html( count( $server->get_prompts() ) ); ?>
							</p>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 */
	public static function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'mcp_adapter_settings' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="mcp_adapter_enable_examples">
								<?php esc_html_e( 'Enable Example Abilities', 'mcp-adapter-plugin' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="mcp_adapter_enable_examples" name="mcp_adapter_enable_examples" value="1" <?php checked( get_option( 'mcp_adapter_enable_examples' ), 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Register example abilities for demonstration purposes.', 'mcp-adapter-plugin' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="mcp_adapter_enable_example_servers">
								<?php esc_html_e( 'Enable Example Servers', 'mcp-adapter-plugin' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="mcp_adapter_enable_example_servers" name="mcp_adapter_enable_example_servers" value="1" <?php checked( get_option( 'mcp_adapter_enable_example_servers' ), 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Create example MCP servers for demonstration purposes.', 'mcp-adapter-plugin' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="mcp_adapter_debug_mode">
								<?php esc_html_e( 'Debug Mode', 'mcp-adapter-plugin' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="mcp_adapter_debug_mode" name="mcp_adapter_debug_mode" value="1" <?php checked( get_option( 'mcp_adapter_debug_mode' ), 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Enable detailed logging for debugging.', 'mcp-adapter-plugin' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

// Initialize admin interface
MCP_Adapter_Admin::init();
