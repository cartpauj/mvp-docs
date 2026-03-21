<?php
/**
 * Markdown import — AJAX handler and editor asset enqueuing.
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

	wp_enqueue_script(
		'mvpd-md-import',
		MVPD_URL . 'assets/editor/md-import.js',
		[ 'wp-blocks', 'wp-data', 'wp-element', 'wp-components', 'wp-plugins', 'wp-editor' ],
		MVPD_VERSION,
		true
	);

	wp_localize_script( 'mvpd-md-import', 'mvpdImport', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'mvpd_parse_markdown' ),
	] );

	wp_enqueue_style( 'mvpd-md-import', MVPD_URL . 'assets/editor/md-import.css', [], MVPD_VERSION );
} );

// AJAX: convert markdown to HTML.
add_action( 'wp_ajax_mvpd_parse_markdown', function () {
	check_ajax_referer( 'mvpd_parse_markdown', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Unauthorized.', 403 );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw markdown, sanitized by commonmark parser.
	$markdown = isset( $_POST['markdown'] ) ? wp_unslash( $_POST['markdown'] ) : '';

	if ( empty( $markdown ) ) {
		wp_send_json_error( 'No markdown content provided.' );
	}

	if ( strlen( $markdown ) > 2 * 1024 * 1024 ) {
		wp_send_json_error( 'Content too large.' );
	}

	if ( ! class_exists( \MvpDocs\Vendor\League\CommonMark\GithubFlavoredMarkdownConverter::class ) ) {
		wp_send_json_error( 'Markdown parser not available. Run composer install.' );
	}

	$converter = new \MvpDocs\Vendor\League\CommonMark\GithubFlavoredMarkdownConverter( [
		'html_input'         => 'strip',
		'allow_unsafe_links' => false,
	] );

	$html = $converter->convert( $markdown )->getContent();

	wp_send_json_success( [ 'html' => $html ] );
} );
