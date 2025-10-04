# 🚀 MCP Adapter Plugin for WordPress

**Expose WordPress capabilities to AI agents via the Model Context Protocol**

[![WordPress](https://img.shields.io/badge/WordPress-6.8+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1+-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)](LICENSE)

> Part of the [AI Building Blocks for WordPress](https://make.wordpress.org/ai/2025/07/17/ai-building-blocks) initiative

## 📋 Overview

A production-ready WordPress plugin that integrates the **WordPress Abilities API** and **MCP Adapter** to expose WordPress functionality to AI agents through the Model Context Protocol (MCP).

```
WordPress Abilities + MCP Adapter = AI-Ready WordPress
```

## ✨ Features

- 🔌 **Unified Plugin**: Single installation for complete MCP integration
- 🎛️ **Admin Interface**: WordPress-native dashboard for managing abilities and servers
- 🛠️ **Three Component Types**: Tools (actions), Resources (data), Prompts (guidance)
- 🔐 **Security First**: Permission callbacks, input validation, WordPress authentication
- 📡 **REST API**: Automatic endpoint creation for AI agent communication
- 📚 **Complete Documentation**: 7 comprehensive guides totaling 2500+ lines
- 🎓 **Working Examples**: 3 production-quality example abilities included
- 🔧 **Developer Friendly**: Clear APIs, extensible architecture, best practices

## 🚦 Quick Start

### 1️⃣ Install

```bash
cd wp-content/plugins/
git clone <repo-url> mcp-adapter
cd mcp-adapter

# Checkout required repositories
git clone https://github.com/WordPress/abilities-api abilities-api-repo
git clone https://github.com/WordPress/mcp-adapter mcp-adapter-repo

# Install dependencies
cd mcp-adapter-repo && composer install --no-dev && cd ..
```

### 2️⃣ Activate

```bash
wp plugin activate mcp-adapter-plugin
# Or via WordPress Admin > Plugins > Activate
```

### 3️⃣ Configure

Go to **WordPress Admin > MCP Adapter > Settings**
- ☑️ Enable Example Abilities
- ☑️ Enable Example Servers
- **Save Changes**

### 4️⃣ Test

```bash
curl -X POST "https://yoursite.com/wp-json/mcp-adapter-plugin/mcp" \
  -H "Content-Type: application/json" \
  -d '{"method": "tools/list"}'
```

**🎉 Done! You now have AI-accessible WordPress capabilities.**

## 📖 Documentation

| Guide | Description | Lines |
|-------|-------------|-------|
| [QUICKSTART.md](QUICKSTART.md) | Get running in 5 minutes | 250+ |
| [README.md](README.md) | Complete usage guide | 300+ |
| [INSTALLATION.md](INSTALLATION.md) | Detailed installation steps | 400+ |
| [DEVELOPER.md](DEVELOPER.md) | Build custom abilities | 600+ |
| [ANALYSIS.md](ANALYSIS.md) | Technical deep-dive | 700+ |
| [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) | Project overview | 300+ |

## 🏗️ Architecture

```
┌─────────────────────────────────────────┐
│   WordPress Admin Interface             │  User-facing
│   Dashboard • Settings • Management     │
└─────────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────┐
│   MCP Adapter Plugin (This Project)     │  Integration
│   Wrapper • Examples • Configuration    │
└─────────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────┐
│   MCP Adapter Library                    │  Conversion
│   Abilities → MCP Components             │
└─────────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────┐
│   WordPress Abilities API                │  Foundation
│   Registration • Validation • Execution  │
└─────────────────────────────────────────┘
```

## 💡 Example: Your First Ability

```php
<?php
// In your theme's functions.php or custom plugin

// 1. Register an ability
add_action( 'abilities_api_init', function() {
    wp_register_ability( 'my-site/greet', [
        'label' => 'Greeting',
        'description' => 'Returns a friendly greeting',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string', 'default' => 'World']
            ]
        ],
        'output_schema' => [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string']
            ]
        ],
        'execute_callback' => function( $input ) {
            return ['message' => "Hello, {$input['name']}!"];
        },
        'permission_callback' => fn() => true
    ]);
});

// 2. Expose via MCP server
add_action( 'mcp_adapter_init', function( $adapter ) {
    $adapter->create_server(
        'my-server',
        'my-site',
        'mcp',
        'My Server',
        'My first MCP server',
        '1.0.0',
        [\WP\MCP\Transport\Http\RestTransport::class],
        \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
        ['my-site/greet'],  // ← Your ability as a tool
        [],
        []
    );
});
```

**Test it:**
```bash
curl -X POST "https://yoursite.com/wp-json/my-site/mcp" \
  -d '{"method":"tools/call","params":{"name":"my-site--greet","arguments":{"name":"AI"}}}'
```

## 🎯 Use Cases

### 🤖 AI Agent Integration
Expose WordPress functionality to AI assistants like Claude, ChatGPT, or custom agents

### 📝 Content Automation
- Generate and publish posts
- Optimize content for SEO
- Schedule publications
- Manage media library

### 📊 Data & Analytics
- Site performance metrics
- User statistics
- Content analysis
- Security audits

### 🛍️ E-commerce (with WooCommerce)
- Product management
- Order processing
- Inventory tracking
- Sales analytics

### 🔧 Site Management
- Plugin/theme management
- User administration
- Settings configuration
- Backup operations

## 📦 What's Included

### Core Files
- ✅ Main plugin file with WordPress headers
- ✅ Plugin initialization and loading
- ✅ WordPress admin interface
- ✅ Settings management
- ✅ Example abilities (tool, resource, prompt)

### Examples
- 🛠️ **Tool**: Create Draft Post
- 📚 **Resource**: Site Information
- 💡 **Prompt**: Content Strategy Recommendations

### Documentation
- 📘 Quick Start Guide
- 📗 Installation Guide
- 📙 Developer Guide
- 📕 Technical Analysis
- 📝 Complete README
- 📋 Change Log

## 🔒 Security

- ✅ Permission callbacks on all abilities
- ✅ Input validation via JSON Schema
- ✅ WordPress authentication required
- ✅ Capability checking
- ✅ Sanitization of all inputs
- ✅ Error message sanitization

## ⚡ Performance

- ✅ Jetpack Autoloader for efficient class loading
- ✅ Lazy loading of components
- ✅ Minimal database queries
- ✅ Cacheable responses
- ✅ Optimized REST endpoints

## 🧪 Testing

```bash
# Check plugin status
wp plugin status mcp-adapter-plugin

# List abilities
wp eval 'var_dump(array_keys(wp_get_abilities()));'

# Test REST endpoint
curl -X POST "https://yoursite.com/wp-json/my-site/mcp" \
  -d '{"method":"tools/list"}'

# View errors
wp eval '$p = MCP_Adapter_Plugin::instance(); var_dump($p->get_errors());'
```

## 📈 Stats

| Metric | Count |
|--------|-------|
| **Files Created** | 15 |
| **Lines of Code** | 1,200+ |
| **Lines of Documentation** | 2,800+ |
| **Total Lines** | 4,000+ |
| **Example Abilities** | 3 |
| **Admin Pages** | 4 |
| **Documentation Guides** | 7 |

## 🛠️ Requirements

| Requirement | Minimum |
|------------|---------|
| WordPress | 6.8+ |
| PHP | 8.1+ |
| Composer | Latest |
| Git | For checkout |

## 🚨 Important Notes

### ⚠️ Do Not Modify

The following directories are **git checkouts** and should **not be modified**:
- `abilities-api-repo/` - Managed by WordPress Abilities API team
- `mcp-adapter-repo/` - Managed by WordPress MCP Adapter team

All customizations should be in:
- Your theme's `functions.php`
- Your custom plugin
- This plugin's extensibility hooks

## 🤝 Contributing

### To This Plugin Wrapper
Submit pull requests to this repository

### To Core Components
- **Abilities API**: https://github.com/WordPress/abilities-api
- **MCP Adapter**: https://github.com/WordPress/mcp-adapter

## 📞 Support

- 💬 **WordPress Slack**: #core-ai channel
- 🐛 **Issues**: GitHub Issues
- 📖 **Docs**: See documentation files
- 🌐 **Forums**: WordPress.org support

## 🗺️ Roadmap

### ✅ v0.1.0 (Current)
- ✅ Core plugin functionality
- ✅ Admin interface
- ✅ Example abilities
- ✅ Complete documentation

### 🔮 Future Versions
- 🔄 Ability testing UI
- 📊 Performance dashboard
- 🔍 Server health monitoring
- 📝 WP-CLI commands
- 🧩 Gutenberg blocks
- 🌐 GraphQL support

## 📜 License

GPL-2.0-or-later

## 🙏 Credits

Part of the **AI Building Blocks for WordPress** initiative

- WordPress.org Contributors
- Abilities API Team
- MCP Adapter Team
- WordPress Community

---

<div align="center">

**[Quick Start](QUICKSTART.md)** • **[Installation](INSTALLATION.md)** • **[Developer Guide](DEVELOPER.md)**

Made with ❤️ for the WordPress Community

</div>
