<?php
/**
 * WP-CLI commands for MVP Docs.
 *
 * Designed to let an operator (human or AI agent) configure and populate a docs
 * knowledge base end-to-end without touching wp-admin.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

require_once MVPD_PATH . 'includes/MarkdownToBlocks.php';

/**
 * Manage MVP Docs from the command line.
 */
class MVPD_CLI {

	/**
	 * Import a Markdown file as an mvpd_doc.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to a .md file.
	 *
	 * [--category=<slug>]
	 * : Assign the imported doc to this category (slug). Created if missing.
	 *
	 * [--title=<title>]
	 * : Override the post title (otherwise taken from the first H1 or filename).
	 *
	 * [--status=<status>]
	 * : Post status. Default: publish. One of: publish, draft, private.
	 *
	 * [--slug=<slug>]
	 * : Explicit post slug. Defaults to WordPress-generated from title.
	 *
	 * [--excerpt=<text>]
	 * : Post excerpt.
	 *
	 * [--sort-order=<n>]
	 * : Value for mvpd_sort_order post meta (lower numbers sort first).
	 *
	 * [--dry-run]
	 * : Print the generated block markup and title; do not create a post.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs import-md docs/getting-started.md
	 *     wp mvp-docs import-md docs/getting-started.md --category=guides --sort-order=1
	 *     wp mvp-docs import-md docs/getting-started.md --dry-run
	 *
	 * @subcommand import-md
	 */
	public function import_md( $args, $assoc_args ) {
		$file = self::resolve_path( $args[0] );
		self::assert_readable( $file );

		$result  = mvpd_markdown_to_blocks( file_get_contents( $file ) );
		$title   = $assoc_args['title'] ?? ( $result['title'] ?: pathinfo( $file, PATHINFO_FILENAME ) );

		self::create_or_dry_run( $title, $result['content'], $assoc_args );
	}

	/**
	 * Import a raw HTML file as an mvpd_doc.
	 *
	 * The HTML is written verbatim into post_content. If it contains Gutenberg
	 * block comments they are preserved; if not, WordPress treats the content
	 * as an implicit Classic block in the editor.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to an .html or .htm file.
	 *
	 * [--category=<slug>]
	 * : Assign the imported doc to this category (slug). Created if missing.
	 *
	 * [--title=<title>]
	 * : Override the post title (otherwise taken from <title>, first <h1>, then filename).
	 *
	 * [--status=<status>]
	 * : Post status. Default: publish. One of: publish, draft, private.
	 *
	 * [--slug=<slug>]
	 * : Explicit post slug. Defaults to WordPress-generated from title.
	 *
	 * [--excerpt=<text>]
	 * : Post excerpt.
	 *
	 * [--sort-order=<n>]
	 * : Value for mvpd_sort_order post meta (lower numbers sort first).
	 *
	 * [--dry-run]
	 * : Print the resolved title and content; do not create a post.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs import-raw docs/page.html
	 *     wp mvp-docs import-raw docs/page.html --title="My Doc" --category=guides --sort-order=2
	 *
	 * @subcommand import-raw
	 */
	public function import_raw( $args, $assoc_args ) {
		$file = self::resolve_path( $args[0] );
		self::assert_readable( $file );

		$html    = file_get_contents( $file );
		$title   = $assoc_args['title'] ?? self::extract_html_title( $html );
		if ( '' === $title ) {
			$title = pathinfo( $file, PATHINFO_FILENAME );
		}

		$content = $html;
		if ( empty( $assoc_args['title'] ) ) {
			// Strip the <title> tag from body so it doesn't render alongside the post title.
			$content = preg_replace( '#<title[^>]*>.*?</title>\s*#is', '', $content, 1 );
		}

		self::create_or_dry_run( $title, $content, $assoc_args );
	}

