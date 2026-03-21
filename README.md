# MVP Docs

A minimum viable documentation plugin for WordPress. Lightweight, native, no bloat.

## What it does

Adds a Docs post type with categories, a card-grid archive, search, markdown import, and import/export — using the native block editor and standard WordPress data structures.

## Features

- Docs post type with configurable archive at `/docs/`
- Doc categories with drag-and-drop ordering
- AJAX search with typeahead dropdown and dedicated results page
- Markdown import (`.md` to Gutenberg blocks via marked.js)
- Import/export docs, categories, and settings as JSON
- Configurable colors, layout, slugs, and page titles
- Block theme and classic theme support
- Zero scripts or styles outside doc pages

## Requirements

- WordPress 6.7+
- PHP 8.0+

## Installation

1. Download or clone this repo into `wp-content/plugins/mvp-docs/`
2. Activate the plugin
3. Visit **Settings > Permalinks** and click Save
4. Create docs under the **MVP Docs** menu

## License

GPLv2 or later
