# MVP Docs

A lightweight documentation plugin for WordPress. AI-friendly markdown import, native editor, no bloat.

## Why MVP Docs?

AI tools like Claude and ChatGPT are great at generating documentation in Markdown format. MVP Docs was built to take advantage of that — import AI-generated `.md` files straight into WordPress with a single click. No copy-pasting HTML, no reformatting, no fussing with blocks. Just import and publish.

It's a lightweight MVP plugin designed to get you up and running with a documentation hub quickly, without the bloat of full-blown knowledge base plugins.

## Features

- **Markdown import** — one-click `.md` to Gutenberg blocks, perfect for AI-generated docs
- **Full WP-CLI coverage** — configure and populate a docs site from the command line, ideal for shell scripts and AI agents
- Docs post type with configurable archive at `/docs/`
- Doc categories with drag-and-drop ordering
- AJAX search with typeahead dropdown and dedicated results page
- Import/export docs, categories, and settings as JSON, or as a zip bundle that ships referenced images
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

## WP-CLI

Every admin action has a command-line equivalent. You can stand up a complete docs site — settings, categories, docs — without ever opening wp-admin. This makes the plugin a good fit for version-controlled setup scripts, CI pipelines, and AI agent workflows.

Run `wp mvp-docs <command> --help` for full option details on any command.

### Commands at a glance

| Command | Purpose |
|---|---|
| `wp mvp-docs import-md <file>` | Import a Markdown file as a doc (byte-identical to the sidebar importer) |
| `wp mvp-docs import-raw <file>` | Import a raw HTML file as a doc (block markup preserved if present) |
| `wp mvp-docs export [--with-images]` | Dump docs, categories, settings, and order to JSON; with `--with-images` produces a zip including referenced media |
| `wp mvp-docs import <file>` | Restore a `.json` or `.zip` bundle (auto-detected; sideloads images on zip; dedupes by title, safe to re-run) |
| `wp mvp-docs reorder-categories <refs>...` | Set category display order (IDs or slugs) |
| `wp mvp-docs settings list` | Show every setting with description and allowed form |
| `wp mvp-docs settings get <key>` | Read a single setting |
| `wp mvp-docs settings set <k=v>...` | Write settings (auto-flushes rewrites; warns on invalid values) |

### Importing content

Both `import-md` and `import-raw` accept the same flags:

```
--title=<text>         Override the extracted title
--slug=<slug>          Explicit post slug
--excerpt=<text>       Short description shown on category/search pages
--category=<slug>      Assign to this category (created if missing)
--sort-order=<n>       In-category sort priority (lower appears first)
--status=<status>      publish | draft | private (default: publish)
--dry-run              Print generated markup instead of creating a post
```

**Markdown** — `import-md` runs the file through the same conversion pipeline as the editor sidebar button. The first `# Heading` becomes the post title (unless `--title` is given). Output matches the sidebar importer byte-for-byte, so docs imported from the CLI are indistinguishable from docs imported in the editor.

```bash
wp mvp-docs import-md ./docs/intro.md --category=getting-started --sort-order=1
```

**HTML** — `import-raw` writes the file verbatim into `post_content`. If it contains Gutenberg block comments (`<!-- wp:paragraph -->`, etc.) they're preserved. If not, WordPress treats the content as an implicit Classic block in the editor.

```bash
wp mvp-docs import-raw ./docs/legacy.html --title="Legacy Guide"
```

### Export and import

Bundle everything to JSON:

```bash
wp mvp-docs export --output=kb-backup.json --pretty
```

Flags:
- `--docs` — include docs and categories only
- `--settings` — include settings and category order only
- `--output=<file>` — write to disk (otherwise stdout)
- `--pretty` — format JSON for readability

Restore:

```bash
wp mvp-docs import kb-backup.json
```

Docs are deduplicated by title, so the same bundle can be imported repeatedly without creating duplicates — useful for migrations and staging→prod syncs.

### Settings

`settings list` is self-documenting — it shows every setting with current value, description, and allowed form:

```
$ wp mvp-docs settings list
key                value                description                       allowed
columns            3                    Archive cards per row             1-4
border_radius      12                   Card corner radius (pixels)       0-24
card_bg            #ffffff              Card background                   hex color (e.g. #ffffff)
link_color         #1e40af              Body/link color                   hex color
sort_by            title                Archive sort field                title | date | modified
sort_order         asc                  Archive sort direction            asc | desc
docs_slug          docs                 URL slug for the docs archive     url-safe slug (triggers rewrite flush)
...
```

Use `--format=json` for machine-readable output.

`settings set` accepts multiple `key=value` pairs and reports any values the sanitizer rejected:

```bash
$ wp mvp-docs settings set columns=7 sort_by=alphabetical
Warning: columns: "7" was not accepted. Allowed: 1-4. Value is now "3".
Warning: sort_by: "alphabetical" was not accepted. Allowed: title | date | modified. Value is now "title".
Success: Settings saved.
```

Changing `docs_slug` or `category_slug` auto-flushes rewrite rules — no separate `wp rewrite flush` needed.

### End-to-end recipe

A complete KB setup from a fresh WordPress install:

```bash
wp plugin activate mvp-docs

wp mvp-docs settings set \
  docs_slug=kb \
  archive_title="Knowledge Base" \
  archive_subtitle="Everything you need to get started" \
  columns=3 \
  sort_by=title

wp term create mvpd_category "Getting Started" --slug=getting-started
wp term create mvpd_category "API Reference"   --slug=api-reference
wp term create mvpd_category "Troubleshooting" --slug=troubleshooting

wp mvp-docs reorder-categories getting-started api-reference troubleshooting

for f in ./docs/getting-started/*.md; do
  wp mvp-docs import-md "$f" --category=getting-started
done

wp mvp-docs export --output=./kb-backup.json
```

### Combining with core WP-CLI

MVP Docs doesn't reinvent commands that core WP-CLI already provides. Common pairings:

```bash
# List docs (uses core wp post)
wp post list --post_type=mvpd_doc

# Update a doc's sort order after import
wp post meta update 42 mvpd_sort_order 3

# Delete a doc
wp post delete 42 --force

# Bulk delete all docs
wp post list --post_type=mvpd_doc --field=ID | xargs -n1 wp post delete --force

# Create/manage categories (uses core wp term)
wp term create mvpd_category "Guides"
wp term list mvpd_category
wp term delete mvpd_category 12
```

## License

GPLv2 or later
