<?php
/**
 * Settings logic — defaults, sanitization, getters, slug conflict checks.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

function mvpd_default_settings(): array {
	return [
		'columns'           => '3',
		'card_bg'           => '#ffffff',
		'card_border'       => '#e2e2e2',
		'header_bg'         => '',
		'title_color'       => '#1a1a1a',
		'link_color'        => '#333333',
		'link_hover'        => '#1a56db',
		'badge_bg'          => '#f0f0f0',
		'badge_color'       => '#1a1a1a',
		'border_radius'     => '12',
		'docs_per_category' => '5',
		'docs_slug'         => 'docs',
		'category_slug'     => 'doc-category',
		'archive_title'     => 'Documentation',
		'archive_subtitle'  => 'Browse our documentation to learn how everything works.',
		'category_title'    => 'Category',
		'search_title'      => 'Search Results',
		'sort_by'           => 'title',
		'sort_order'        => 'asc',
	];
}

function mvpd_get_settings(): array {
	$settings = get_option( 'mvpd_settings', [] );
	return wp_parse_args( $settings, mvpd_default_settings() );
}

function mvpd_get_sort_args(): array {
	$s = mvpd_get_settings();
	return [
		'orderby' => $s['sort_by'],
		'order'   => strtoupper( $s['sort_order'] ),
	];
}

function mvpd_sanitize_settings( $input ): array {
	$defaults = mvpd_default_settings();

	if ( ! is_array( $input ) ) {
		return $defaults;
	}

	$clean = [];

	$clean['columns']           = in_array( $input['columns'] ?? '', [ '1', '2', '3', '4' ], true ) ? $input['columns'] : $defaults['columns'];
	$clean['card_bg']           = sanitize_hex_color( $input['card_bg'] ?? '' ) ?: $defaults['card_bg'];
	$clean['card_border']       = sanitize_hex_color( $input['card_border'] ?? '' ) ?: $defaults['card_border'];
	$clean['header_bg']         = sanitize_hex_color( $input['header_bg'] ?? '' ) ?: '';
	$clean['title_color']       = sanitize_hex_color( $input['title_color'] ?? '' ) ?: $defaults['title_color'];
	$clean['link_color']        = sanitize_hex_color( $input['link_color'] ?? '' ) ?: $defaults['link_color'];
	$clean['link_hover']        = sanitize_hex_color( $input['link_hover'] ?? '' ) ?: $defaults['link_hover'];
	$clean['badge_bg']          = sanitize_hex_color( $input['badge_bg'] ?? '' ) ?: $defaults['badge_bg'];
	$clean['badge_color']       = sanitize_hex_color( $input['badge_color'] ?? '' ) ?: $defaults['badge_color'];
	$clean['border_radius']     = min( 24, max( 0, absint( $input['border_radius'] ?? 12 ) ) );
	$clean['docs_per_category'] = min( 50, max( 1, absint( $input['docs_per_category'] ?? 5 ) ) );
	$clean['archive_title']     = sanitize_text_field( $input['archive_title'] ?? $defaults['archive_title'] );
	$clean['archive_subtitle']  = sanitize_text_field( $input['archive_subtitle'] ?? $defaults['archive_subtitle'] );
	$clean['category_title']    = sanitize_text_field( $input['category_title'] ?? $defaults['category_title'] );
	$clean['search_title']      = sanitize_text_field( $input['search_title'] ?? $defaults['search_title'] );
	$clean['sort_by']           = in_array( $input['sort_by'] ?? '', [ 'title', 'date', 'modified' ], true ) ? $input['sort_by'] : $defaults['sort_by'];
	$clean['sort_order']        = in_array( $input['sort_order'] ?? '', [ 'asc', 'desc' ], true ) ? $input['sort_order'] : $defaults['sort_order'];

	// Slug settings.
	$docs_slug     = sanitize_title( $input['docs_slug'] ?? 'docs' );
	$category_slug = sanitize_title( $input['category_slug'] ?? 'doc-category' );

	if ( empty( $docs_slug ) ) {
		$docs_slug = $defaults['docs_slug'];
	}
	if ( empty( $category_slug ) ) {
		$category_slug = $defaults['category_slug'];
	}
	if ( $docs_slug === $category_slug ) {
		$category_slug = $docs_slug . '-category';
	}

	$old              = get_option( 'mvpd_settings', [] );
	$old_docs_slug    = $old['docs_slug'] ?? $defaults['docs_slug'];
	$old_cat_slug     = $old['category_slug'] ?? $defaults['category_slug'];

	if ( $docs_slug !== $old_docs_slug || $category_slug !== $old_cat_slug ) {
		$field_errors = mvpd_check_slug_conflicts( $docs_slug, $category_slug );

		if ( ! empty( $field_errors ) ) {
			set_transient( 'mvpd_slug_errors', $field_errors, 60 );
			$docs_slug     = $old_docs_slug;
			$category_slug = $old_cat_slug;
		} else {
			update_option( 'mvpd_flush_rewrites', true, false );
		}
	}

	$clean['docs_slug']     = $docs_slug;
	$clean['category_slug'] = $category_slug;

	return $clean;
}

/**
 * Check if proposed slugs conflict with existing pages, CPTs, or taxonomies.
 */
