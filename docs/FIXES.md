# MCP Adapter Plugin - Fixes Applied

## Issue
The REST API endpoint `/wp-json/mcp-adapter-plugin/mcp` was returning a 404 error:
```json
{"code":"rest_no_route","message":"No route was found matching the URL and request method.","data":{"status":404}}
```

## Root Causes

### 1. Lazy Initialization of Abilities API
The `WP_Abilities_Registry` class uses lazy initialization - the `abilities_api_init` action only fires when `WP_Abilities_Registry::get_instance()` is called for the first time. Since nothing was explicitly calling this function, the registry was never initialized.

### 2. Timing Issues with Hook Registration
The MCP Adapter initialization was hooked to `abilities_api_init`, but this action was never firing, so the MCP servers were never being registered.

### 3. Incorrect Function Signature
The `create_server()` method was being called with the wrong number of parameters - missing the `$observability_handler` parameter.

## Solutions Applied

### 1. Force Abilities API Initialization
**File**: `includes/class-mcp-adapter-plugin.php`

Added explicit initialization of the Abilities API registry in the `init_hooks()` method:

```php
// Force initialization of the Abilities API registry
// This triggers the 'abilities_api_init' action
if ( class_exists( 'WP_Abilities_Registry' ) ) {
    WP_Abilities_Registry::get_instance();
}
```

### 2. Direct MCP Adapter Initialization
Changed from hooking to `abilities_api_init` to directly calling the initialization:

```php
// Initialize the MCP Adapter now that the registry is ready
$this->init_mcp_adapter();
```

### 3. Fixed Function Signature
Updated the `register_example_servers()` method to include all required parameters:

```php
$adapter->create_server(
    'example-server',
    'mcp-adapter-plugin',
    'mcp',
    'Example MCP Server',
    'Demonstration server for the MCP Adapter Plugin',
    '1.0.0',
    array( \WP\MCP\Transport\Http\RestTransport::class ),
    \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
    null, // Observability handler (added)
    array( 'mcp-adapter-plugin/site-info' ), // Tools
    array(), // Resources
    array(), // Prompts
    function() { return true; } // Permission callback (added for demo purposes)
);
```

### 4. Public Access for Example Server
Added a permission callback that returns `true` to allow public access for testing purposes:

```php
function() { return true; } // Allow all access for example server (insecure - for demo only)
```

**⚠️ Security Note**: This is for demonstration purposes only. In production, implement proper authentication and authorization.

## Testing

### Enable Example Server
```bash
wp option update mcp_adapter_enable_example_servers 1
```

### Test Initialize Endpoint
```bash
curl -X POST "https://thepocketplane.com/wp-json/mcp-adapter-plugin/mcp" \
  -H "Content-Type: application/json" \
  -d '{"method": "initialize"}'
```

**Expected Response:**
```json
{
  "protocolVersion": "2025-06-18",
  "serverInfo": {
    "name": "Example MCP Server",
    "version": "1.0.0"
  },
  "capabilities": { ... }
}
```

### Test Tools List Endpoint
```bash
curl -X POST "https://thepocketplane.com/wp-json/mcp-adapter-plugin/mcp" \
  -H "Content-Type: application/json" \
  -d '{"method": "tools/list"}'
```

**Expected Response:**
```json
{
  "tools": []
}
```

Note: Tools array is empty because example abilities need to be enabled separately with:
```bash
wp option update mcp_adapter_enable_examples 1
```

## Status
✅ **FIXED** - The REST API endpoint is now working correctly and responding to MCP protocol requests.
