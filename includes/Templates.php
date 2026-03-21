<?php
/**
 * Template registration — block themes and classic themes.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build block template markup that wraps shortcodes in the standard layout.
 */
function mvpd_block_template( string $shortcodes ): string {
	return '<!-- wp:template-part {"slug":"header","area":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">

' . $shortcodes . '

</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","area":"footer"} /-->';
}

if ( wp_is_block_theme() ) {
	add_action( 'init', function () {
		if ( ! function_exists( 'register_block_template' ) ) {
			return;
		}

		// Single doc uses native post blocks.
		$single_file = MVPD_PATH . 'templates/block/single-mvp_doc.html';
		if ( file_exists( $single_file ) ) {
			register_block_template( 'mvp-docs//single-mvp_doc', [
				'title'      => __( 'Single Doc', 'mvp-docs' ),
				'content'    => file_get_contents( $single_file ),
				'post_types' => [ 'mvp_doc' ],
			] );
		}

		$shortcode = '<!-- wp:shortcode -->' . "\n" . '%s' . "\n" . '<!-- /wp:shortcode -->';

		// Archive, category, and search share the same wrapper — only shortcodes differ.
		$templates = [
			'archive-mvp_doc' => [
				'title'      => __( 'Doc Archive', 'mvp-docs' ),
				'post_types' => [ 'mvp_doc' ],
				'shortcodes' => sprintf( $shortcode, '[mvpd_archive_header]' ) . "\n\n" . sprintf( $shortcode, '[mvpd_archive]' ),
			],
			'taxonomy-mvpd_category' => [
				'title'      => __( 'Doc Category', 'mvp-docs' ),
				'shortcodes' => sprintf( $shortcode, '[mvpd_category_header]' ) . "\n\n" . sprintf( $shortcode, '[mvpd_category]' ),
			],
			'search-mvp_doc' => [
				'title'      => __( 'Doc Search', 'mvp-docs' ),
				'shortcodes' => sprintf( $shortcode, '[mvpd_search_header]' ) . "\n\n" . sprintf( $shortcode, '[mvpd_search]' ),
			],
		];

		foreach ( $templates as $slug => $args ) {
			$reg_args = [
				'title'   => $args['title'],
				'content' => mvpd_block_template( $args['shortcodes'] ),
			];
			if ( isset( $args['post_types'] ) ) {
				$reg_args['post_types'] = $args['post_types'];
			}
			register_block_template( 'mvp-docs//' . $slug, $reg_args );
		}
	} );

	// Tell WordPress to use our search-mvp_doc block template for the search page.
	add_filter( 'pre_get_block_file_template', function ( $template, $id, $template_type ) {
		if ( 'wp_template' !== $template_type || ! get_query_var( 'mvpd_search' ) ) {
			return $template;
		}

		$block_template = get_block_template( 'mvp-docs//search-mvp_doc' );
		return $block_template ?: $template;
	}, 10, 3 );

	// Insert our template slug at the top of the hierarchy so WordPress resolves it.
	add_filter( 'archive_template_hierarchy', function ( $templates ) {
		if ( get_query_var( 'mvpd_search' ) ) {
			array_unshift( $templates, 'search-mvp_doc' );
		}
		return $templates;
	} );
} else {
	add_filter( 'template_include', function ( $template ) {
		if ( is_singular( 'mvp_doc' ) ) {
			$override = MVPD_PATH . 'templates/classic/single-mvp_doc.php';
			if ( file_exists( $override ) ) {
				return $override;
			}
		}

		if ( get_query_var( 'mvpd_search' ) || is_post_type_archive( 'mvp_doc' ) || is_tax( 'mvpd_category' ) ) {
			$override = MVPD_PATH . 'templates/classic/archive-mvp_doc.php';
			if ( file_exists( $override ) ) {
				return $override;
			}
		}

		return $template;
	} );
}
