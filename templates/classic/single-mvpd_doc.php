<?php
/**
 * Classic theme template for single docs.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<div class="mvpd-single-doc">
		<h1><?php echo esc_html( get_the_title() ); ?></h1>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>
<?php endwhile; ?>

<?php get_footer(); ?>
