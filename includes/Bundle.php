<?php
/**
 * Bundle — image-aware export/import helpers.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the export array (docs/categories/settings). Same shape as v1.1.x JSON.
 */
function mvpd_build_export_array( bool $include_docs, bool $include_settings ): array {
	$export = [ 'version' => MVPD_VERSION ];

	if ( $include_settings ) {
		$export['settings']       = mvpd_get_settings();
		$export['category_order'] = get_option( 'mvpd_category_order', [] );
	}

	if ( $include_docs ) {
		$categories = get_terms( [
			'taxonomy'   => 'mvpd_category',
			'hide_empty' => false,
		] );

		$export['categories'] = [];
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$export['categories'][] = [
					'name'        => $cat->name,
					'slug'        => $cat->slug,
					'description' => $cat->description,
				];
			}
		}

		$docs = new WP_Query( [
			'post_type'      => 'mvpd_doc',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		] );

		$export['docs'] = [];
		while ( $docs->have_posts() ) {
			$docs->the_post();
			$post_id   = get_the_ID();
			$terms     = get_the_terms( $post_id, 'mvpd_category' );
			$cat_slugs = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'slug' ) : [];

			$thumb_id  = get_post_thumbnail_id( $post_id );
			$thumb_url = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';

			$export['docs'][] = [
				'title'         => get_the_title(),
				'slug'          => get_post_field( 'post_name' ),
				'content'       => get_the_content(),
				'excerpt'       => get_the_excerpt(),
				'status'        => get_post_status(),
				'categories'    => $cat_slugs,
				'sort_order'    => (int) get_post_meta( $post_id, 'mvpd_sort_order', true ),
				'featured_url'  => $thumb_url ?: '',
			];
		}
		wp_reset_postdata();
	}

	return $export;
}

/**
 * Walk an export array and return a deduped list of local upload URLs referenced
 * by doc content or featured images.
 */
function mvpd_collect_image_urls( array $export ): array {
	if ( empty( $export['docs'] ) ) {
		return [];
	}

	$urls    = [];
	$baseurl = wp_upload_dir()['baseurl'] ?? '';
	if ( ! $baseurl ) {
		return [];
	}

	foreach ( $export['docs'] as $doc ) {
		if ( ! empty( $doc['featured_url'] ) && mvpd_is_local_upload_url( $doc['featured_url'], $baseurl ) ) {
			$urls[ $doc['featured_url'] ] = true;
		}

		$content = $doc['content'] ?? '';
		if ( ! $content ) {
			continue;
		}

		// <img src="..."> and srcset entries.
		if ( preg_match_all( '/<img[^>]+(?:src|srcset)\s*=\s*"([^"]+)"/i', $content, $m ) ) {
			foreach ( $m[1] as $val ) {
				// srcset is a comma-separated list of "url 1x" entries.
				foreach ( preg_split( '/\s*,\s*/', $val ) as $part ) {
					$u = preg_split( '/\s+/', trim( $part ) )[0] ?? '';
					if ( $u && mvpd_is_local_upload_url( $u, $baseurl ) ) {
						$urls[ $u ] = true;
					}
				}
			}
		}
	}

	return array_keys( $urls );
}

function mvpd_is_local_upload_url( string $url, string $baseurl ): bool {
	return $url && str_starts_with( $url, $baseurl );
}

/**
 * Convert an upload URL to its absolute filesystem path.
 */
function mvpd_path_for_upload_url( string $url ): string {
	$dir = wp_upload_dir();
	if ( empty( $dir['baseurl'] ) || empty( $dir['basedir'] ) ) {
		return '';
	}
	if ( ! str_starts_with( $url, $dir['baseurl'] ) ) {
		return '';
	}
	$rel  = ltrim( substr( $url, strlen( $dir['baseurl'] ) ), '/' );
	$path = $dir['basedir'] . '/' . $rel;
	// Confine to basedir.
	$real_base = realpath( $dir['basedir'] );
	$real_path = realpath( $path );
	if ( ! $real_base || ! $real_path || ! str_starts_with( $real_path, $real_base ) ) {
		return '';
	}
	return $real_path;
}

/**
 * Path inside the zip for a given upload URL (mirrors the uploads tree).
 */
function mvpd_zip_entry_for_url( string $url ): string {
	$dir = wp_upload_dir();
	if ( empty( $dir['baseurl'] ) ) {
		return '';
	}
	$rel = ltrim( substr( $url, strlen( $dir['baseurl'] ) ), '/' );
	return 'uploads/' . $rel;
}

/**
 * Get (creating if needed) the temp dir for export/import jobs.
 * Returns absolute path with trailing slash, or '' on failure.
 */
function mvpd_temp_dir( string $job_id = '' ): string {
	$base = wp_upload_dir()['basedir'] ?? '';
	if ( ! $base ) {
		return '';
	}
	$root = trailingslashit( $base ) . 'mvpd-tmp';
	if ( ! wp_mkdir_p( $root ) ) {
		return '';
	}
	// Prevent directory listing.
	if ( ! file_exists( $root . '/index.html' ) ) {
		@file_put_contents( $root . '/index.html', '' );
	}
	if ( $job_id === '' ) {
		return trailingslashit( $root );
	}
	$job_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', $job_id );
	if ( ! $job_id ) {
		return '';
	}
	$dir = trailingslashit( $root ) . $job_id;
	if ( ! wp_mkdir_p( $dir ) ) {
		return '';
	}
	return trailingslashit( $dir );
}