	/**
	 * Export docs, settings, categories, and category order to a JSON or zip bundle.
	 *
	 * ## OPTIONS
	 *
	 * [--docs]
	 * : Include docs and categories. Default on if neither flag is set.
	 *
	 * [--settings]
	 * : Include settings and category order. Default on if neither flag is set.
	 *
	 * [--with-images]
	 * : Bundle referenced images into a .zip alongside export.json. Requires --output.
	 *
	 * [--output=<file>]
	 * : Write to this path instead of stdout. Required when --with-images is set.
	 *
	 * [--pretty]
	 * : Pretty-print the JSON.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs export --output=/tmp/kb.json --pretty
	 *     wp mvp-docs export --with-images --output=/tmp/kb.zip
	 *     wp mvp-docs export --settings > settings.json
	 */
	public function export( $args, $assoc_args ) {
		$include_docs     = ! empty( $assoc_args['docs'] );
		$include_settings = ! empty( $assoc_args['settings'] );
		if ( ! $include_docs && ! $include_settings ) {
			$include_docs = $include_settings = true;
		}
		$with_images = ! empty( $assoc_args['with-images'] );

		if ( $with_images ) {
			if ( ! $include_docs ) {
				WP_CLI::error( '--with-images requires docs to be included.' );
			}
			if ( empty( $assoc_args['output'] ) ) {
				WP_CLI::error( '--with-images requires --output=<file>.' );
			}
			if ( ! class_exists( 'ZipArchive' ) ) {
				WP_CLI::error( 'PHP ZipArchive extension is not available.' );
			}
		}

		$export = mvpd_build_export_array( $include_docs, $include_settings );

		$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
		if ( ! empty( $assoc_args['pretty'] ) ) {
			$flags |= JSON_PRETTY_PRINT;
		}
		$json = wp_json_encode( $export, $flags );

		if ( $with_images ) {
			$path = self::resolve_path( $assoc_args['output'] );
			$zip  = new ZipArchive();
			if ( true !== $zip->open( $path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
				WP_CLI::error( "Could not create zip at {$path}" );
			}
			$zip->addFromString( 'export.json', $json );

			$urls    = mvpd_collect_image_urls( $export );
			$added   = 0;
			$missing = 0;
			foreach ( $urls as $url ) {
				$file  = mvpd_path_for_upload_url( $url );
				$entry = mvpd_zip_entry_for_url( $url );
				if ( $file && $entry ) {
					$zip->addFile( $file, $entry );
					$added++;
				} else {
					$missing++;
				}
			}
			$zip->close();

			$msg = sprintf( 'Wrote %s (%d images bundled', $path, $added );
			if ( $missing ) {
				$msg .= sprintf( ', %d skipped', $missing );
			}
			$msg .= ')';
			WP_CLI::success( $msg );
			return;
		}

		if ( ! empty( $assoc_args['output'] ) ) {
			$path  = self::resolve_path( $assoc_args['output'] );
			$bytes = file_put_contents( $path, $json );
			if ( false === $bytes ) {
				WP_CLI::error( "Could not write to {$path}" );
			}
			WP_CLI::success( sprintf( 'Wrote %d bytes to %s', $bytes, $path ) );
		} else {
			WP_CLI::line( $json );
		}
	}

	/**
	 * Import a JSON or zip bundle previously produced by `wp mvp-docs export`.
	 *
	 * Docs are deduplicated by title — if a doc with the same title exists it is skipped.
	 * Zip bundles ship referenced images alongside the data; images are sideloaded
	 * into the media library and content URLs are rewritten.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the JSON or zip bundle.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs import /tmp/kb.json
	 *     wp mvp-docs import /tmp/kb.zip
	 */
	public function import( $args, $assoc_args ) {
		$file = self::resolve_path( $args[0] );
		self::assert_readable( $file );

		$is_zip = preg_match( '/\.zip$/i', $file );

		$images_dir = '';
		$cleanup    = '';

		if ( $is_zip ) {
			if ( ! class_exists( 'ZipArchive' ) ) {
				WP_CLI::error( 'PHP ZipArchive extension is not available.' );
			}
			$zip = new ZipArchive();
			if ( true !== $zip->open( $file ) ) {
				WP_CLI::error( "Could not open zip: {$file}" );
			}
			$json = $zip->getFromName( 'export.json' );
			if ( false === $json ) {
				$zip->close();
				WP_CLI::error( 'Bundle is missing export.json.' );
			}
			$tmp = mvpd_temp_dir( 'cli' . wp_generate_password( 12, false ) );
			if ( ! $tmp ) {
				$zip->close();
				WP_CLI::error( 'Could not create temporary directory.' );
			}
			if ( ! $zip->extractTo( $tmp ) ) {
				$zip->close();
				mvpd_rmdir_recursive( $tmp );
				WP_CLI::error( 'Could not extract bundle.' );
			}
			$zip->close();
			$images_dir = $tmp;
			$cleanup    = $tmp;
		} else {
			$json = file_get_contents( $file );
		}

		$data = json_decode( $json, true );
		if ( ! is_array( $data ) || empty( $data['version'] ) ) {
			if ( $cleanup ) {
				mvpd_rmdir_recursive( $cleanup );
			}
			WP_CLI::error( 'Invalid export file.' );
		}

		$imported_settings = mvpd_import_settings_and_order( $data );
		$n_cats            = mvpd_import_categories( $data );
		$n_docs            = 0;
		$n_skipped         = 0;

		if ( ! empty( $data['docs'] ) && is_array( $data['docs'] ) ) {
			$map = [];
			foreach ( $data['docs'] as $doc ) {
				$res = mvpd_import_one_doc( $doc, $images_dir, $map );
				if ( $res === 'imported' ) {
					$n_docs++;
				} elseif ( $res === 'skipped' ) {
					$n_skipped++;
				}
			}
		}

		if ( $cleanup ) {
			mvpd_rmdir_recursive( $cleanup );
		}

		$parts = [];
		if ( $imported_settings ) { $parts[] = 'settings imported'; }
		if ( $n_cats )            { $parts[] = "{$n_cats} categor" . ( 1 === $n_cats ? 'y' : 'ies' ); }
		if ( $n_docs )            { $parts[] = "{$n_docs} doc" . ( 1 === $n_docs ? '' : 's' ) . ' imported'; }
		if ( $n_skipped )         { $parts[] = "{$n_skipped} skipped"; }
		if ( empty( $parts ) )    { $parts[] = 'nothing to import'; }
		WP_CLI::success( implode( ', ', $parts ) );
	}

	/**
	 * Reorder categories for archive display.
	 *
	 * Accepts term IDs or slugs.
	 *
	 * ## OPTIONS
	 *
	 * <term>...
	 * : One or more category term IDs or slugs in desired order.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs reorder-categories getting-started api-reference troubleshooting
	 *     wp mvp-docs reorder-categories 12 8 15
	 *
	 * @subcommand reorder-categories
	 */
	public function reorder_categories( $args, $assoc_args ) {
		$ids = [];
		foreach ( $args as $ref ) {
			$term = ctype_digit( (string) $ref )
				? get_term( (int) $ref, 'mvpd_category' )
				: get_term_by( 'slug', sanitize_title( $ref ), 'mvpd_category' );
			if ( ! $term || is_wp_error( $term ) ) {
				WP_CLI::error( "Category not found: {$ref}" );
			}
			$ids[] = (int) $term->term_id;
		}
		update_option( 'mvpd_category_order', $ids, false );
		WP_CLI::success( 'Category order saved: ' . implode( ', ', $ids ) );
	}

	// --- helpers ---

	protected static function resolve_path( $path ) {
		if ( '' === $path ) {
			return $path;
		}
		if ( '/' === $path[0] || preg_match( '#^[A-Za-z]:[/\\\\]#', $path ) ) {
			return $path;
		}
		return rtrim( getcwd(), '/\\' ) . DIRECTORY_SEPARATOR . $path;
	}

	protected static function assert_readable( $path ) {
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			WP_CLI::error( "File not readable: {$path}" );
		}
	}

