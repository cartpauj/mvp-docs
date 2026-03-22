<?php
/**
 * Shortcode registrations.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'mvpd_archive_header', function () {
	$s   = mvpd_get_settings();
	$html = '';

	if ( $s['archive_title'] ) {
		$html .= '<h1>' . esc_html( $s['archive_title'] ) . '</h1>';
	}
	if ( $s['archive_subtitle'] ) {
		$html .= '<p>' . esc_html( $s['archive_subtitle'] ) . '</p>';
	}

	return $html;
} );

add_shortcode( 'mvpd_category_header', function () {
	$s = mvpd_get_settings();
	if ( $s['category_title'] ) {
		return '<h1>' . esc_html( $s['category_title'] ) . '</h1>';
	}
	return '';
} );

add_shortcode( 'mvpd_search_header', function () {
	$s = mvpd_get_settings();
	if ( $s['search_title'] ) {
		return '<h1>' . esc_html( $s['search_title'] ) . '</h1>';
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
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public search display only.
	$search_query = sanitize_text_field( wp_unslash( $_GET['mvpd_s'] ?? '' ) );
	$search_docs  = new WP_Query( [
		'post_type'      => 'mvp_doc',
		'posts_per_page' => 200,
		's'              => $search_query,
		'post_status'    => 'publish',
	] );

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
		'post_type'      => 'mvp_doc',
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
