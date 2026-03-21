<?php
/**
 * Classic theme template for docs archive, category, and search pages.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="mvpd-classic-archive">
	<?php if ( get_query_var( 'mvpd_search' ) ) : ?>
		<?php echo do_shortcode( '[mvpd_search_header]' ); ?>
		<?php echo do_shortcode( '[mvpd_search]' ); ?>
	<?php elseif ( is_tax( 'mvpd_category' ) ) : ?>
		<?php echo do_shortcode( '[mvpd_category_header]' ); ?>
		<?php echo do_shortcode( '[mvpd_category]' ); ?>
	<?php else : ?>
		<?php echo do_shortcode( '[mvpd_archive_header]' ); ?>
		<?php echo do_shortcode( '[mvpd_archive]' ); ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
