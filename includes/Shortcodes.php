<?php
/**
 * Shortcode registrations.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'mvpd_archive_header', function () {
	$s    = mvpd_get_settings();
	$html = '';

	if ( $s['archive_title'] ) {
		$html .= '<h1 class="mvpd-page-title">' . esc_html( $s['archive_title'] ) . '</h1>';
	}
	if ( $s['archive_subtitle'] ) {
		$html .= '<p class="mvpd-page-subtitle">' . esc_html( $s['archive_subtitle'] ) . '</p>';
	}

	return $html ? '<div class="mvpd-page-header">' . $html . '</div>' : '';
} );

add_shortcode( 'mvpd_category_header', function () {
	$s = mvpd_get_settings();
	if ( $s['category_title'] ) {
		return '<div class="mvpd-page-header"><h1 class="mvpd-page-title">' . esc_html( $s['category_title'] ) . '</h1></div>';
	}
	return '';
} );

add_shortcode( 'mvpd_search_header', function () {
	$s = mvpd_get_settings();
	if ( $s['search_title'] ) {
		return '<div class="mvpd-page-header"><h1 class="mvpd-page-title">' . esc_html( $s['search_title'] ) . '</h1></div>';
	}
	return '';
} );

add_shortcode( 'mvpd_archive', function () {
	$categories = mvpd_get_ordered_categories();

	ob_start();
	include MVPD_PATH . 'templates/archive-content.php';
	return ob_get_clean();
} );

add_shortcode( 'mvpd_search', function () {
	$search_query = isset( $_GET['mvpd_s'] ) ? sanitize_text_field( wp_unslash( $_GET['mvpd_s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only search; nonce would break bookmarkable/shareable URLs (same pattern as core ?s= search).

	if ( '' !== $search_query ) {
		$search_docs = new WP_Query( [
			'post_type'      => 'mvpd_doc',
			'posts_per_page' => 200,
			's'              => $search_query,
			'post_status'    => 'publish',
		] );
	} else {
		$search_docs = new WP_Query();
	}

	ob_start();
	include MVPD_PATH . 'templates/search-content.php';
	wp_reset_postdata();
	return ob_get_clean();
} );

add_shortcode( 'mvpd_category', function () {
	$term = get_queried_object();
	if ( ! $term || ! is_a( $term, 'WP_Term' ) ) {
		return '';
	}

	$cat_docs = new WP_Query( array_merge( [
		'post_type'      => 'mvpd_doc',
		'posts_per_page' => 200,
		'tax_query'      => [ [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'taxonomy' => 'mvpd_category',
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		] ],
	], mvpd_get_sort_args() ) );

	ob_start();
	include MVPD_PATH . 'templates/category-content.php';
	wp_reset_postdata();
	return ob_get_clean();
} );
