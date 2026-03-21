<?php
/**
 * CPT and taxonomy registration.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the configured URL slugs.
 */
function mvpd_get_slugs(): array {
	$settings = mvpd_get_settings();
	return [
		'docs'     => $settings['docs_slug'] ?? 'docs',
		'category' => $settings['category_slug'] ?? 'doc-category',
	];
}

/**
 * Register the Docs CPT and Doc Categories taxonomy.
 */
function mvpd_register_types(): void {
	$slugs = mvpd_get_slugs();

	register_post_type( 'mvp_doc', [
		'labels'       => [
			'name'               => __( 'Docs', 'mvp-docs' ),
			'singular_name'      => __( 'Doc', 'mvp-docs' ),
			'add_new'            => __( 'Add New', 'mvp-docs' ),
			'add_new_item'       => __( 'Add New Doc', 'mvp-docs' ),
			'edit_item'          => __( 'Edit Doc', 'mvp-docs' ),
			'view_item'          => __( 'View Doc', 'mvp-docs' ),
			'all_items'          => __( 'All Docs', 'mvp-docs' ),
			'search_items'       => __( 'Search Docs', 'mvp-docs' ),
			'not_found'          => __( 'No docs found.', 'mvp-docs' ),
			'not_found_in_trash' => __( 'No docs found in Trash.', 'mvp-docs' ),
			'menu_name'          => __( 'MVP Docs', 'mvp-docs' ),
		],
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => [ 'slug' => $slugs['docs'], 'with_front' => false ],
		'show_in_rest' => true,
		'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
		'taxonomies'   => [ 'mvpd_category' ],
		'menu_icon'    => 'dashicons-book-alt',
	] );

	register_taxonomy( 'mvpd_category', 'mvp_doc', [
		'labels'       => [
			'name'              => __( 'Doc Categories', 'mvp-docs' ),
			'singular_name'     => __( 'Doc Category', 'mvp-docs' ),
			'search_items'      => __( 'Search Doc Categories', 'mvp-docs' ),
			'all_items'         => __( 'All Doc Categories', 'mvp-docs' ),
			'parent_item'       => __( 'Parent Doc Category', 'mvp-docs' ),
			'parent_item_colon' => __( 'Parent Doc Category:', 'mvp-docs' ),
			'edit_item'         => __( 'Edit Doc Category', 'mvp-docs' ),
			'update_item'       => __( 'Update Doc Category', 'mvp-docs' ),
			'add_new_item'      => __( 'Add New Doc Category', 'mvp-docs' ),
			'new_item_name'     => __( 'New Doc Category Name', 'mvp-docs' ),
			'menu_name'         => __( 'Doc Categories', 'mvp-docs' ),
		],
		'hierarchical'      => false,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => [ 'slug' => $slugs['category'], 'with_front' => false ],
	] );

	register_post_meta( 'mvp_doc', 'mvpd_sort_order', [
		'show_in_rest'      => true,
		'single'            => true,
		'type'              => 'integer',
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	] );
}
add_action( 'init', 'mvpd_register_types' );

// Docs search rewrite.
add_action( 'init', function () {
	$slugs = mvpd_get_slugs();
	add_rewrite_rule(
		'^' . preg_quote( $slugs['docs'], '/' ) . '/search/?$',
		'index.php?post_type=mvp_doc&mvpd_search=1',
		'top'
	);
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'mvpd_search';
	return $vars;
} );

// Flush rewrite rules if slugs were changed.
add_action( 'init', function () {
	if ( get_option( 'mvpd_flush_rewrites' ) ) {
		flush_rewrite_rules();
		delete_option( 'mvpd_flush_rewrites' );
	}
}, 99 );

// Order docs on archive/taxonomy pages.
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'mvp_doc' ) || $query->is_tax( 'mvpd_category' ) ) {
		$sort = mvpd_get_sort_args();
		$query->set( 'orderby', $sort['orderby'] );
		$query->set( 'order', $sort['order'] );
		$query->set( 'posts_per_page', 200 );
	}
} );
