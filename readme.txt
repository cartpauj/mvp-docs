=== MVP Docs ===
Contributors: cartpauj
Tags: documentation, docs, knowledge base, markdown, ai
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight documentation plugin for WordPress. AI-friendly markdown import, native editor, no bloat.

== Description ==

MVP Docs adds a simple documentation system to your WordPress site. It creates a Docs custom post type with categories, a clean archive page, and a markdown import tool — nothing more.

**Why MVP Docs?**

AI tools like Claude and ChatGPT are excellent at generating documentation in Markdown format. MVP Docs was built to take advantage of that — paste your AI-generated `.md` files straight into WordPress with a single click. No copy-pasting HTML, no reformatting, no fussing with blocks. Just import and publish.

Beyond the AI workflow, most documentation plugins are overbuilt. They add custom page builders, proprietary editors, dozens of database tables, and megabytes of JavaScript. MVP Docs takes the opposite approach:

* Uses the native WordPress block editor
* Works with any theme (block themes and classic themes)
* Stores content as standard WordPress posts
* Loads zero scripts or styles outside of doc pages
* No lock-in — your content is just posts and taxonomies
* No external dependencies — everything ships with the plugin

**Features**

* **Docs post type** with archive at `/docs/`
* **Doc Categories** for organizing content
* **Search** — AJAX-powered typeahead dropdown on the archive and category pages, with a dedicated search results page at `/docs/search/`
* **Markdown import** — upload a `.md` file and it converts to native Gutenberg blocks (tables, code blocks, lists, and all GFM features)
* **Import / Export** — export docs, categories, and settings as a JSON file; import on another site
* **Archive page** — docs grouped by category in a card grid
* **Category archives** — full list of docs in a category with search bar
* **Search results page** — dedicated page with breadcrumbs and themed card layout
* **Breadcrumbs** on single doc pages
* **Settings** — archive layout (columns, border radius, docs per category), colors, page titles, sort order, custom slugs, category ordering via drag-and-drop
* **Block theme support** — registers proper block templates for single docs, archives, category pages, and search
* **Classic theme support** — falls back to PHP templates with `get_header()`/`get_footer()`
* **WP-CLI** — full command-line coverage so an AI agent or shell script can configure and populate a docs site without ever opening wp-admin

**Markdown Import — Built for AI-Generated Docs**

AI tools produce clean, well-structured Markdown. MVP Docs lets you import those `.md` files directly into the block editor with one click. The sidebar includes an "Import from Markdown" button — upload a file and it gets parsed client-side with full GitHub Flavored Markdown support (via marked.js), then converted into native Gutenberg blocks. The first `# Heading` becomes the post title automatically.

Use your favorite AI to generate docs, how-to guides, or knowledge base articles in Markdown, then import them into WordPress without any reformatting.

Supported: headings, bold, italic, links, images, tables, fenced code blocks with syntax hints, task lists, strikethrough, blockquotes, horizontal rules, ordered and unordered lists.

**Import / Export**

Go to Docs > Settings > Import / Export to:

* **Export** docs, categories, and/or settings as a single JSON file
* **Import** a previously exported file to restore or migrate content between sites

Existing docs with the same title are skipped during import to avoid duplicates. Categories are created automatically if they don't exist.

**WP-CLI**

Every admin action has a command-line equivalent, so a docs site can be stood up and populated entirely from a shell script or AI agent. Run `wp mvp-docs <command> --help` for full details on any command below.

*Content*

`wp mvp-docs import-md <file>`
Import a Markdown file as a doc. Output is byte-identical to the sidebar Markdown importer — same blocks, same title extraction from the first `#` heading.

`wp mvp-docs import-raw <file>`
Import a raw HTML file as a doc. Block-commented HTML is preserved as-is; plain HTML renders as a Classic block in the editor.

Both import commands accept:
* `--title=<text>` — override the extracted title
* `--slug=<slug>` — explicit post slug
* `--excerpt=<text>` — short description shown on category/search pages
* `--category=<slug>` — assign to this category (created if missing)
* `--sort-order=<n>` — in-category sort priority (lower appears first)
* `--status=<publish|draft|private>` — post status (default: publish)
* `--dry-run` — print the generated markup instead of creating a post

*Backup and migration*

`wp mvp-docs export [--docs] [--settings] [--output=<file>] [--pretty]`
Dump docs, categories, settings, and category order to a single JSON bundle. Omitting both flags exports everything. Pipe to stdout or write to a file.

`wp mvp-docs import <file>`
Restore a JSON bundle. Docs are deduplicated by title, so running the same import twice is safe.

*Structure*

`wp mvp-docs reorder-categories <slug-or-id>...`
Set the display order of categories. Accepts term IDs or slugs.

