# MCP Adapter Plugin

A WordPress plugin that integrates the WordPress Abilities API with the Model Context Protocol (MCP) to expose WordPress capabilities to AI agents.

## 📚 Documentation

- **[Quick Start Guide](docs/QUICKSTART.md)** - Get up and running quickly
- **[Installation Guide](docs/INSTALLATION.md)** - Detailed installation instructions
- **[Developer Guide](docs/DEVELOPER.md)** - Extending and customizing the plugin
- **[Visual Guide](docs/README_VISUAL.md)** - Visual documentation and diagrams
- **[Project Summary](docs/PROJECT_SUMMARY.md)** - Architecture and design overview
- **[Analysis](docs/ANALYSIS.md)** - Technical analysis and implementation details
- **[Recent Fixes](docs/FIXES.md)** - Latest bug fixes and improvements

## Overview

This plugin serves as a wrapper around two core components:

1. **Abilities API** (`abilities-api-repo/`): Provides a standardized way to register WordPress capabilities
2. **MCP Adapter** (`mcp-adapter-repo/`): Converts abilities into MCP tools, resources, and prompts

## Features

- **Unified Plugin Interface**: Single plugin installation that manages both Abilities API and MCP Adapter
- **Admin Dashboard**: WordPress admin interface for managing abilities and MCP servers
- **Example Abilities**: Optional example abilities demonstrating tools, resources, and prompts
- **REST API Endpoints**: Automatic REST API integration for MCP communication
- **Extensible Architecture**: Easy to extend with custom abilities and servers

## Requirements

- WordPress 6.8 or higher
- PHP 8.1 or higher
- Composer (for MCP Adapter dependencies)

## Installation

1. Clone or download this plugin to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins
   git clone <repository-url> mcp-adapter
   ```

2. Checkout the required submodules/repositories:
   ```bash
   cd mcp-adapter
   # The abilities-api-repo and mcp-adapter-repo should already be present
   ```

3. Install MCP Adapter dependencies:
   ```bash
   cd mcp-adapter-repo
   composer install
   cd ..
   ```

4. Activate the plugin in WordPress admin

## Structure

```
mcp-adapter/
├── mcp-adapter-plugin.php          # Main plugin file
├── README.md                        # This file
├── CHANGELOG.md                     # Version history
├── docs/                            # Documentation
│   ├── QUICKSTART.md               # Quick start guide
│   ├── INSTALLATION.md             # Installation instructions
│   ├── DEVELOPER.md                # Developer guide
│   ├── README_VISUAL.md            # Visual documentation
│   ├── PROJECT_SUMMARY.md          # Project architecture
│   ├── ANALYSIS.md                 # Technical analysis
│   └── FIXES.md                    # Recent bug fixes
├── includes/                        # Plugin core files
│   ├── class-mcp-adapter-plugin.php # Main plugin class
│   ├── admin/                       # Admin interface
│   │   └── class-mcp-adapter-admin.php
│   └── examples/                    # Example abilities
│       └── example-abilities.php
├── assets/                          # CSS and JavaScript
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── abilities-api-repo/              # Abilities API (do not modify)
│   ├── abilities-api.php
│   ├── includes/
│   └── docs/
└── mcp-adapter-repo/                # MCP Adapter library (do not modify)
    ├── composer.json
    ├── src/
    ├── tests/
    └── docs/
