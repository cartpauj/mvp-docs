<?php
/**
 * Admin — settings pages, menus, and admin asset enqueuing.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

// Register settings.
add_action( 'admin_init', function () {
	register_setting( 'mvpd_settings', 'mvpd_settings', [
		'type'              => 'array',
		'sanitize_callback' => 'mvpd_sanitize_settings',
		'default'           => mvpd_default_settings(),
	] );
} );

// Add settings submenu.
add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=mvpd_doc',
		__( 'Settings', 'mvp-docs' ),
		__( 'Settings', 'mvp-docs' ),
		'manage_options',
		'mvpd-settings',
		'mvpd_render_settings_page'
	);
} );

// Enqueue admin assets on settings page.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'mvpd_doc_page_mvpd-settings' !== $hook ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab display only.
	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'design';

	// Shared settings page styles.
	wp_enqueue_style( 'mvpd-settings', MVPD_URL . 'assets/admin/settings.css', [], MVPD_VERSION );

	if ( 'order' === $tab ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'mvpd-cat-order', MVPD_URL . 'assets/admin/cat-order.js', [ 'jquery-ui-sortable' ], MVPD_VERSION, true );
		wp_localize_script( 'mvpd-cat-order', 'mvpdOrder', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'mvpd_settings' ),
		] );
		wp_enqueue_style( 'mvpd-cat-order', MVPD_URL . 'assets/admin/cat-order.css', [], MVPD_VERSION );
	} elseif ( 'design' === $tab ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'mvpd-design', MVPD_URL . 'assets/admin/settings.js', [ 'wp-color-picker' ], MVPD_VERSION, true );
	} elseif ( 'permalinks' === $tab ) {
		wp_enqueue_script( 'mvpd-permalinks', MVPD_URL . 'assets/admin/settings.js', [], MVPD_VERSION, true );
	} elseif ( 'export-import' === $tab ) {
		wp_enqueue_script( 'mvpd-export-import', MVPD_URL . 'assets/admin/export-import.js', [], MVPD_VERSION, true );
		wp_localize_script( 'mvpd-export-import', 'mvpdExportImport', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'mvpd_export_import' ),
		] );
	}
} );

// AJAX: save category order.
add_action( 'wp_ajax_mvpd_save_category_order', function () {
	check_ajax_referer( 'mvpd_settings', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array of IDs, sanitized below.
	$order = isset( $_POST['order'] ) ? wp_unslash( $_POST['order'] ) : [];

	if ( ! is_array( $order ) ) {
		wp_send_json_error( __( 'Invalid data.', 'mvp-docs' ) );
	}

	$clean = array_filter( array_map( 'absint', $order ) );
	update_option( 'mvpd_category_order', $clean, false );
	wp_send_json_success();
} );

/**
 * Render the settings page with tabs.
 */
function mvpd_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab display only.
	$tab      = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'design';
	$s        = mvpd_get_settings();
	$base_url = admin_url( 'edit.php?post_type=mvpd_doc&page=mvpd-settings' );

	include MVPD_PATH . 'templates/admin/settings-page.php';
}

/**
 * Render the Category Order tab content.
 */
function mvpd_render_category_order_tab(): void {
	$categories = get_terms( [
		'taxonomy'   => 'mvpd_category',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		echo '<p class="mvpd-tab-intro">' . esc_html__( 'No categories yet. Create some under MVP Docs > Doc Categories.', 'mvp-docs' ) . '</p>';
		return;
	}

	$saved_order = get_option( 'mvpd_category_order', [] );
	if ( ! empty( $saved_order ) ) {
		$order_map = array_flip( array_map( 'intval', $saved_order ) );
		usort( $categories, function ( $a, $b ) use ( $order_map ) {
			return ( $order_map[ $a->term_id ] ?? 999 ) - ( $order_map[ $b->term_id ] ?? 999 );
		} );
	}

	include MVPD_PATH . 'templates/admin/tab-order.php';
}

// AJAX: export as a single JSON file (no images).
add_action( 'wp_ajax_mvpd_export', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	$include_docs     = ! empty( $_POST['docs'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['docs'] ) );
	$include_settings = ! empty( $_POST['settings'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['settings'] ) );

	$export = mvpd_build_export_array( $include_docs, $include_settings );

	header( 'Content-Type: application/json' );
	header( 'Content-Disposition: attachment; filename=mvp-docs-export.json' );
	echo wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	exit;
} );