/**
 * Recursively delete a job dir.
 */
function mvpd_rmdir_recursive( string $path ): void {
	if ( ! is_dir( $path ) ) {
		if ( is_file( $path ) ) {
			@unlink( $path );
		}
		return;
	}
	$items = @scandir( $path );
	if ( ! $items ) {
		return;
	}
	foreach ( $items as $item ) {
		if ( $item === '.' || $item === '..' ) {
			continue;
		}
		mvpd_rmdir_recursive( $path . '/' . $item );
	}
	@rmdir( $path );
}

/**
 * Daily cleanup of stale job dirs (> 24h old).
 */
function mvpd_cleanup_stale_jobs(): void {
	$root = mvpd_temp_dir();
	if ( ! $root || ! is_dir( $root ) ) {
		return;
	}
	$cutoff = time() - DAY_IN_SECONDS;
	$items  = @scandir( $root );
	if ( ! $items ) {
		return;
	}
	foreach ( $items as $item ) {
		if ( $item === '.' || $item === '..' || $item === 'index.html' ) {
			continue;
		}
		$full = $root . $item;
		if ( is_dir( $full ) && filemtime( $full ) < $cutoff ) {
			mvpd_rmdir_recursive( $full );
		}
	}
}
add_action( 'mvpd_cleanup_jobs', 'mvpd_cleanup_stale_jobs' );

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'mvpd_cleanup_jobs' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'mvpd_cleanup_jobs' );
	}
} );

/**
 * Sideload an image file from a local path into the media library.
 * Returns [ 'id' => int, 'url' => string ] on success or null on failure.
 */
function mvpd_sideload_local_file( string $path, string $original_filename = '' ): ?array {
	if ( ! file_exists( $path ) ) {
		return null;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filename = $original_filename ?: basename( $path );
	$tmp      = wp_tempnam( $filename );
	if ( ! $tmp ) {
		return null;
	}
	if ( ! @copy( $path, $tmp ) ) {
		@unlink( $tmp );
		return null;
	}

	$file_array = [
		'name'     => $filename,
		'tmp_name' => $tmp,
	];

	$id = media_handle_sideload( $file_array, 0 );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		return null;
	}

	return [
		'id'  => (int) $id,
		'url' => (string) wp_get_attachment_url( $id ),
	];
}

/**
 * Apply settings + category-order pieces of an import payload.
 * Returns true if settings were imported.
 */
function mvpd_import_settings_and_order( array $data ): bool {
	$imported = false;
	if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
		update_option( 'mvpd_settings', mvpd_sanitize_settings( $data['settings'] ) );
		$imported = true;
	}
	if ( isset( $data['category_order'] ) && is_array( $data['category_order'] ) ) {
		$clean = array_filter( array_map( 'absint', $data['category_order'] ) );
		update_option( 'mvpd_category_order', $clean, false );
	}
	return $imported;
}

/**
 * Import categories from an import payload. Returns the number created.
 */
function mvpd_import_categories( array $data ): int {
	if ( empty( $data['categories'] ) || ! is_array( $data['categories'] ) ) {
		return 0;
	}
	$count = 0;
	foreach ( $data['categories'] as $cat ) {
		if ( empty( $cat['name'] ) ) {
			continue;
		}
		$slug = sanitize_title( $cat['slug'] ?? '' );
		if ( get_term_by( 'slug', $slug, 'mvpd_category' ) ) {
			continue;
		}
		$result = wp_insert_term( sanitize_text_field( $cat['name'] ), 'mvpd_category', [
			'slug'        => $slug,
			'description' => sanitize_text_field( $cat['description'] ?? '' ),
		] );
		if ( ! is_wp_error( $result ) ) {
			$count++;
		}
	}
	return $count;
}

/**
 * Import a single doc record. Returns 'imported', 'skipped', or 'failed'.
 *
 * If $images_dir is non-empty, image URLs in the content (and the featured URL)
 * will be sideloaded from that directory and rewritten to point at the new
 * attachments. $url_map is passed by reference so the caller can dedupe across
 * docs.
 */
