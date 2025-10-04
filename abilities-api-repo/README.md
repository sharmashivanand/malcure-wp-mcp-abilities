# Abilities API

[_Part of the **AI Building Blocks for WordPress** initiative_
](https://make.wordpress.org/ai/2025/07/17/ai-building-blocks)

## Overview

- **Purpose:** provide a common way for WordPress core, plugins, and themes to describe what they can do ("abilities") in a machine‑readable, human‑friendly form.
- **Scope:** discovery, permissioning, and execution metadata only. Actual business logic stays inside the registering component.
- **Audience:** plugin & theme authors, agency builders, and future AI / automation tools.

## Design Goals

1. **Discoverability** - every ability can be listed, queried, and inspected.
2. **Interoperability** - a uniform schema lets unrelated components compose workflows.
3. **Security‑first** - explicit permissions determine who/what may invoke an ability.
4. **Gradual adoption** - ships first as a Composer package, migrates smoothly to core.

## Developer Documentation

- [Introduction](docs/1.intro.md)
- [Getting Started](docs/2.getting-started.md)
- [Registering Abilities](docs/3.registering-abilities.md)
- [Using Abilities](docs/4.using-abilities.md)
- [Contributing Guidelines](CONTRIBUTING.md)

## Inspiration

- **[wp‑feature‑api](https://github.com/automattic/wp-feature-api)** - shared vision of declaring capabilities at the PHP layer.
- Command Palette experiments in Gutenberg.
- Modern AI assistant protocols (MCP, A2A).

## Current Status

| Milestone                           | State       |
| ----------------------------------- |-------------|
| Placeholder repository              | **created** |
| Spec draft                          | in progress |
| Prototype plugin & Composer package | in progress |
| Community feedback (#core‑ai Slack) | planned     |
| Core proposal                       | planned     |

## How to Get Involved

- **Discuss:** `#core-ai` channel on WordPress Slack.
- **File issues:** suggestions & use‑cases welcome in this repo.
- **Prototype:** experiment with the upcoming Composer package once released.

## License

WordPress is free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE.md](LICENSE.md) for complete license.

<br/><br/><p align="center"><img src="https://s.w.org/style/images/codeispoetry.png?1" alt="Code is Poetry." /></p>