function mvpd_check_slug_conflicts( string $docs_slug, string $category_slug ): array {
	$errors = [];

	foreach ( [ 'docs_slug' => $docs_slug, 'category_slug' => $category_slug ] as $key => $slug ) {
		$page = get_page_by_path( $slug, OBJECT, [ 'page', 'post' ] );
		if ( $page ) {
			$type_obj       = get_post_type_object( $page->post_type );
			/* translators: %1$s: slug, %2$s: post type name */
			$errors[ $key ] = sprintf(
				__( '"%1$s" conflicts with an existing %2$s.', 'mvp-docs' ),
				$slug,
				$type_obj ? $type_obj->labels->singular_name : $page->post_type
			);
			continue;
		}

		$cpts = get_post_types( [ 'public' => true ], 'objects' );
		foreach ( $cpts as $cpt ) {
			if ( 'mvp_doc' === $cpt->name ) {
				continue;
			}
			if ( isset( $cpt->rewrite['slug'] ) && $cpt->rewrite['slug'] === $slug ) {
				/* translators: %1$s: slug, %2$s: post type name */
				$errors[ $key ] = sprintf(
					__( '"%1$s" conflicts with the "%2$s" post type.', 'mvp-docs' ),
					$slug,
					$cpt->labels->name
				);
				continue 2;
			}
		}

		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		foreach ( $taxonomies as $tax ) {
			if ( 'mvpd_category' === $tax->name ) {
				continue;
			}
			if ( isset( $tax->rewrite['slug'] ) && $tax->rewrite['slug'] === $slug ) {
				/* translators: %1$s: slug, %2$s: taxonomy name */
				$errors[ $key ] = sprintf(
					__( '"%1$s" conflicts with the "%2$s" taxonomy.', 'mvp-docs' ),
					$slug,
					$tax->labels->name
				);
				continue 2;
			}
		}
	}

	return $errors;
}

/**
 * Get categories in saved order.
 */
function mvpd_get_ordered_categories(): array {
	$categories = get_terms( [
		'taxonomy'   => 'mvpd_category',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return [];
	}

	$saved_order = get_option( 'mvpd_category_order', [] );
	if ( ! empty( $saved_order ) ) {
		$order_map = array_flip( array_map( 'intval', $saved_order ) );
		usort( $categories, function ( $a, $b ) use ( $order_map ) {
			$pos_a = $order_map[ $a->term_id ] ?? 999;
			$pos_b = $order_map[ $b->term_id ] ?? 999;
			return $pos_a - $pos_b;
		} );
	}

	return $categories;
}