	protected static function extract_html_title( $html ) {
		if ( preg_match( '#<title[^>]*>(.*?)</title>#is', $html, $m ) ) {
			$t = trim( html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5 ) );
			if ( '' !== $t ) {
				return $t;
			}
		}
		if ( preg_match( '#<h1[^>]*>(.*?)</h1>#is', $html, $m ) ) {
			return trim( wp_strip_all_tags( $m[1] ) );
		}
		return '';
	}

	protected static function create_or_dry_run( $title, $content, $assoc_args ) {
		if ( ! empty( $assoc_args['dry-run'] ) ) {
			WP_CLI::line( '--- TITLE ---' );
			WP_CLI::line( $title );
			WP_CLI::line( '--- CONTENT ---' );
			WP_CLI::line( $content );
			return;
		}

		$status = $assoc_args['status'] ?? 'publish';
		if ( ! in_array( $status, [ 'publish', 'draft', 'private' ], true ) ) {
			WP_CLI::error( "Invalid status: {$status}" );
		}

		$postarr = [
			'post_type'    => 'mvpd_doc',
			'post_status'  => $status,
			'post_title'   => $title,
			'post_content' => $content,
		];
		if ( isset( $assoc_args['slug'] ) && '' !== $assoc_args['slug'] ) {
			$postarr['post_name'] = sanitize_title( $assoc_args['slug'] );
		}
		if ( isset( $assoc_args['excerpt'] ) ) {
			$postarr['post_excerpt'] = $assoc_args['excerpt'];
		}

		$post_id = wp_insert_post( $postarr, true );

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::error( $post_id->get_error_message() );
		}

		if ( isset( $assoc_args['sort-order'] ) ) {
			update_post_meta( $post_id, 'mvpd_sort_order', absint( $assoc_args['sort-order'] ) );
		}

		if ( ! empty( $assoc_args['category'] ) ) {
			$slug    = sanitize_title( $assoc_args['category'] );
			$term    = get_term_by( 'slug', $slug, 'mvpd_category' );
			$term_id = null;
			if ( ! $term ) {
				$r = wp_insert_term( $assoc_args['category'], 'mvpd_category', [ 'slug' => $slug ] );
				if ( ! is_wp_error( $r ) ) {
					$term_id = (int) $r['term_id'];
				}
			} else {
				$term_id = (int) $term->term_id;
			}
			if ( $term_id ) {
				wp_set_object_terms( $post_id, [ $term_id ], 'mvpd_category' );
			}
		}

		WP_CLI::success( "Imported \"{$title}\" as doc #{$post_id}" );
	}
}