*Settings*

`wp mvp-docs settings list [--format=table|json|yaml|csv]`
Show every setting with its current value, description, and allowed form — self-documenting so you never have to read the source to discover what's configurable.

`wp mvp-docs settings get <key>`
Print a single setting's value.

`wp mvp-docs settings set <key=value>...`
Update one or more settings. Values run through the same sanitizer as the admin UI; if a value is rejected (e.g. `columns=7` when the allowed range is 1–4), a warning names the allowed form and shows the actual saved value. Changing `docs_slug` or `category_slug` auto-flushes rewrite rules.

*Quick start for AI agents*

    wp plugin activate mvp-docs
    wp mvp-docs settings set docs_slug=kb archive_title="Knowledge Base"
    wp term create mvpd_category "Getting Started" --slug=getting-started
    wp mvp-docs reorder-categories getting-started
    wp mvp-docs import-md ./intro.md --category=getting-started --sort-order=1
    wp mvp-docs export --output=backup.json

== Installation ==

1. Go to Plugins > Add New and search for "MVP Docs"
2. Click Install Now, then Activate
3. Go to Settings > Permalinks and click Save (to register the URL structure)
4. Start creating docs under the new Docs menu

== Frequently Asked Questions ==

= Does it work with my theme? =

Yes. MVP Docs auto-detects whether you're using a block theme or a classic theme and uses the appropriate template system. The archive styling is minimal and designed to work within your theme's layout.

= Can I import existing markdown files? =

Yes. When editing a doc, open the sidebar and click "Import from Markdown" under Doc Settings. Select a `.md` file and it will be converted to native Gutenberg blocks.

= Can I migrate docs between sites? =

Yes. Use the Import / Export tab under Docs > Settings to export your docs, categories, and settings as a JSON file, then import it on another site.

= Does it add scripts or styles to every page? =

No. CSS and JS are only loaded on doc pages — the archive, category pages, search results, and single docs. Nothing is enqueued globally.

= Can I change the URL structure? =

Yes. Go to Docs > Settings > Permalinks to change the docs slug and category slug.

= Can I script setup for an AI agent or CI pipeline? =

Yes. MVP Docs ships a complete WP-CLI command set — activate the plugin, configure settings, create categories, and import Markdown or HTML docs all from a shell script. See the "WP-CLI" section above and run `wp mvp-docs <command> --help` for detailed options.

= What happens if I deactivate the plugin? =

Your content stays. Docs are standard WordPress posts — they remain in your database and can be accessed via the admin even without the plugin active.

== Screenshots ==

1. Docs archive page with category cards and search bar
2. Search results page
3. Single doc with breadcrumbs
4. Settings — Design tab
5. Settings — Category Order (drag and drop)
6. Settings — Permalinks
7. Settings — Import / Export
8. Markdown import in the block editor sidebar

== Changelog ==
= 1.1.1 =
* Fixed `/docs/search/` 404 on fresh activation — search rewrite rule is now registered alongside the post type so activation's flush captures it (no manual permalink save needed)

= 1.1.0 =
* Added full WP-CLI command set (`wp mvp-docs import-md|import-raw|export|import|reorder-categories|settings`) — configure and populate a docs site without touching wp-admin
* Markdown CLI import produces byte-identical output to the sidebar importer
* `wp mvp-docs settings list` now shows description and allowed-value form for every setting; `settings set` warns when a value is rejected and explains what's allowed
* Slug changes via CLI auto-flush rewrite rules
* Unified head typography and spacing across archive, category, and single doc views
* Added `.mvpd-page-header`, `.mvpd-page-title`, `.mvpd-page-subtitle` classes to header shortcode output so themes can't override plugin spacing
* Search button icon now uses `--mvpd-card-bg` for reliable contrast on dark accent colors
* Breadcrumbs now use the plugin's link color (with explicit `:visited` handling to beat classic themes)

= 1.0.7 =
* First vs published to WP.org

= 1.0.6 =
* Fixed a couple of non-translatable strings

= 1.0.5 =
* Rename custom post type from mvp_doc to mvpd_doc for unique prefix compliance
* Add nonce verification to AJAX search endpoint
* Sanitize all input values including validated whitelist fields
* Escape inline CSS output with wp_strip_all_tags()
* Add input type validation for JSON import data
* Fix subtitle settings field stretching full width

= 1.0.4 =
* Update readme's
* Changed metabox title

= 1.0.3 =
* Fix docs archive not filling full width in classic themes with flex layouts
* Fix search input placeholder text overlapping the search icon

= 1.0.2 =
* Fix duplicate plugin/author URI
* Derive MVPD_VERSION from plugin header

= 1.0.1 =
* Update author URI

= 1.0.0 =
* Initial release
