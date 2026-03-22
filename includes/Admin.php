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
		'edit.php?post_type=mvp_doc',
		__( 'Settings', 'mvp-docs' ),
		__( 'Settings', 'mvp-docs' ),
		'manage_options',
		'mvpd-settings',
		'mvpd_render_settings_page'
	);
} );

// Enqueue admin assets on settings page.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'mvp_doc_page_mvpd-settings' !== $hook ) {
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
		wp_send_json_error( 'Unauthorized.', 403 );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array of IDs, sanitized below.
	$order = isset( $_POST['order'] ) ? wp_unslash( $_POST['order'] ) : [];

	if ( ! is_array( $order ) ) {
		wp_send_json_error( 'Invalid data.' );
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
	$base_url = admin_url( 'edit.php?post_type=mvp_doc&page=mvpd-settings' );

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

// AJAX: export docs and/or settings.
add_action( 'wp_ajax_mvpd_export', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized.', 403 );
	}

	$include_docs     = ! empty( $_POST['docs'] ) && '1' === $_POST['docs'];
	$include_settings = ! empty( $_POST['settings'] ) && '1' === $_POST['settings'];

	$export = [ 'version' => MVPD_VERSION ];

	if ( $include_settings ) {
		$export['settings']       = mvpd_get_settings();
		$export['category_order'] = get_option( 'mvpd_category_order', [] );
	}

	if ( $include_docs ) {
		$categories = get_terms( [
			'taxonomy'   => 'mvpd_category',
			'hide_empty' => false,
		] );

		$export['categories'] = [];
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$export['categories'][] = [
					'name'        => $cat->name,
					'slug'        => $cat->slug,
					'description' => $cat->description,
				];
			}
		}

		$docs = new WP_Query( [
			'post_type'      => 'mvp_doc',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		] );

		$export['docs'] = [];
		while ( $docs->have_posts() ) {
			$docs->the_post();
			$terms      = get_the_terms( get_the_ID(), 'mvpd_category' );
			$cat_slugs  = [];
			if ( $terms && ! is_wp_error( $terms ) ) {
				$cat_slugs = wp_list_pluck( $terms, 'slug' );
			}

			$export['docs'][] = [
				'title'      => get_the_title(),
				'slug'       => get_post_field( 'post_name' ),
				'content'    => get_the_content(),
				'excerpt'    => get_the_excerpt(),
				'status'     => get_post_status(),
				'categories' => $cat_slugs,
				'sort_order' => (int) get_post_meta( get_the_ID(), 'mvpd_sort_order', true ),
			];
		}
		wp_reset_postdata();
	}

	header( 'Content-Type: application/json' );
	header( 'Content-Disposition: attachment; filename=mvp-docs-export.json' );
	echo wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	exit;
} );

// AJAX: import docs and/or settings.
add_action( 'wp_ajax_mvpd_import', function () {
	check_ajax_referer( 'mvpd_export_import', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized.', 403 );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string; individual fields sanitized below.
	$raw = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) || empty( $data['version'] ) ) {
		wp_send_json_error( __( 'Invalid export file.', 'mvp-docs' ) );
	}

	$imported_settings   = false;
	$imported_categories = 0;
	$imported_docs       = 0;
	$skipped_docs        = 0;

	// Import settings.
	if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
		$clean = mvpd_sanitize_settings( $data['settings'] );
		update_option( 'mvpd_settings', $clean );
		$imported_settings = true;
	}

	if ( isset( $data['category_order'] ) && is_array( $data['category_order'] ) ) {
		$clean_order = array_filter( array_map( 'absint', $data['category_order'] ) );
		update_option( 'mvpd_category_order', $clean_order, false );
	}

	// Import categories.
	if ( ! empty( $data['categories'] ) && is_array( $data['categories'] ) ) {
		foreach ( $data['categories'] as $cat ) {
			if ( empty( $cat['name'] ) ) {
				continue;
			}

			$cat_slug = sanitize_title( $cat['slug'] ?? '' );
			$existing = get_term_by( 'slug', $cat_slug, 'mvpd_category' );
			if ( $existing ) {
				continue;
			}

			$result = wp_insert_term( sanitize_text_field( $cat['name'] ), 'mvpd_category', [
				'slug'        => $cat_slug,
				'description' => sanitize_text_field( $cat['description'] ?? '' ),
			] );

			if ( ! is_wp_error( $result ) ) {
				$imported_categories++;
			}
		}
	}

	// Import docs.
	if ( ! empty( $data['docs'] ) && is_array( $data['docs'] ) ) {
		foreach ( $data['docs'] as $doc ) {
			if ( empty( $doc['title'] ) ) {
				continue;
			}

			// Skip if a doc with this title already exists.
			$existing = new WP_Query( [
				'post_type'              => 'mvp_doc',
				'title'                  => $doc['title'],
				'posts_per_page'         => 1,
				'post_status'            => 'any',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			] );
			if ( $existing->have_posts() ) {
				$skipped_docs++;
				continue;
			}

			$post_id = wp_insert_post( [
				'post_type'    => 'mvp_doc',
				'post_title'   => sanitize_text_field( $doc['title'] ),
				'post_name'    => sanitize_title( $doc['slug'] ?? '' ),
				'post_content' => wp_kses_post( $doc['content'] ?? '' ),
				'post_excerpt' => sanitize_text_field( $doc['excerpt'] ?? '' ),
				'post_status'  => in_array( $doc['status'] ?? '', [ 'publish', 'draft', 'private' ], true ) ? $doc['status'] : 'draft',
			] );

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			$imported_docs++;

			if ( ! empty( $doc['sort_order'] ) ) {
				update_post_meta( $post_id, 'mvpd_sort_order', absint( $doc['sort_order'] ) );
			}

			if ( ! empty( $doc['categories'] ) && is_array( $doc['categories'] ) ) {
				$term_ids = [];
				foreach ( $doc['categories'] as $slug ) {
					$term = get_term_by( 'slug', sanitize_title( $slug ), 'mvpd_category' );
					if ( $term ) {
						$term_ids[] = $term->term_id;
					}
				}
				if ( $term_ids ) {
					wp_set_object_terms( $post_id, $term_ids, 'mvpd_category' );
				}
			}
		}
	}

	$parts = [];
	if ( $imported_settings ) {
		$parts[] = __( 'settings imported', 'mvp-docs' );
	}
	if ( $imported_categories ) {
		/* translators: %d: number of categories */
		$parts[] = sprintf( _n( '%d category', '%d categories', $imported_categories, 'mvp-docs' ), $imported_categories );
	}
	if ( $imported_docs ) {
		/* translators: %d: number of docs */
		$parts[] = sprintf( _n( '%d doc', '%d docs', $imported_docs, 'mvp-docs' ), $imported_docs );
	}
	if ( $skipped_docs ) {
		/* translators: %d: number of skipped docs */
		$parts[] = sprintf( _n( '%d doc skipped (already exists)', '%d docs skipped (already exist)', $skipped_docs, 'mvp-docs' ), $skipped_docs );
	}

	if ( empty( $parts ) ) {
		wp_send_json_success( __( 'Nothing to import.', 'mvp-docs' ) );
	}

	wp_send_json_success( implode( ', ', $parts ) . '.' );
} );