/**
 * Manage MVP Docs settings.
 */
class MVPD_Settings_CLI {

	/**
	 * Metadata for each setting — description and allowed value form.
	 * Used by `settings list` and `settings set` for self-documenting output.
	 */
	protected static function schema() {
		return [
			'columns'           => [ 'desc' => 'Archive cards per row',           'allowed' => '1-4' ],
			'border_radius'     => [ 'desc' => 'Card corner radius (pixels)',     'allowed' => '0-24' ],
			'docs_per_category' => [ 'desc' => 'Docs shown per category card',    'allowed' => '1-50' ],
			'card_bg'           => [ 'desc' => 'Card background',                 'allowed' => 'hex color (e.g. #ffffff)' ],
			'card_border'       => [ 'desc' => 'Card border',                     'allowed' => 'hex color' ],
			'header_bg'         => [ 'desc' => 'Card header strip background',    'allowed' => 'hex color or empty' ],
			'title_color'       => [ 'desc' => 'Heading and card title color',    'allowed' => 'hex color' ],
			'link_color'        => [ 'desc' => 'Body/link color',                 'allowed' => 'hex color' ],
			'link_hover'        => [ 'desc' => 'Link hover color',                'allowed' => 'hex color' ],
			'badge_bg'          => [ 'desc' => 'Category count badge background', 'allowed' => 'hex color' ],
			'badge_color'       => [ 'desc' => 'Category count badge text',       'allowed' => 'hex color' ],
			'archive_title'     => [ 'desc' => 'Archive page h1',                 'allowed' => 'plain text' ],
			'archive_subtitle'  => [ 'desc' => 'Archive page subtitle',           'allowed' => 'plain text' ],
			'category_title'    => [ 'desc' => 'Category page h1',                'allowed' => 'plain text' ],
			'search_title'      => [ 'desc' => 'Search results page h1',          'allowed' => 'plain text' ],
			'docs_slug'         => [ 'desc' => 'URL slug for the docs archive',   'allowed' => 'url-safe slug (triggers rewrite flush)' ],
			'category_slug'     => [ 'desc' => 'URL slug for category pages',     'allowed' => 'url-safe slug (triggers rewrite flush)' ],
			'sort_by'           => [ 'desc' => 'Archive sort field',              'allowed' => 'title | date | modified' ],
			'sort_order'        => [ 'desc' => 'Archive sort direction',          'allowed' => 'asc | desc' ],
		];
	}