/**
 * AJAX: start an image-bundle export. Builds the zip with export.json and the
 * image manifest, returns a job ID so the client can chunk through images.
 */
add_action( 'wp_ajax_mvpd_export_start', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_send_json_error( __( 'Image bundle export requires the PHP ZipArchive extension, which is not available on this server.', 'mvp-docs' ) );
	}

	$include_docs     = ! empty( $_POST['docs'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['docs'] ) );
	$include_settings = ! empty( $_POST['settings'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['settings'] ) );

	$export = mvpd_build_export_array( $include_docs, $include_settings );
	$urls   = $include_docs ? mvpd_collect_image_urls( $export ) : [];

	$job_id = wp_generate_password( 16, false );
	$dir    = mvpd_temp_dir( $job_id );
	if ( ! $dir ) {
		wp_send_json_error( __( 'Could not create temporary directory.', 'mvp-docs' ) );
	}

	$zip_path = $dir . 'mvp-docs-export.zip';
	$zip      = new ZipArchive();
	if ( true !== $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
		wp_send_json_error( __( 'Could not create zip archive.', 'mvp-docs' ) );
	}
	$zip->addFromString( 'export.json', wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
	$zip->close();

	set_transient( 'mvpd_export_' . $job_id, [
		'zip_path' => $zip_path,
		'urls'     => $urls,
		'index'    => 0,
	], HOUR_IN_SECONDS );

	wp_send_json_success( [
		'job_id' => $job_id,
		'total'  => count( $urls ),
	] );
} );

/**
 * AJAX: append the next batch of images to the export zip.
 */