function mvpd_import_one_doc( array $doc, string $images_dir = '', array &$url_map = [] ): string {
	if ( empty( $doc['title'] ) ) {
		return 'failed';
	}

	$existing = new WP_Query( [
		'post_type'              => 'mvpd_doc',
		'title'                  => sanitize_text_field( $doc['title'] ),
		'posts_per_page'         => 1,
		'post_status'            => 'any',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	] );
	if ( $existing->have_posts() ) {
		return 'skipped';
	}

	$content = (string) ( $doc['content'] ?? '' );

	// Sideload images referenced in this doc and build a URL map.
	if ( $images_dir && $content ) {
		$old_baseurl = $doc['source_baseurl'] ?? ''; // optional hint, not currently used
		// Find candidate URLs in this content.
		if ( preg_match_all( '/<img[^>]+(?:src|srcset)\s*=\s*"([^"]+)"/i', $content, $m ) ) {
			foreach ( $m[1] as $val ) {
				foreach ( preg_split( '/\s*,\s*/', $val ) as $part ) {
					$u = preg_split( '/\s+/', trim( $part ) )[0] ?? '';
					if ( $u && ! isset( $url_map[ $u ] ) ) {
						$file = mvpd_sideload_from_bundle( $u, $images_dir );
						if ( $file ) {
							$url_map[ $u ] = $file['url'];
						}
					}
				}
			}
		}
		$content = mvpd_rewrite_image_urls( $content, $url_map );
	}

	$post_id = wp_insert_post( [
		'post_type'    => 'mvpd_doc',
		'post_title'   => sanitize_text_field( $doc['title'] ),
		'post_name'    => sanitize_title( $doc['slug'] ?? '' ),
		'post_content' => wp_kses_post( $content ),
		'post_excerpt' => sanitize_text_field( $doc['excerpt'] ?? '' ),
		'post_status'  => in_array( $doc['status'] ?? '', [ 'publish', 'draft', 'private' ], true ) ? sanitize_key( $doc['status'] ) : 'draft',
	] );

	if ( is_wp_error( $post_id ) ) {
		return 'failed';
	}

	if ( ! empty( $doc['sort_order'] ) ) {
		update_post_meta( $post_id, 'mvpd_sort_order', absint( $doc['sort_order'] ) );
	}

	if ( ! empty( $doc['categories'] ) && is_array( $doc['categories'] ) ) {
		$term_ids = [];
		foreach ( $doc['categories'] as $slug ) {
			$term = get_term_by( 'slug', sanitize_title( $slug ), 'mvpd_category' );
			if ( $term ) {
				$term_ids[] = $term->term_id;
			}
		}
		if ( $term_ids ) {
			wp_set_object_terms( $post_id, $term_ids, 'mvpd_category' );
		}
	}

	// Featured image.
	if ( $images_dir && ! empty( $doc['featured_url'] ) ) {
		$featured_url = $doc['featured_url'];
		if ( ! isset( $url_map[ $featured_url ] ) ) {
			$file = mvpd_sideload_from_bundle( $featured_url, $images_dir );
			if ( $file ) {
				$url_map[ $featured_url ] = $file['url'];
				set_post_thumbnail( $post_id, $file['id'] );
			}
		} else {
			$existing_id = attachment_url_to_postid( $url_map[ $featured_url ] );
			if ( $existing_id ) {
				set_post_thumbnail( $post_id, $existing_id );
			}
		}
	}

	return 'imported';
}

/**
 * Given an old upload URL and the unzipped bundle's image dir, locate the
 * corresponding file under <images_dir>/uploads/<rel> and sideload it.
 */
function mvpd_sideload_from_bundle( string $old_url, string $images_dir ): ?array {
	// The zip stores files at uploads/<year>/<month>/<file>. Strip query strings.
	$old_url = preg_replace( '/\?.*$/', '', $old_url );
	if ( ! preg_match( '#/uploads/(.+)$#', $old_url, $m ) ) {
		return null;
	}
	$rel  = $m[1];
	$path = trailingslashit( $images_dir ) . 'uploads/' . $rel;
	if ( ! file_exists( $path ) ) {
		// Try the path as-is in case the URL didn't include /uploads/.
		$alt = trailingslashit( $images_dir ) . ltrim( parse_url( $old_url, PHP_URL_PATH ) ?? '', '/' );
		if ( file_exists( $alt ) ) {
			$path = $alt;
		} else {
			return null;
		}
	}
	return mvpd_sideload_local_file( $path, basename( $path ) );
}

/**
 * Rewrite all instances of $old_url (and its sized variants) in $content to $new_url.
 * Sized variants follow WP's "name-WxH.ext" pattern, so we replace by base path.
 */
function mvpd_rewrite_image_urls( string $content, array $url_map ): string {
	if ( ! $content || ! $url_map ) {
		return $content;
	}
	foreach ( $url_map as $old => $new ) {
		// Replace exact URL and any sized variant prefix (strip extension to catch -WxH variants).
		$content = str_replace( $old, $new, $content );

		$ext_pos = strrpos( $old, '.' );
		if ( $ext_pos !== false ) {
			$old_base = substr( $old, 0, $ext_pos );
			$old_ext  = substr( $old, $ext_pos );
			$new_pos  = strrpos( $new, '.' );
			if ( $new_pos !== false ) {
				$new_base = substr( $new, 0, $new_pos );
				// Match name-WxH.ext variants.
				$pattern = '/' . preg_quote( $old_base, '/' ) . '-(\d+x\d+)' . preg_quote( $old_ext, '/' ) . '/';
				$content = preg_replace_callback( $pattern, function ( $m ) use ( $new_base, $old_ext ) {
					return $new_base . '-' . $m[1] . $old_ext;
				}, $content );
			}
		}
	}
	return $content;
}
