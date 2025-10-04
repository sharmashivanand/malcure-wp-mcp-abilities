<?php
/**
 * Main Plugin Class
 *
 * @package mcp-adapter-plugin
 */

/**
 * Main plugin class for MCP Adapter Plugin.
 *
 * Coordinates the loading and initialization of both the Abilities API
 * and MCP Adapter components.
 */
class MCP_Adapter_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var MCP_Adapter_Plugin
	 */
	private static $instance = null;

	/**
	 * Whether the Abilities API is loaded.
	 *
	 * @var bool
	 */
	private $abilities_api_loaded = false;

	/**
	 * Whether the MCP Adapter is loaded.
	 *
	 * @var bool
	 */
	private $mcp_adapter_loaded = false;

	/**
	 * Errors encountered during loading.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Get plugin instance.
	 *
	 * @return MCP_Adapter_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load plugin dependencies.
	 */
	private function load_dependencies() {
		// Load Abilities API
		$this->load_abilities_api();

		// Load MCP Adapter (only if Abilities API is loaded)
		if ( $this->abilities_api_loaded ) {
			$this->load_mcp_adapter();
		}

		// Display admin notices if there are errors
		if ( ! empty( $this->errors ) ) {
			add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
		}
	}

	/**
	 * Load the Abilities API.
	 */
	private function load_abilities_api() {
		$abilities_api_file = MCP_ADAPTER_PLUGIN_DIR . 'abilities-api-repo/includes/bootstrap.php';

		if ( ! file_exists( $abilities_api_file ) ) {
			$this->errors[] = sprintf(
				/* translators: %s: file path */
				__( 'Abilities API not found at: %s', 'mcp-adapter-plugin' ),
				$abilities_api_file
			);
			return;
		}

		// Define the Abilities API directory constant if not already defined
		if ( ! defined( 'WP_ABILITIES_API_DIR' ) ) {
			define( 'WP_ABILITIES_API_DIR', MCP_ADAPTER_PLUGIN_DIR . 'abilities-api-repo/' );
		}

		// Load the Abilities API bootstrap
		require_once $abilities_api_file;

		// Check if the API loaded successfully
		if ( function_exists( 'wp_register_ability' ) ) {
			$this->abilities_api_loaded = true;
		} else {
			$this->errors[] = __( 'Abilities API failed to load properly.', 'mcp-adapter-plugin' );
		}
	}

	/**
	 * Load the MCP Adapter.
	 */
	private function load_mcp_adapter() {
		// Check for Jetpack Autoloader
		$autoloader_file = MCP_ADAPTER_PLUGIN_DIR . 'mcp-adapter-repo/vendor/autoload_packages.php';

		if ( ! file_exists( $autoloader_file ) ) {
			$this->errors[] = sprintf(
				/* translators: %s: file path */
				__( 'MCP Adapter autoloader not found at: %s. Please run "composer install" in the mcp-adapter-repo directory.', 'mcp-adapter-plugin' ),
				$autoloader_file
			);
			return;
		}

		// Load the Jetpack Autoloader
		require_once $autoloader_file;

		// Check if the MCP Adapter loaded successfully
		if ( class_exists( 'WP\MCP\Core\McpAdapter' ) ) {
			$this->mcp_adapter_loaded = true;
		} else {
			$this->errors[] = __( 'MCP Adapter failed to load. The required classes are not available.', 'mcp-adapter-plugin' );
		}
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Only proceed if both components are loaded
		if ( ! $this->abilities_api_loaded || ! $this->mcp_adapter_loaded ) {
			return;
		}

		// Force initialization of the Abilities API registry
		// This triggers the 'abilities_api_init' action
		if ( class_exists( 'WP_Abilities_Registry' ) ) {
			WP_Abilities_Registry::get_instance();
		}

		// Initialize the MCP Adapter now that the registry is ready
		$this->init_mcp_adapter();

		// Add example abilities and servers (can be removed in production)
		add_action( 'mcp_adapter_plugin_example_setup', array( $this, 'register_example_abilities' ) );
		add_action( 'mcp_adapter_init', array( $this, 'register_example_servers' ) );

		// Load additional includes
		$this->load_includes();
	}

	/**
	 * Load additional plugin includes.
	 */
	private function load_includes() {
		// Load admin interface
		if ( is_admin() ) {
			require_once MCP_ADAPTER_PLUGIN_DIR . 'includes/admin/class-mcp-adapter-admin.php';
		}

		// Load example abilities if enabled
		$load_examples = apply_filters( 'mcp_adapter_plugin_load_examples', get_option( 'mcp_adapter_enable_examples', false ) );
		if ( $load_examples ) {
			require_once MCP_ADAPTER_PLUGIN_DIR . 'includes/examples/example-abilities.php';
		}
	}

	/**
	 * Initialize the MCP Adapter.
	 */
	public function init_mcp_adapter() {
		// Get the MCP Adapter instance
		$adapter = \WP\MCP\Core\McpAdapter::instance();

		/**
		 * Fires after the MCP Adapter is initialized.
		 *
		 * This is the main hook for registering MCP servers.
		 *
		 * @param \WP\MCP\Core\McpAdapter $adapter The MCP Adapter instance.
		 */
		do_action( 'mcp_adapter_init', $adapter );
	}

	/**
	 * Register example abilities for demonstration.
	 */
	public function register_example_abilities() {
		// This is called from the example-abilities.php file
		// See includes/examples/example-abilities.php
	}

	/**
	 * Register example MCP servers for demonstration.
	 *
	 * @param \WP\MCP\Core\McpAdapter $adapter The MCP Adapter instance.
	 */
	public function register_example_servers( $adapter ) {
		// Only register examples if explicitly enabled
		$register_examples = apply_filters( 'mcp_adapter_plugin_register_example_servers', get_option( 'mcp_adapter_enable_example_servers', false ) );
		if ( ! $register_examples ) {
			return;
		}

		// Example: Create a basic MCP server
		try {
			$adapter->create_server(
				'example-server',
				'mcp-adapter-plugin',
				'mcp',
				'Example MCP Server',
				'Demonstration server for the MCP Adapter Plugin',
				'1.0.0',
				array( \WP\MCP\Transport\Http\RestTransport::class ),
				\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
				null, // Observability handler
				array( 'mcp-adapter-plugin/site-info' ), // Tools
				array(), // Resources
				array(), // Prompts
				function() { return true; } // Allow all access for example server (insecure - for demo only)
			);
		} catch ( \Exception $e ) {
			error_log( 'Failed to create example MCP server: ' . $e->getMessage() );
		}
	}

	/**
	 * Display admin notices for errors.
	 */
	public function display_admin_notices() {
		foreach ( $this->errors as $error ) {
			printf(
				'<div class="notice notice-error"><p><strong>%s:</strong> %s</p></div>',
				esc_html__( 'MCP Adapter Plugin Error', 'mcp-adapter-plugin' ),
				esc_html( $error )
			);
		}
	}

	/**
	 * Check if the plugin is fully loaded.
	 *
	 * @return bool
	 */
	public function is_loaded() {
		return $this->abilities_api_loaded && $this->mcp_adapter_loaded;
	}

	/**
	 * Get plugin errors.
	 *
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}
}