add_action( 'wp_ajax_mvpd_export_chunk', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	$job_id = isset( $_POST['job_id'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', wp_unslash( $_POST['job_id'] ) ) : '';
	$state  = $job_id ? get_transient( 'mvpd_export_' . $job_id ) : false;
	if ( ! $state ) {
		wp_send_json_error( __( 'Export session expired or not found.', 'mvp-docs' ) );
	}

	$batch = 5;
	$urls  = $state['urls'];
	$start = (int) $state['index'];
	$end   = min( $start + $batch, count( $urls ) );

	$zip = new ZipArchive();
	if ( true !== $zip->open( $state['zip_path'] ) ) {
		wp_send_json_error( __( 'Could not open zip archive.', 'mvp-docs' ) );
	}

	for ( $i = $start; $i < $end; $i++ ) {
		$url   = $urls[ $i ];
		$path  = mvpd_path_for_upload_url( $url );
		$entry = mvpd_zip_entry_for_url( $url );
		if ( $path && $entry && ! $zip->locateName( $entry ) ) {
			$zip->addFile( $path, $entry );
		}
	}
	$zip->close();

	$state['index'] = $end;
	set_transient( 'mvpd_export_' . $job_id, $state, HOUR_IN_SECONDS );

	wp_send_json_success( [
		'processed' => $end,
		'total'     => count( $urls ),
		'done'      => $end >= count( $urls ),
	] );
} );

/**
 * AJAX: finalize an export — issues a one-time download token.
 */
add_action( 'wp_ajax_mvpd_export_finish', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	$job_id = isset( $_POST['job_id'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', wp_unslash( $_POST['job_id'] ) ) : '';
	$state  = $job_id ? get_transient( 'mvpd_export_' . $job_id ) : false;
	if ( ! $state ) {
		wp_send_json_error( __( 'Export session expired or not found.', 'mvp-docs' ) );
	}

	$token = wp_generate_password( 24, false );
	set_transient( 'mvpd_export_dl_' . $token, $job_id, 10 * MINUTE_IN_SECONDS );

	wp_send_json_success( [
		'download_url' => add_query_arg( [
			'action' => 'mvpd_export_download',
			'token'  => $token,
		], admin_url( 'admin-ajax.php' ) ),
	] );
} );

/**
 * AJAX: stream the finished zip and clean up the job dir.
 */
add_action( 'wp_ajax_mvpd_export_download', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	$token  = isset( $_GET['token'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', wp_unslash( $_GET['token'] ) ) : '';
	$job_id = $token ? get_transient( 'mvpd_export_dl_' . $token ) : false;
	if ( ! $job_id ) {
		wp_die( esc_html__( 'Download link expired.', 'mvp-docs' ), 403 );
	}
	$state = get_transient( 'mvpd_export_' . $job_id );
	if ( ! $state || empty( $state['zip_path'] ) || ! file_exists( $state['zip_path'] ) ) {
		wp_die( esc_html__( 'Export not found.', 'mvp-docs' ), 404 );
	}

	$zip_path = $state['zip_path'];

	delete_transient( 'mvpd_export_dl_' . $token );

	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename=mvp-docs-export.zip' );
	header( 'Content-Length: ' . filesize( $zip_path ) );
	readfile( $zip_path );

	delete_transient( 'mvpd_export_' . $job_id );
	mvpd_rmdir_recursive( dirname( $zip_path ) );
	exit;
} );

/**
 * Build the human-readable summary from import counters.
 */
function mvpd_import_summary( bool $settings, int $cats, int $docs, int $skipped ): string {
	$parts = [];
	if ( $settings ) {
		$parts[] = __( 'settings imported', 'mvp-docs' );
	}
	if ( $cats ) {
		/* translators: %d: number of categories */
		$parts[] = sprintf( _n( '%d category', '%d categories', $cats, 'mvp-docs' ), $cats );
	}
	if ( $docs ) {
		/* translators: %d: number of docs */
		$parts[] = sprintf( _n( '%d doc', '%d docs', $docs, 'mvp-docs' ), $docs );
	}
	if ( $skipped ) {
		/* translators: %d: number of skipped docs */
		$parts[] = sprintf( _n( '%d doc skipped (already exists)', '%d docs skipped (already exist)', $skipped, 'mvp-docs' ), $skipped );
	}
	return $parts ? implode( ', ', $parts ) . '.' : __( 'Nothing to import.', 'mvp-docs' );
}

// AJAX: import a JSON export (no images).
add_action( 'wp_ajax_mvpd_import', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string; sanitized after json_decode below.
	$raw  = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
	$data = is_string( $raw ) ? json_decode( $raw, true ) : null;

	if ( ! is_array( $data ) || empty( $data['version'] ) ) {
		wp_send_json_error( __( 'Invalid export file.', 'mvp-docs' ) );
	}

	$imported_settings = mvpd_import_settings_and_order( $data );
	$imported_cats     = mvpd_import_categories( $data );
	$imported_docs     = 0;
	$skipped_docs      = 0;

	if ( ! empty( $data['docs'] ) && is_array( $data['docs'] ) ) {
		$map = [];
		foreach ( $data['docs'] as $doc ) {
			$res = mvpd_import_one_doc( $doc, '', $map );
			if ( $res === 'imported' ) {
				$imported_docs++;
			} elseif ( $res === 'skipped' ) {
				$skipped_docs++;
			}
		}
	}

	wp_send_json_success( mvpd_import_summary( $imported_settings, $imported_cats, $imported_docs, $skipped_docs ) );
} );

/**
 * AJAX: start a zip-bundle import. Accepts the uploaded zip file, unzips it,
 * imports settings + categories immediately, returns a job ID for the
 * client to chunk through the docs.
 */
add_action( 'wp_ajax_mvpd_import_zip_start', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_send_json_error( __( 'Image bundle import requires the PHP ZipArchive extension, which is not available on this server.', 'mvp-docs' ) );
	}

	if ( empty( $_FILES['file'] ) || empty( $_FILES['file']['tmp_name'] ) ) {
		wp_send_json_error( __( 'No file uploaded.', 'mvp-docs' ) );
	}
	if ( ! empty( $_FILES['file']['error'] ) ) {
		wp_send_json_error( __( 'Upload failed.', 'mvp-docs' ) );
	}

	$job_id = wp_generate_password( 16, false );
	$dir    = mvpd_temp_dir( $job_id );
	if ( ! $dir ) {
		wp_send_json_error( __( 'Could not create temporary directory.', 'mvp-docs' ) );
	}

	$zip_path = $dir . 'import.zip';
	if ( ! @move_uploaded_file( $_FILES['file']['tmp_name'], $zip_path ) ) {
		mvpd_rmdir_recursive( $dir );
		wp_send_json_error( __( 'Could not store uploaded file.', 'mvp-docs' ) );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $zip_path ) ) {
		mvpd_rmdir_recursive( $dir );
		wp_send_json_error( __( 'Invalid zip archive.', 'mvp-docs' ) );
	}

	$json_raw = $zip->getFromName( 'export.json' );
	if ( false === $json_raw ) {
		$zip->close();
		mvpd_rmdir_recursive( $dir );
		wp_send_json_error( __( 'Bundle is missing export.json.', 'mvp-docs' ) );
	}

	$data = json_decode( $json_raw, true );
	if ( ! is_array( $data ) || empty( $data['version'] ) ) {
		$zip->close();
		mvpd_rmdir_recursive( $dir );
		wp_send_json_error( __( 'Invalid export.json inside bundle.', 'mvp-docs' ) );
	}

	if ( ! $zip->extractTo( $dir ) ) {
		$zip->close();
		mvpd_rmdir_recursive( $dir );
		wp_send_json_error( __( 'Could not extract bundle.', 'mvp-docs' ) );
	}
	$zip->close();
	@unlink( $zip_path );

	$imported_settings = mvpd_import_settings_and_order( $data );
	$imported_cats     = mvpd_import_categories( $data );

	$docs = ! empty( $data['docs'] ) && is_array( $data['docs'] ) ? $data['docs'] : [];

	set_transient( 'mvpd_import_' . $job_id, [
		'dir'      => $dir,
		'docs'     => $docs,
		'index'    => 0,
		'url_map'  => [],
		'imported' => 0,
		'skipped'  => 0,
		'settings' => $imported_settings,
		'cats'     => $imported_cats,
	], HOUR_IN_SECONDS );

	wp_send_json_success( [
		'job_id' => $job_id,
		'total'  => count( $docs ),
	] );
} );