	/**
	 * List all settings with current values, descriptions, and allowed forms.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format. One of: table, json, yaml, csv. Default: table.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs settings list
	 *     wp mvp-docs settings list --format=json
	 *
	 * @subcommand list
	 */
	public function list_all( $args, $assoc_args ) {
		$settings = mvpd_get_settings();
		$schema   = self::schema();
		$rows     = [];
		foreach ( $settings as $k => $v ) {
			$rows[] = [
				'key'         => $k,
				'value'       => (string) $v,
				'description' => $schema[ $k ]['desc']    ?? '',
				'allowed'     => $schema[ $k ]['allowed'] ?? '',
			];
		}
		$format = $assoc_args['format'] ?? 'table';
		WP_CLI\Utils\format_items( $format, $rows, [ 'key', 'value', 'description', 'allowed' ] );
	}

	/**
	 * Get a single setting value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : Setting key.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs settings get columns
	 */
	public function get( $args, $assoc_args ) {
		list( $key ) = $args;
		$settings    = mvpd_get_settings();
		if ( ! array_key_exists( $key, $settings ) ) {
			WP_CLI::error( "Unknown setting: {$key}" );
		}
		WP_CLI::line( (string) $settings[ $key ] );
	}

	/**
	 * Set one or more settings.
	 *
	 * Values pass through the same sanitizer used by the admin UI. Invalid
	 * values are silently replaced with defaults — run `settings get` after
	 * to confirm.
	 *
	 * ## OPTIONS
	 *
	 * <assignments>...
	 * : One or more key=value pairs.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mvp-docs settings set columns=4
	 *     wp mvp-docs settings set docs_slug=kb category_slug=kb-category
	 *     wp mvp-docs settings set archive_title="Knowledge Base" sort_by=date sort_order=desc
	 */
	public function set( $args, $assoc_args ) {
		$current   = mvpd_get_settings();
		$schema    = self::schema();
		$requested = [];

		foreach ( $args as $pair ) {
			if ( false === strpos( $pair, '=' ) ) {
				WP_CLI::error( "Expected key=value, got: {$pair}" );
			}
			list( $k, $v ) = explode( '=', $pair, 2 );
			if ( ! array_key_exists( $k, $current ) ) {
				WP_CLI::error( "Unknown setting: {$k}" );
			}
			$requested[ $k ] = $v;
			$current[ $k ]   = $v;
		}

		$clean = mvpd_sanitize_settings( $current );
		update_option( 'mvpd_settings', $clean );

		$errs = get_transient( 'mvpd_slug_errors' );
		if ( is_array( $errs ) && ! empty( $errs ) ) {
			foreach ( $errs as $field => $msg ) {
				WP_CLI::warning( "{$field}: {$msg}" );
			}
			delete_transient( 'mvpd_slug_errors' );
		}

		// Flush rewrites if the sanitizer marked a slug change. The admin UI
		// does this on next page load; for a CLI-only workflow, do it now.
		if ( get_option( 'mvpd_flush_rewrites' ) ) {
			delete_option( 'mvpd_flush_rewrites' );
			mvpd_register_types();
			flush_rewrite_rules();
			WP_CLI::log( 'Rewrite rules flushed.' );
		}

		// Report each change; warn loudly when the sanitizer rejected a value.
		foreach ( $requested as $k => $wanted ) {
			$actual = (string) $clean[ $k ];
			if ( (string) $wanted !== $actual ) {
				$allowed = $schema[ $k ]['allowed'] ?? 'see `wp mvp-docs settings list`';
				WP_CLI::warning( sprintf( '%s: "%s" was not accepted. Allowed: %s. Value is now "%s".', $k, $wanted, $allowed, $actual ) );
			} else {
				WP_CLI::log( sprintf( '%s = %s', $k, $actual ) );
			}
		}
		WP_CLI::success( 'Settings saved.' );
	}
}

WP_CLI::add_command( 'mvp-docs', 'MVPD_CLI' );
WP_CLI::add_command( 'mvp-docs settings', 'MVPD_Settings_CLI' );
