# Installation Guide

Complete guide for installing and configuring the MCP Adapter Plugin.

## Prerequisites

Before installing, ensure your environment meets these requirements:

- **WordPress**: Version 6.8 or higher
- **PHP**: Version 8.1 or higher
- **Composer**: For managing MCP Adapter dependencies
- **Git**: For checking out the required repositories

## Step 1: Download the Plugin

### Option A: Git Clone

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone <your-plugin-repo-url> mcp-adapter
cd mcp-adapter
```

### Option B: Manual Download

1. Download the plugin as a ZIP file
2. Extract to `wp-content/plugins/mcp-adapter/`

## Step 2: Checkout Required Repositories

The plugin requires two repositories to be present in its directory:

```bash
cd /path/to/wordpress/wp-content/plugins/mcp-adapter/

# Clone abilities-api repository
git clone https://github.com/WordPress/abilities-api abilities-api-repo

# Clone mcp-adapter repository  
git clone https://github.com/WordPress/mcp-adapter mcp-adapter-repo
```

**Important**: Do not modify these repositories! They are managed separately and changes will be overwritten.

## Step 3: Install MCP Adapter Dependencies

The MCP Adapter requires Composer dependencies:

```bash
cd mcp-adapter-repo
composer install --no-dev
cd ..
```

For development (includes testing tools):

```bash
cd mcp-adapter-repo
composer install
cd ..
```

## Step 4: Verify Directory Structure

Your plugin directory should look like this:

```
wp-content/plugins/mcp-adapter/
├── mcp-adapter-plugin.php
├── README.md
├── includes/
│   ├── class-mcp-adapter-plugin.php
│   ├── admin/
│   └── examples/
├── assets/
│   ├── css/
│   └── js/
├── abilities-api-repo/           ← Checked out
│   ├── abilities-api.php
│   ├── includes/
│   └── docs/
└── mcp-adapter-repo/              ← Checked out
    ├── composer.json
    ├── vendor/                    ← Composer installed
    ├── src/
    └── docs/
```

## Step 5: Activate the Plugin

### Via WordPress Admin

1. Log in to WordPress admin
2. Navigate to **Plugins** > **Installed Plugins**
3. Find "MCP Adapter Plugin"
4. Click **Activate**

### Via WP-CLI

```bash
wp plugin activate mcp-adapter-plugin
```

## Step 6: Verify Installation

1. Go to **WordPress Admin** > **MCP Adapter**
2. Check the dashboard for:
   - ✅ Green checkmark for "Abilities API" 
   - ✅ Green checkmark for "MCP Adapter"
3. If you see red X marks, check the error messages

## Step 7: Configure Settings (Optional)

1. Navigate to **MCP Adapter** > **Settings**
2. Enable example features if desired:
   - ☑️ **Enable Example Abilities**: Registers demo abilities
   - ☑️ **Enable Example Servers**: Creates demo MCP servers
   - ☑️ **Debug Mode**: Enables detailed logging
3. Click **Save Changes**

## Step 8: Verify Functionality

### Test Abilities Registration

1. Go to **MCP Adapter** > **Abilities**
2. If examples are enabled, you should see:
   - `mcp-adapter-plugin/site-info`
   - `mcp-adapter-plugin/create-draft`
   - `mcp-adapter-plugin/content-strategy`

### Test MCP Servers

1. Go to **MCP Adapter** > **MCP Servers**
2. If example servers are enabled, you should see:
   - Server: "Example MCP Server"
   - Endpoint URL displayed

### Test REST API

If example servers are enabled:

```bash
# Test the MCP endpoint
curl -X POST "https://yoursite.com/wp-json/mcp-adapter-plugin/mcp" \
  -H "Content-Type: application/json" \
  -d '{"method": "tools/list"}'
```

Expected response: List of available tools

## Troubleshooting

### Issue: Plugin won't activate

**Error**: "MCP Adapter Plugin requires PHP 8.1 or higher"

**Solution**: Upgrade your PHP version to 8.1 or higher

**Error**: "MCP Adapter Plugin requires WordPress 6.8 or higher"

**Solution**: Update WordPress to version 6.8+

### Issue: Red X for Abilities API

**Error**: "Abilities API not found at: ..."

**Solution**: 
```bash
cd /path/to/plugins/mcp-adapter/
git clone https://github.com/WordPress/abilities-api abilities-api-repo
```

### Issue: Red X for MCP Adapter

**Error**: "MCP Adapter autoloader not found"

**Solution**:
```bash
cd /path/to/plugins/mcp-adapter/mcp-adapter-repo
composer install --no-dev
```

**Error**: "MCP Adapter failed to load"

**Solution**: Clear PHP opcache and regenerate autoloader:
```bash
cd mcp-adapter-repo
composer dump-autoload
```

### Issue: REST API returns 404

**Solution**: Flush rewrite rules:
```bash
wp rewrite flush
```

Or via WordPress admin:
1. Go to **Settings** > **Permalinks**
2. Click **Save Changes** (no changes needed)

### Issue: Permission errors when testing

**Solution**: Ensure you're authenticated. Most abilities require user permissions.

For WP-CLI testing:
```bash
wp --user=admin rest ...
```

## Uninstallation

To completely remove the plugin:

1. Deactivate via **Plugins** > **Installed Plugins**
2. Click **Delete** on the plugin
3. Or manually remove:
   ```bash
   rm -rf wp-content/plugins/mcp-adapter/
   ```

## Next Steps

- Read the [Developer Guide](DEVELOPER.md) for creating custom abilities
- Review [Abilities API Documentation](abilities-api-repo/docs/)
- Review [MCP Adapter Documentation](mcp-adapter-repo/docs/)
- Check out [Example Abilities](includes/examples/example-abilities.php)

## Getting Help

- **Issues**: File bugs on GitHub
- **Documentation**: Check the `/docs` folders in both repositories
- **WordPress Slack**: #core-ai channel
- **Community**: WordPress.org support forums

## Security Notes

1. The plugin exposes WordPress functionality via REST API
2. All abilities respect WordPress permission callbacks
3. Authentication is required for most operations
4. Review ability permissions before exposing to external systems
5. Consider using application passwords for AI agent authentication
6. Monitor REST API logs for unusual activity

## Performance Considerations

- The plugin uses Jetpack Autoloader for efficient class loading
- REST API responses are JSON formatted
- Consider caching strategies for frequently accessed resources
- Monitor server resources when exposing many abilities
- Use the observability features to track performance

## Production Checklist

Before deploying to production:

- [ ] Disable example abilities and servers
- [ ] Review all registered abilities for security
- [ ] Configure proper permission callbacks
- [ ] Set up monitoring and logging
- [ ] Test authentication mechanisms
- [ ] Review REST API rate limiting
- [ ] Document your custom abilities
- [ ] Plan for updates to sub-repositories
- [ ] Set up automated backups
- [ ] Configure error tracking