```

## Usage

### Basic Setup

1. **Activate the Plugin**
   - Go to WordPress Admin > Plugins
   - Activate "MCP Adapter Plugin"

2. **Access the Dashboard**
   - Navigate to WordPress Admin > MCP Adapter
   - View registered abilities and MCP servers

3. **Enable Examples** (Optional)
   - Go to MCP Adapter > Settings
   - Enable "Example Abilities" and "Example Servers"
   - Save settings

### Registering Custom Abilities

Create abilities in your theme or plugin:

```php
add_action( 'abilities_api_init', function() {
    wp_register_ability( 'my-plugin/custom-ability', array(
        'label' => __( 'Custom Ability', 'my-plugin' ),
        'description' => __( 'Does something useful', 'my-plugin' ),
        'input_schema' => array(
            'type' => 'object',
            'properties' => array(
                'param' => array(
                    'type' => 'string',
                    'description' => 'A parameter'
                )
            ),
            'required' => array( 'param' )
        ),
        'output_schema' => array(
            'type' => 'object',
            'properties' => array(
                'result' => array( 'type' => 'string' )
            )
        ),
        'execute_callback' => function( $input ) {
            return array( 'result' => 'Success: ' . $input['param'] );
        },
        'permission_callback' => function() {
            return current_user_can( 'edit_posts' );
        }
    ));
});
```

### Creating MCP Servers

Expose abilities through MCP servers:

```php
add_action( 'mcp_adapter_init', function( $adapter ) {
    $adapter->create_server(
        'my-server',                // Server ID
        'my-plugin',                // Namespace
        'mcp',                      // Route
        'My MCP Server',            // Name
        'Server description',       // Description
        '1.0.0',                    // Version
        array(                      // Transports
            \WP\MCP\Transport\Http\RestTransport::class
        ),
        \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
        array( 'my-plugin/custom-ability' ), // Tools
        array(),                    // Resources
        array()                     // Prompts
    );
});
```

### Accessing MCP Endpoints

Once configured, MCP servers are accessible via REST API:

```bash
# List available tools
curl -X POST "https://yoursite.com/wp-json/my-plugin/mcp" \
  -H "Content-Type: application/json" \
  -d '{"method": "tools/list"}'

# Call a tool
curl -X POST "https://yoursite.com/wp-json/my-plugin/mcp" \
  -H "Content-Type: application/json" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "my-plugin--custom-ability",
      "arguments": {"param": "value"}
    }
  }'
```

## Development

### Filters

```php
// Disable example abilities
add_filter( 'mcp_adapter_plugin_load_examples', '__return_false' );

// Disable example servers
add_filter( 'mcp_adapter_plugin_register_example_servers', '__return_false' );

// Enable example abilities
add_filter( 'mcp_adapter_plugin_register_example_abilities', '__return_true' );
```

### Actions

```php
// Plugin activation
add_action( 'mcp_adapter_plugin_activate', function() {
    // Your activation code
});

// Plugin deactivation
add_action( 'mcp_adapter_plugin_deactivate', function() {
    // Your deactivation code
});

// After MCP Adapter initialization
add_action( 'mcp_adapter_init', function( $adapter ) {
    // Register your servers
});

// After Abilities API initialization
add_action( 'abilities_api_init', function() {
    // Register your abilities
});
```

## Documentation

- **Abilities API Documentation**: See `abilities-api-repo/docs/`
  - [Introduction](abilities-api-repo/docs/1.intro.md)
  - [Registering Abilities](abilities-api-repo/docs/3.registering-abilities.md)

- **MCP Adapter Documentation**: See `mcp-adapter-repo/docs/`
  - [Getting Started](mcp-adapter-repo/docs/getting-started/README.md)
  - [Basic Examples](mcp-adapter-repo/docs/getting-started/basic-examples.md)
  - [Architecture Overview](mcp-adapter-repo/docs/architecture/overview.md)

## Troubleshooting

### Plugin won't activate

- Check PHP version (requires 8.1+)
- Check WordPress version (requires 6.8+)
- Ensure composer dependencies are installed in `mcp-adapter-repo/`

### Abilities not showing up

- Verify the Abilities API is loaded (check admin dashboard)
- Check that `abilities_api_init` action is firing
- Look for PHP errors in debug log

### MCP endpoints returning errors

- Verify MCP Adapter is loaded (check admin dashboard)
- Check REST API is accessible
- Verify user permissions
- Enable debug mode in settings

### Composer autoloader issues

If you see class not found errors:

```bash
cd mcp-adapter-repo
composer dump-autoload
```

## Contributing

This plugin wraps two separate repositories:
- **Do not modify** `abilities-api-repo/` - changes will be overwritten
- **Do not modify** `mcp-adapter-repo/` - changes will be overwritten

To contribute:
- Plugin wrapper improvements: Contribute to this plugin
- Abilities API improvements: Contribute to https://github.com/WordPress/abilities-api
- MCP Adapter improvements: Contribute to https://github.com/WordPress/mcp-adapter

## License

GPL-2.0-or-later

## Credits

Part of the **AI Building Blocks for WordPress** initiative.

- Abilities API: WordPress.org Contributors
- MCP Adapter: WordPress.org Contributors
- Plugin Wrapper: Your contributions here
