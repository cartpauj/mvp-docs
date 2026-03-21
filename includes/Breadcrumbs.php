<?php
/**
 * Breadcrumbs — prepended to single doc content via the_content filter.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'mvp_doc' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$archive_url = get_post_type_archive_link( 'mvp_doc' );
	$crumbs      = '<a href="' . esc_url( $archive_url ) . '">' . esc_html__( 'Docs', 'mvp-docs' ) . '</a>';

	$cats = get_the_terms( get_the_ID(), 'mvpd_category' );
	if ( $cats && ! is_wp_error( $cats ) ) {
		$cat     = $cats[0];
		$crumbs .= ' <span class="mvpd-crumb-sep">/</span> ';
		$crumbs .= '<a href="' . esc_url( get_term_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
	}

	$crumbs .= ' <span class="mvpd-crumb-sep">/</span> ';
	$crumbs .= '<span class="mvpd-crumb-current">' . esc_html( get_the_title() ) . '</span>';

	$breadcrumb = '<nav class="mvpd-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'mvp-docs' ) . '">' . $crumbs . '</nav>';

	return $breadcrumb . $content;
} );
