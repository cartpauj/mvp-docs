<?php
/**
 * Markdown import — editor asset enqueuing.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

// Enqueue editor sidebar assets.
add_action( 'enqueue_block_editor_assets', function () {
	$screen = get_current_screen();

	if ( ! $screen || 'mvp_doc' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script( 'mvpd-marked', MVPD_URL . 'assets/editor/marked.min.js', [], MVPD_VERSION, true );

	wp_enqueue_script(
		'mvpd-md-import',
		MVPD_URL . 'assets/editor/md-import.js',
		[ 'wp-blocks', 'wp-data', 'wp-element', 'wp-components', 'wp-plugins', 'wp-editor', 'mvpd-marked' ],
		MVPD_VERSION,
		true
	);

	wp_enqueue_style( 'mvpd-md-import', MVPD_URL . 'assets/editor/md-import.css', [], MVPD_VERSION );
} );
