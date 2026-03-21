<?php
/**
 * Category Order tab template.
 *
 * @package MVP_Docs
 * @var array $categories Sorted term objects.
 */

defined( 'ABSPATH' ) || exit;
?>
<p class="mvpd-tab-intro"><?php esc_html_e( 'Drag to reorder how categories appear on the docs archive.', 'mvp-docs' ); ?></p>
<ul id="mvpd-cat-sortable" class="mvpd-cat-sortable">
	<?php foreach ( $categories as $cat ) : ?>
		<li data-term-id="<?php echo esc_attr( $cat->term_id ); ?>">
			<span class="dashicons dashicons-menu"></span>
			<?php echo esc_html( $cat->name ); ?>
			<span class="mvpd-cat-count"><?php echo esc_html( $cat->count ); ?> <?php esc_html_e( 'docs', 'mvp-docs' ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
<p id="mvpd-cat-order-status"></p>