/**
 * AJAX: process a chunk of docs from a started import.
 */
add_action( 'wp_ajax_mvpd_import_zip_chunk', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	$job_id = isset( $_POST['job_id'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', wp_unslash( $_POST['job_id'] ) ) : '';
	$state  = $job_id ? get_transient( 'mvpd_import_' . $job_id ) : false;
	if ( ! $state ) {
		wp_send_json_error( __( 'Import session expired or not found.', 'mvp-docs' ) );
	}

	$batch = 3;
	$docs  = $state['docs'];
	$start = (int) $state['index'];
	$end   = min( $start + $batch, count( $docs ) );

	$url_map = (array) $state['url_map'];
	for ( $i = $start; $i < $end; $i++ ) {
		$res = mvpd_import_one_doc( $docs[ $i ], $state['dir'], $url_map );
		if ( $res === 'imported' ) {
			$state['imported']++;
		} elseif ( $res === 'skipped' ) {
			$state['skipped']++;
		}
	}
	$state['url_map'] = $url_map;
	$state['index']   = $end;
	set_transient( 'mvpd_import_' . $job_id, $state, HOUR_IN_SECONDS );

	$done = $end >= count( $docs );

	wp_send_json_success( [
		'processed' => $end,
		'total'     => count( $docs ),
		'done'      => $done,
		'summary'   => $done ? mvpd_import_summary( (bool) $state['settings'], (int) $state['cats'], (int) $state['imported'], (int) $state['skipped'] ) : '',
	] );
} );

/**
 * AJAX: finalize a zip import — clean up the temp dir.
 */
add_action( 'wp_ajax_mvpd_import_zip_finish', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'mvp-docs' ), 403 );
	}

	$job_id = isset( $_POST['job_id'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', wp_unslash( $_POST['job_id'] ) ) : '';
	$state  = $job_id ? get_transient( 'mvpd_import_' . $job_id ) : false;
	if ( $state && ! empty( $state['dir'] ) ) {
		mvpd_rmdir_recursive( $state['dir'] );
	}
	delete_transient( 'mvpd_import_' . $job_id );

	wp_send_json_success();
} );
