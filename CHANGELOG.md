# Changelog

All notable changes to the MCP Adapter Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2025-01-XX

### Added
- Initial plugin release
- WordPress admin interface for MCP Adapter management
- Integration with Abilities API repository
- Integration with MCP Adapter repository
- Admin dashboard showing:
  - Plugin status and component health
  - Registered abilities list
  - Configured MCP servers
  - Quick stats and metrics
- Example abilities demonstrating:
  - Tools (Create Draft Post)
  - Resources (Site Information)
  - Prompts (Content Strategy Recommendations)
- Settings page for plugin configuration:
  - Enable/disable example abilities
  - Enable/disable example servers
  - Debug mode toggle
- Complete documentation in README.md
- WordPress filters and actions for extensibility
- Activation/deactivation hooks with requirements checking
- Error handling and admin notices
- CSS and JavaScript for admin interface

### Technical Details
- Minimum PHP: 8.1
- Minimum WordPress: 6.8
- Loads Abilities API from `abilities-api-repo/`
- Loads MCP Adapter via Jetpack Autoloader from `mcp-adapter-repo/`
- Provides unified plugin wrapper without modifying source repositories

### Documentation
- Complete README.md with usage examples
- Inline code documentation
- Links to Abilities API and MCP Adapter documentation
- Troubleshooting guide

## [Unreleased]

### Planned Features
- Ability testing interface in admin
- Server status monitoring dashboard
- Import/export abilities configuration
- Advanced error logging and debugging tools
- Performance metrics dashboard
- Custom transport configuration UI
- Ability marketplace/sharing
- GraphQL endpoint support
- WebSocket transport option
