# Developer Guide

Guide for developers extending the MCP Adapter Plugin with custom abilities and servers.

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Creating Custom Abilities](#creating-custom-abilities)
- [Registering MCP Servers](#registering-mcp-servers)
- [Understanding Component Types](#understanding-component-types)
- [Best Practices](#best-practices)
- [Advanced Topics](#advanced-topics)
- [Testing](#testing)

## Architecture Overview

The plugin integrates three layers:

```
┌─────────────────────────────────────────┐
│   MCP Adapter Plugin (Wrapper)          │
│   - Admin Interface                      │
│   - Configuration Management             │
│   - WordPress Integration                │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│   MCP Adapter (Library)                  │
│   - Converts Abilities → MCP Components  │
│   - Transport Layer (REST, Streaming)    │
│   - Error Handling & Observability       │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│   Abilities API (Framework)              │
│   - Ability Registration & Discovery     │
│   - Input/Output Validation              │
│   - Permission Management                │
│   - Execution Layer                      │
└─────────────────────────────────────────┘
```

## Creating Custom Abilities

### Basic Ability Structure

Abilities are registered on the `abilities_api_init` hook:

```php
add_action( 'abilities_api_init', 'my_plugin_register_abilities' );

function my_plugin_register_abilities() {
    wp_register_ability( 'my-plugin/ability-name', array(
        'label'              => __( 'Human Readable Name', 'my-plugin' ),
        'description'        => __( 'Detailed description for AI agents', 'my-plugin' ),
        'input_schema'       => array( /* JSON Schema */ ),
        'output_schema'      => array( /* JSON Schema */ ),
        'execute_callback'   => 'my_plugin_execute_ability',
        'permission_callback' => 'my_plugin_check_permission',
        'meta'               => array( /* Optional metadata */ ),
    ));
}
```

### Input Schema Definition

Define expected parameters using JSON Schema:

```php
'input_schema' => array(
    'type' => 'object',
    'properties' => array(
        'post_id' => array(
            'type' => 'integer',
            'description' => 'The ID of the post to update',
            'minimum' => 1,
        ),
        'title' => array(
            'type' => 'string',
            'description' => 'New post title',
            'minLength' => 1,
            'maxLength' => 200,
        ),
        'status' => array(
            'type' => 'string',
            'description' => 'Post status',
            'enum' => array( 'draft', 'publish', 'pending' ),
            'default' => 'draft',
        ),
    ),
    'required' => array( 'post_id' ),
    'additionalProperties' => false,
),
```

### Output Schema Definition

Define the response structure:

```php
'output_schema' => array(
    'type' => 'object',
    'properties' => array(
        'success' => array(
            'type' => 'boolean',
            'description' => 'Whether the operation succeeded',
        ),
        'post_id' => array(
            'type' => 'integer',
            'description' => 'The post ID',
        ),
        'post_url' => array(
            'type' => 'string',
            'format' => 'uri',
            'description' => 'URL to the post',
        ),
        'message' => array(
            'type' => 'string',
            'description' => 'Status message',
        ),
    ),
    'required' => array( 'success' ),
),
```

### Execute Callback

Implement the ability's logic:

```php
function my_plugin_execute_ability( $input ) {
    // Input is already validated against input_schema
    $post_id = $input['post_id'];
    $title   = $input['title'] ?? null;
    $status  = $input['status'] ?? 'draft';
    
    // Perform the operation
    $post = get_post( $post_id );
    if ( ! $post ) {
        throw new Exception( 'Post not found' );
    }
    
    $update_data = array( 'ID' => $post_id );
    if ( $title ) {
        $update_data['post_title'] = sanitize_text_field( $title );
    }
    if ( $status ) {
        $update_data['post_status'] = $status;
    }
    
    $result = wp_update_post( $update_data, true );
    
    if ( is_wp_error( $result ) ) {
        throw new Exception( $result->get_error_message() );
    }
    
    // Return data matching output_schema
    return array(
        'success'  => true,
        'post_id'  => $post_id,
        'post_url' => get_permalink( $post_id ),
        'message'  => 'Post updated successfully',
    );
}
```

### Permission Callback

Control who can execute the ability:

```php
function my_plugin_check_permission( $input ) {
    // Check general permission
    if ( ! current_user_can( 'edit_posts' ) ) {
        return false;
    }
    
    // Check specific permission for this post
    if ( isset( $input['post_id'] ) ) {
        if ( ! current_user_can( 'edit_post', $input['post_id'] ) ) {
            return new WP_Error(
                'forbidden',
                'You do not have permission to edit this post'
            );
        }
    }
    
    return true;
}
```

## Registering MCP Servers

### Basic Server Registration

Register servers on the `mcp_adapter_init` hook:

```php
add_action( 'mcp_adapter_init', 'my_plugin_register_mcp_servers' );

function my_plugin_register_mcp_servers( $adapter ) {
    $adapter->create_server(
        'my-server-id',           // Unique server ID
        'my-plugin',              // REST API namespace
        'mcp',                    // REST API route
        'My MCP Server',          // Server name
        'Server description',     // Description
        '1.0.0',                  // Version
        array(                    // Transport classes
            \WP\MCP\Transport\Http\RestTransport::class,
        ),
        \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
        array(                    // Tools (ability IDs)
            'my-plugin/update-post',
            'my-plugin/create-post',
        ),
        array(                    // Resources (ability IDs)
            'my-plugin/site-stats',
        ),
        array(                    // Prompts (ability IDs)
            'my-plugin/seo-advice',
        )
    );
}
```

This creates a REST endpoint at:
```
https://yoursite.com/wp-json/my-plugin/mcp
```

### Multiple Servers

You can create multiple servers for different purposes:

```php
function my_plugin_register_mcp_servers( $adapter ) {
    // Public server - limited abilities
    $adapter->create_server(
        'public-server',
        'my-plugin',
        'public',
        'Public API Server',
        'Public-facing API with limited functionality',
        '1.0.0',
        array( \WP\MCP\Transport\Http\RestTransport::class ),
        \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
        array( 'my-plugin/site-info' ),
        array(),
        array()
    );
    
    // Admin server - full access
    $adapter->create_server(
        'admin-server',
        'my-plugin',
        'admin',
        'Admin API Server',
        'Full administrative access',
        '1.0.0',
        array( \WP\MCP\Transport\Http\RestTransport::class ),
        \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
        array(
            'my-plugin/create-post',
            'my-plugin/update-post',
            'my-plugin/delete-post',
        ),
        array( 'my-plugin/full-stats' ),
        array( 'my-plugin/admin-guidance' )
    );
}
```

## Understanding Component Types

### Tools (Interactive Actions)

Use Tools for operations that:
- Modify data or state
- Require dynamic input parameters
- Perform actions with side effects
- Execute business logic

**Example**: Create post, update settings, send email

```php
wp_register_ability( 'my-plugin/send-email', array(
    'label' => 'Send Email',
    'description' => 'Sends an email to specified recipients',
    'input_schema' => array(
        'type' => 'object',
        'properties' => array(
            'to' => array( 'type' => 'string', 'format' => 'email' ),
            'subject' => array( 'type' => 'string' ),
            'body' => array( 'type' => 'string' ),
        ),
        'required' => array( 'to', 'subject', 'body' ),
    ),
    'output_schema' => array(
        'type' => 'object',
        'properties' => array(
            'sent' => array( 'type' => 'boolean' ),
            'message_id' => array( 'type' => 'string' ),
        ),
    ),
    'execute_callback' => function( $input ) {
        $sent = wp_mail( $input['to'], $input['subject'], $input['body'] );
        return array(
            'sent' => $sent,
            'message_id' => wp_generate_uuid4(),
        );
    },
    'permission_callback' => function() {
        return current_user_can( 'publish_posts' );
    },
));
```

### Resources (Data Access)

Use Resources for:
- Read-only or semi-static data
- System information
- Configuration data
- Contextual information

**Example**: Site info, user profile, system status

```php
wp_register_ability( 'my-plugin/user-profile', array(
    'label' => 'User Profile',
    'description' => 'Retrieves current user profile information',
    'input_schema' => array(
        'type' => 'object',
        'properties' => array(
            'include_meta' => array(
                'type' => 'boolean',
                'default' => false,
            ),
        ),
    ),
    'output_schema' => array(
        'type' => 'object',
        'properties' => array(
            'id' => array( 'type' => 'integer' ),
            'username' => array( 'type' => 'string' ),
            'email' => array( 'type' => 'string' ),
            'display_name' => array( 'type' => 'string' ),
            'roles' => array( 'type' => 'array' ),
        ),
    ),
    'execute_callback' => function( $input ) {
        $user = wp_get_current_user();
        return array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles' => $user->roles,
        );
    },
    'permission_callback' => function() {
        return is_user_logged_in();
    },
));
```

### Prompts (Guidance Generation)

Use Prompts for:
- Advisory content
- Analysis and recommendations
- Structured guidance
- Templated responses

**Example**: SEO recommendations, content strategy, security audit

```php
wp_register_ability( 'my-plugin/security-audit', array(
    'label' => 'Security Audit',
    'description' => 'Generates security recommendations for the site',
    'input_schema' => array(
        'type' => 'object',
        'properties' => array(
            'depth' => array(
                'type' => 'string',
                'enum' => array( 'basic', 'detailed' ),
                'default' => 'basic',
            ),
        ),
    ),
    'output_schema' => array(
        'type' => 'object',
        'properties' => array(
            'summary' => array( 'type' => 'string' ),
            'issues' => array(
                'type' => 'array',
                'items' => array(
                    'type' => 'object',
                    'properties' => array(
                        'severity' => array( 'type' => 'string' ),
                        'issue' => array( 'type' => 'string' ),
                        'recommendation' => array( 'type' => 'string' ),
                    ),
                ),
            ),
        ),
    ),
    'execute_callback' => function( $input ) {
        $issues = array();
        
        // Check SSL
        if ( ! is_ssl() ) {
            $issues[] = array(
                'severity' => 'high',
                'issue' => 'Site not using HTTPS',
                'recommendation' => 'Enable SSL certificate',
            );
        }
        
        // Check WordPress version
        global $wp_version;
        // ... more checks
        
        return array(
            'summary' => sprintf( 'Found %d security issues', count( $issues ) ),
            'issues' => $issues,
        );
    },
    'permission_callback' => function() {
        return current_user_can( 'manage_options' );
    },
));
```

## Best Practices

### 1. Naming Conventions

```php
// Use namespace/ability-name format
'my-plugin/create-post'     // ✅ Good
'createPost'                 // ❌ Bad
'my_plugin_create_post'      // ❌ Bad

// Be descriptive
'my-plugin/send-welcome-email'  // ✅ Good
'my-plugin/email'               // ❌ Too vague
```

### 2. Input Validation

```php
// Leverage JSON Schema validation
'input_schema' => array(
    'type' => 'object',
    'properties' => array(
        'email' => array(
            'type' => 'string',
            'format' => 'email',  // Built-in validation
        ),
        'age' => array(
            'type' => 'integer',
            'minimum' => 18,      // Range validation
            'maximum' => 120,
        ),
    ),
    'required' => array( 'email' ),  // Required fields
),
```

### 3. Error Handling

```php
'execute_callback' => function( $input ) {
    try {
        // Validate business logic
        if ( empty( $input['post_id'] ) ) {
            throw new InvalidArgumentException( 'Post ID is required' );
        }
        
        // Perform operation
        $result = wp_update_post( $data );
        
        if ( is_wp_error( $result ) ) {
            throw new Exception( $result->get_error_message() );
        }
        
        return $result;
        
    } catch ( InvalidArgumentException $e ) {
        // Client errors - input validation
        throw $e;
    } catch ( Exception $e ) {
        // Server errors - log and sanitize
        error_log( 'Ability error: ' . $e->getMessage() );
        throw new Exception( 'Operation failed. Please try again.' );
    }
},
```

### 4. Permission Callbacks

```php
// Simple permission check
'permission_callback' => function() {
    return current_user_can( 'edit_posts' );
},

// Context-aware permission check
'permission_callback' => function( $input ) {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return false;
    }
    
    if ( isset( $input['post_id'] ) ) {
        return current_user_can( 'edit_post', $input['post_id'] );
    }
    
    return true;
},

// Detailed error messages
'permission_callback' => function( $input ) {
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'not_logged_in', 'Authentication required' );
    }
    
    if ( ! current_user_can( 'edit_posts' ) ) {
        return new WP_Error( 'insufficient_permissions', 'You cannot edit posts' );
    }
    
    return true;
},
```

### 5. Documentation

```php
wp_register_ability( 'my-plugin/complex-operation', array(
    'label' => 'Complex Operation',
    
    // Detailed description for AI agents
    'description' => 'Performs a complex multi-step operation. ' .
                     'First validates the input data, then processes ' .
                     'the request in batches, and finally returns a ' .
                     'summary of the results. This operation may take ' .
                     'several seconds for large datasets.',
    
    // Documented parameters
    'input_schema' => array(
        'type' => 'object',
        'properties' => array(
            'data' => array(
                'type' => 'array',
                'description' => 'Array of items to process. Each item ' .
                                'should have a "name" and "value" property.',
                'items' => array(
                    'type' => 'object',
                    'properties' => array(
                        'name' => array(
                            'type' => 'string',
                            'description' => 'Unique identifier for the item',
                        ),
                        'value' => array(
                            'type' => 'number',
                            'description' => 'Numeric value between 0 and 100',
                        ),
                    ),
                ),
            ),
        ),
    ),
    
    // ... rest of ability
));
```

## Advanced Topics

### Custom Error Handlers

```php
class My_Custom_Error_Handler implements \WP\MCP\Infrastructure\ErrorHandling\Contracts\McpErrorHandlerInterface {
    public function log( string $message, array $context = array(), string $type = 'error' ): void {
        // Send to external logging service
        $this->send_to_logging_service( $message, $context, $type );
        
        // Also log locally
        error_log( sprintf(
            '[MCP %s] %s - Context: %s',
            strtoupper( $type ),
            $message,
            json_encode( $context )
        ) );
    }
    
    private function send_to_logging_service( $message, $context, $type ) {
        // Implementation for external service
    }
}

// Use in server registration
$adapter->create_server(
    'my-server',
    'my-plugin',
    'mcp',
    'My Server',
    'Description',
    '1.0.0',
    array( \WP\MCP\Transport\Http\RestTransport::class ),
    'My_Custom_Error_Handler',  // Your custom handler
    $tools,
    $resources,
    $prompts
);
```

### Custom Transport

See `mcp-adapter-repo/docs/guides/custom-transports.md` for detailed information.

### Conditional Ability Registration

```php
add_action( 'abilities_api_init', function() {
    // Only register if WooCommerce is active
    if ( class_exists( 'WooCommerce' ) ) {
        wp_register_ability( 'my-plugin/woo-stats', array(
            // WooCommerce-specific ability
        ));
    }
    
    // Only register for specific users
    if ( current_user_can( 'manage_options' ) ) {
        wp_register_ability( 'my-plugin/admin-tool', array(
            // Admin-only ability
        ));
    }
    
    // Only register in production
    if ( defined( 'WP_ENV' ) && 'production' === WP_ENV ) {
        wp_register_ability( 'my-plugin/prod-only', array(
            // Production-only ability
        ));
    }
});
```

## Testing

### Manual Testing via REST API

```bash
# List tools
curl -X POST "https://yoursite.com/wp-json/my-plugin/mcp" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"method": "tools/list"}'

# Call a tool
curl -X POST "https://yoursite.com/wp-json/my-plugin/mcp" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "my-plugin--ability-name",
      "arguments": {"param": "value"}
    }
  }'
```

### WP-CLI Testing

```bash
# Test ability directly
wp eval 'var_dump(wp_get_ability("my-plugin/ability-name"));'

# Execute ability
wp eval '$ability = wp_get_ability("my-plugin/ability-name"); var_dump($ability->execute(["param" => "value"]));'
```

### PHPUnit Testing

See `mcp-adapter-repo/tests/` for examples of unit and integration tests.

## Next Steps

- Review [Example Abilities](includes/examples/example-abilities.php)
- Read [MCP Adapter Documentation](mcp-adapter-repo/docs/)
- Read [Abilities API Documentation](abilities-api-repo/docs/)
- Join WordPress #core-ai Slack channel

## Resources

- [JSON Schema Reference](https://json-schema.org/understanding-json-schema/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Model Context Protocol Specification](https://spec.modelcontextprotocol.io/)
