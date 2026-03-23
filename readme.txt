=== MVP Docs ===
Contributors: cartpauj
Tags: documentation, docs, knowledge base, markdown, ai
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.4
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

**Markdown Import — Built for AI-Generated Docs**

AI tools produce clean, well-structured Markdown. MVP Docs lets you import those `.md` files directly into the block editor with one click. The sidebar includes an "Import from Markdown" button — upload a file and it gets parsed client-side with full GitHub Flavored Markdown support (via marked.js), then converted into native Gutenberg blocks. The first `# Heading` becomes the post title automatically.

Use your favorite AI to generate docs, how-to guides, or knowledge base articles in Markdown, then import them into WordPress without any reformatting.

Supported: headings, bold, italic, links, images, tables, fenced code blocks with syntax hints, task lists, strikethrough, blockquotes, horizontal rules, ordered and unordered lists.

**Import / Export**

Go to Docs > Settings > Import / Export to:

* **Export** docs, categories, and/or settings as a single JSON file
* **Import** a previously exported file to restore or migrate content between sites

Existing docs with the same title are skipped during import to avoid duplicates. Categories are created automatically if they don't exist.

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
