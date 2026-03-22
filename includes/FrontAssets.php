<?php
/**
 * Front-end asset enqueuing and CSS variable output.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

// Enqueue front-end CSS on doc pages.
add_action( 'wp_enqueue_scripts', function () {
	$is_search   = (bool) get_query_var( 'mvpd_search' );
	$is_doc_page = is_post_type_archive( 'mvp_doc' ) || is_tax( 'mvpd_category' ) || is_singular( 'mvp_doc' ) || $is_search;

	if ( $is_doc_page ) {
		wp_enqueue_style( 'mvpd-archive', MVPD_URL . 'assets/front/archive.css', [], MVPD_VERSION );
	}

	if ( is_post_type_archive( 'mvp_doc' ) || is_tax( 'mvpd_category' ) || $is_search ) {
		$slugs = mvpd_get_slugs();
		wp_enqueue_script( 'mvpd-archive-search', MVPD_URL . 'assets/front/archive-search.js', [], MVPD_VERSION, true );
		wp_localize_script( 'mvpd-archive-search', 'mvpdSearch', [
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'searchUrl' => home_url( '/' . $slugs['docs'] . '/search/' ),
		] );
	}
} );

// Output CSS variables from settings.
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_post_type_archive( 'mvp_doc' ) && ! is_tax( 'mvpd_category' ) && ! get_query_var( 'mvpd_search' ) ) {
		return;
	}

	$s   = mvpd_get_settings();
	$css = '.mvpd-archive {';

	$map = [
		'--mvpd-columns'       => $s['columns'],
		'--mvpd-card-bg'       => $s['card_bg'],
		'--mvpd-card-border'   => $s['card_border'],
		'--mvpd-title-color'   => $s['title_color'],
		'--mvpd-link-color'    => $s['link_color'],
		'--mvpd-link-hover'    => $s['link_hover'],
		'--mvpd-badge-bg'      => $s['badge_bg'],
		'--mvpd-badge-color'   => $s['badge_color'],
		'--mvpd-border-radius' => $s['border_radius'] . 'px',
	];

	if ( ! empty( $s['header_bg'] ) ) {
		$map['--mvpd-header-bg'] = $s['header_bg'];
	}

	foreach ( $map as $var => $val ) {
		$safe_val = preg_replace( '/[^a-zA-Z0-9#.\-]/', '', $val );
		$css     .= $var . ':' . $safe_val . ';';
	}

	$css .= '}';
	wp_add_inline_style( 'mvpd-archive', $css );
}, 20 );

// AJAX: search docs.
add_action( 'wp_ajax_mvpd_search_docs', 'mvpd_ajax_search_docs' );
add_action( 'wp_ajax_nopriv_mvpd_search_docs', 'mvpd_ajax_search_docs' );

function mvpd_ajax_search_docs(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only search.
	$query = sanitize_text_field( wp_unslash( $_GET['mvpd_s'] ?? '' ) );

	if ( strlen( $query ) < 2 ) {
		wp_send_json_success( [] );
	}

	$results = new WP_Query( [
		'post_type'      => 'mvp_doc',
		'posts_per_page' => 10,
		's'              => $query,
		'post_status'    => 'publish',
	] );

	$docs = [];
	while ( $results->have_posts() ) {
		$results->the_post();
		$terms = get_the_terms( get_the_ID(), 'mvpd_category' );
		$docs[] = [
			'title'    => get_the_title(),
			'url'      => get_the_permalink(),
			'category' => ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '',
		];
	}
	wp_reset_postdata();

	wp_send_json_success( $docs );
}
