<?php
/**
 * Settings page template.
 *
 * @package MVP_Docs
 * @var string $tab      Current tab slug.
 * @var array  $s        Current settings.
 * @var string $base_url Settings page base URL.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Docs Settings', 'mvp-docs' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="<?php echo esc_url( $base_url . '&tab=design' ); ?>" class="nav-tab <?php echo 'design' === $tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Design', 'mvp-docs' ); ?>
		</a>
		<a href="<?php echo esc_url( $base_url . '&tab=order' ); ?>" class="nav-tab <?php echo 'order' === $tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Category Order', 'mvp-docs' ); ?>
		</a>
		<a href="<?php echo esc_url( $base_url . '&tab=permalinks' ); ?>" class="nav-tab <?php echo 'permalinks' === $tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Permalinks', 'mvp-docs' ); ?>
		</a>
		<a href="<?php echo esc_url( $base_url . '&tab=export-import' ); ?>" class="nav-tab <?php echo 'export-import' === $tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Import / Export', 'mvp-docs' ); ?>
		</a>
	</nav>

	<?php if ( 'order' === $tab ) : ?>
		<?php mvpd_render_category_order_tab(); ?>
	<?php elseif ( 'permalinks' === $tab ) : ?>
		<?php include MVPD_PATH . 'templates/admin/tab-permalinks.php'; ?>
	<?php elseif ( 'export-import' === $tab ) : ?>
		<?php include MVPD_PATH . 'templates/admin/tab-export-import.php'; ?>
	<?php else : ?>
		<?php include MVPD_PATH . 'templates/admin/tab-design.php'; ?>
	<?php endif; ?>
</div>
