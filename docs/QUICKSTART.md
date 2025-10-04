# Quick Start Guide

Get the MCP Adapter Plugin up and running in 5 minutes.

## Prerequisites

- WordPress 6.8+ installed
- PHP 8.1+ available
- Terminal access to your server
- Composer installed (for MCP Adapter dependencies)

## Installation (5 minutes)

### 1. Navigate to plugins directory

```bash
cd /path/to/wordpress/wp-content/plugins/
```

### 2. Download the plugin

```bash
# If you have the plugin files
cd mcp-adapter

# Or clone from repository
# git clone <repository-url> mcp-adapter
# cd mcp-adapter
```

### 3. Checkout required repositories

```bash
# Clone Abilities API
git clone https://github.com/WordPress/abilities-api abilities-api-repo

# Clone MCP Adapter
git clone https://github.com/WordPress/mcp-adapter mcp-adapter-repo
```

### 4. Install dependencies

```bash
cd mcp-adapter-repo
composer install --no-dev
cd ..
```

### 5. Activate the plugin

```bash
# Via WP-CLI
wp plugin activate mcp-adapter-plugin

# Or via WordPress Admin
# Navigate to Plugins > Activate "MCP Adapter Plugin"
```

## Quick Configuration (2 minutes)

### 1. Access the Dashboard

Go to: **WordPress Admin > MCP Adapter**

You should see:
- ✅ Abilities API loaded
- ✅ MCP Adapter loaded

### 2. Enable Examples (Optional)

Go to: **WordPress Admin > MCP Adapter > Settings**

Check:
- ☑️ Enable Example Abilities
- ☑️ Enable Example Servers

Click **Save Changes**

### 3. Verify Examples

Go to: **WordPress Admin > MCP Adapter > Abilities**

You should see 3 abilities:
- `mcp-adapter-plugin/site-info`
- `mcp-adapter-plugin/create-draft`
- `mcp-adapter-plugin/content-strategy`

Go to: **WordPress Admin > MCP Adapter > MCP Servers**

You should see:
- Server: "Example MCP Server"
- Endpoint: `https://yoursite.com/wp-json/mcp-adapter-plugin/mcp`

## First API Call (1 minute)

Test the REST API endpoint:

```bash
# List available tools
curl -X POST "https://yoursite.com/wp-json/mcp-adapter-plugin/mcp" \
  -H "Content-Type: application/json" \
  -d '{"method": "tools/list"}'
```

**Expected Response:**
```json
{
  "tools": [
    {
      "name": "mcp-adapter-plugin--site-info",
      "description": "Retrieves comprehensive information about the WordPress site...",
      "inputSchema": { ... }
    },
    {
      "name": "mcp-adapter-plugin--create-draft",
      "description": "Creates a new draft post...",
      "inputSchema": { ... }
    }
  ]
}
```

## Create Your First Ability (5 minutes)

### 1. Create a plugin or use functions.php

In your theme's `functions.php` or a custom plugin:

```php
<?php
/**
 * Register a simple ability
 */
add_action( 'abilities_api_init', 'my_first_ability' );

function my_first_ability() {
    wp_register_ability( 'my-site/hello-world', array(
        'label'       => 'Hello World',
        'description' => 'Returns a friendly greeting',
        
        'input_schema' => array(
            'type' => 'object',
            'properties' => array(
                'name' => array(
                    'type' => 'string',
                    'description' => 'Your name',
                    'default' => 'World',
                ),
            ),
        ),
        
        'output_schema' => array(
            'type' => 'object',
            'properties' => array(
                'greeting' => array(
                    'type' => 'string',
                    'description' => 'The greeting message',
                ),
            ),
        ),
        
        'execute_callback' => function( $input ) {
            $name = $input['name'] ?? 'World';
            return array(
                'greeting' => "Hello, {$name}! Welcome to WordPress MCP.",
            );
        },
        
        'permission_callback' => function() {
            return true; // Public access
        },
    ));
}
```

### 2. Create an MCP Server

Add this to your `functions.php` or plugin:

```php
<?php
/**
 * Register an MCP server
 */
add_action( 'mcp_adapter_init', 'my_first_server' );

function my_first_server( $adapter ) {
    $adapter->create_server(
        'my-server',                  // Server ID
        'my-site',                    // REST namespace
        'mcp',                        // REST route
        'My First Server',            // Server name
        'My first MCP server',        // Description
        '1.0.0',                      // Version
        array( \WP\MCP\Transport\Http\RestTransport::class ),
        \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
        array( 'my-site/hello-world' ), // Tools
        array(),                      // Resources
        array()                       // Prompts
    );
}
```

### 3. Test Your Ability

```bash
# List tools - should include your new ability
curl -X POST "https://yoursite.com/wp-json/my-site/mcp" \
  -H "Content-Type: application/json" \
  -d '{"method": "tools/list"}'

# Call your ability
curl -X POST "https://yoursite.com/wp-json/my-site/mcp" \
  -H "Content-Type: application/json" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "my-site--hello-world",
      "arguments": {"name": "Developer"}
    }
  }'
```

**Expected Response:**
```json
{
  "content": [
    {
      "type": "text",
      "text": "{\"greeting\":\"Hello, Developer! Welcome to WordPress MCP.\"}"
    }
  ]
}
```

## Next Steps

### Learn More

1. **Read the Documentation**
   - [README.md](README.md) - Complete usage guide
   - [DEVELOPER.md](DEVELOPER.md) - In-depth developer guide
   - [INSTALLATION.md](INSTALLATION.md) - Detailed installation

2. **Explore Examples**
   - See `includes/examples/example-abilities.php`
   - Review the example server configuration
   - Try different ability types (tools, resources, prompts)

3. **Review Repository Docs**
   - Abilities API: `abilities-api-repo/docs/`
   - MCP Adapter: `mcp-adapter-repo/docs/`

### Build Something Cool

Ideas for your next abilities:

**Content Management**
- Create/update/delete posts
- Manage categories and tags
- Schedule posts
- Bulk operations

**Site Information**
- User statistics
- Plugin/theme information
- Performance metrics
- Security status

**E-commerce** (if using WooCommerce)
- Product management
- Order processing
- Inventory tracking
- Sales analytics

**SEO & Analytics**
- SEO recommendations
- Content analysis
- Keyword research
- Performance insights

**Custom Integrations**
- Third-party API connections
- Data synchronization
- Automated workflows
- Custom reporting

## Common Commands

```bash
# Check plugin status
wp plugin status mcp-adapter-plugin

# List all abilities
wp eval 'var_dump(array_keys(wp_get_abilities()));'

# Test REST API
wp rest GET /wp-json/my-site/mcp

# Flush rewrite rules (if endpoints not working)
wp rewrite flush

# Enable debug mode
wp option update mcp_adapter_debug_mode 1

# View plugin errors
wp eval '$p = MCP_Adapter_Plugin::instance(); var_dump($p->get_errors());'
```

## Troubleshooting

### Plugin won't activate
```bash
# Check PHP version
php -v

# Check WordPress version
wp core version
```

### Dependencies missing
```bash
cd wp-content/plugins/mcp-adapter/mcp-adapter-repo
composer install --no-dev
```

### REST endpoint returns 404
```bash
wp rewrite flush
```

### Need more help?
- Check [INSTALLATION.md](INSTALLATION.md) for detailed troubleshooting
- Review error logs: `wp-content/debug.log`
- Visit WordPress Admin > MCP Adapter for status

## Success! 🎉

You now have:
- ✅ A working MCP Adapter Plugin installation
- ✅ Example abilities to learn from
- ✅ Your first custom ability
- ✅ An MCP server exposing your abilities
- ✅ REST API endpoints for AI agent integration

**What's Next?**
- Create abilities for your specific use case
- Integrate with AI agents like Claude, ChatGPT, or custom agents
- Build automation workflows
- Share your abilities with the community

**Happy Building!** 🚀
