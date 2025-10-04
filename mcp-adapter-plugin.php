<?php
/**
 * MCP Adapter Plugin
 *
 * @package     mcp-adapter-plugin
 * @author      Malcure.com
 * @copyright   2025 Malcure.com
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       MCP Adapter Plugin
 * Plugin URI:        https://github.com/sharmashivanand/malcure-wp-mcp-abilities
 * Description:       Integrates WordPress Abilities API with Model Context Protocol (MCP) to expose WordPress capabilities to AI agents as tools, resources, and prompts.
 * Requires at least: 6.8
 * Version:           0.1.0
 * Requires PHP:      8.1
 * Author:            Malcure.com
 * Author URI:        https://github.com/sharmashivanand/malcure-wp-mcp-abilities/graphs/contributors
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mcp-adapter-plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version.
 */
define( 'MCP_ADAPTER_PLUGIN_VERSION', '0.1.0' );

/**
 * Plugin directory path.
 */
define( 'MCP_ADAPTER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'MCP_ADAPTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load the plugin.
 */
require_once MCP_ADAPTER_PLUGIN_DIR . 'includes/class-mcp-adapter-plugin.php';

/**
 * Initialize the plugin.
 */
function mcp_adapter_plugin_init() {
	MCP_Adapter_Plugin::instance();
}
add_action( 'plugins_loaded', 'mcp_adapter_plugin_init', 5 );

/**
 * Activation hook.
 */
function mcp_adapter_plugin_activate() {
	// Verify minimum requirements
	if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'MCP Adapter Plugin requires PHP 8.1 or higher.', 'mcp-adapter-plugin' ),
			esc_html__( 'Plugin Activation Error', 'mcp-adapter-plugin' ),
			array( 'back_link' => true )
		);
	}

	if ( version_compare( get_bloginfo( 'version' ), '6.8', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'MCP Adapter Plugin requires WordPress 6.8 or higher.', 'mcp-adapter-plugin' ),
			esc_html__( 'Plugin Activation Error', 'mcp-adapter-plugin' ),
			array( 'back_link' => true )
		);
	}

	// Trigger activation routine
	do_action( 'mcp_adapter_plugin_activate' );
}
register_activation_hook( __FILE__, 'mcp_adapter_plugin_activate' );

/**
 * Deactivation hook.
 */
function mcp_adapter_plugin_deactivate() {
	do_action( 'mcp_adapter_plugin_deactivate' );
}
register_deactivation_hook( __FILE__, 'mcp_adapter_plugin_deactivate' );
