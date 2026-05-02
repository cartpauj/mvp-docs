<?php
/**
 * Plugin Name: MVP Docs
 * Plugin URI:  https://github.com/cartpauj/mvp-docs
 * Description: A minimum viable documentation plugin. Lightweight docs CPT with categories, markdown import, and just enough settings to be useful.
 * Version:     1.1.1
 * Author:      cartpauj
 * Author URI:  https://github.com/cartpauj
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mvp-docs
 * Requires at least: 6.7
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'MVPD_URL', plugin_dir_url( __FILE__ ) );
define( 'MVPD_PATH', plugin_dir_path( __FILE__ ) );
define( 'MVPD_VERSION', get_file_data( __FILE__, [ 'Version' => 'Version' ] )['Version'] );

// Core includes.
require_once MVPD_PATH . 'includes/Settings.php';
require_once MVPD_PATH . 'includes/PostType.php';
require_once MVPD_PATH . 'includes/Templates.php';
require_once MVPD_PATH . 'includes/Shortcodes.php';
require_once MVPD_PATH . 'includes/Breadcrumbs.php';
require_once MVPD_PATH . 'includes/FrontAssets.php';
require_once MVPD_PATH . 'includes/Markdown.php';

if ( is_admin() ) {
	require_once MVPD_PATH . 'includes/Admin.php';
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MVPD_PATH . 'includes/CLI.php';
}

// Activation/deactivation.
register_activation_hook( __FILE__, function () {
	require_once MVPD_PATH . 'includes/Settings.php';
	require_once MVPD_PATH . 'includes/PostType.php';
	mvpd_register_types();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );
