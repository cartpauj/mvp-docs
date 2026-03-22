<?php
/**
 * Docs archive content — rendered via [mvpd_archive] shortcode.
 * Groups docs by category in a card grid.
 *
 * @package MVP_Docs
 * @var array $categories Term objects from mvpd_get_ordered_categories().
 */

defined( 'ABSPATH' ) || exit;

$mvpd_settings  = mvpd_get_settings();
$mvpd_per_cat        = (int) $mvpd_settings['docs_per_category'];
$mvpd_sort_args      = mvpd_get_sort_args();
?>

<div class="mvpd-archive">

	<div class="mvpd-search" role="search">
		<svg class="mvpd-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		<input type="text" class="mvpd-search-input" placeholder="<?php esc_attr_e( 'Search docs…', 'mvp-docs' ); ?>" autocomplete="off" />
		<button type="button" class="mvpd-search-btn" aria-label="<?php esc_attr_e( 'Search', 'mvp-docs' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		</button>
		<div class="mvpd-search-dropdown"></div>
	</div>

	<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
		<div class="mvpd-grid">
			<?php foreach ( $categories as $cat ) : ?>
				<?php
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				$mvpd_cat_docs = new WP_Query( array_merge( [
					'post_type'      => 'mvp_doc',
					'posts_per_page' => $mvpd_per_cat + 1,
					'tax_query'      => [ [
						'taxonomy' => 'mvpd_category',
						'field'    => 'term_id',
						'terms'    => $cat->term_id,
					] ],
				], $mvpd_sort_args ) );

				$mvpd_has_more = $mvpd_cat_docs->post_count > $mvpd_per_cat;
				$mvpd_shown    = 0;
				?>
				<?php if ( $mvpd_cat_docs->have_posts() ) : ?>
					<div class="mvpd-card">
						<div class="mvpd-card-header">
							<div class="mvpd-card-header-top">
								<h2 class="mvpd-card-title"><?php echo esc_html( $cat->name ); ?></h2>
								<span class="mvpd-card-badge"><?php echo esc_html( $cat->count ); ?></span>
							</div>
							<?php if ( $cat->description ) : ?>
								<p class="mvpd-card-desc"><?php echo esc_html( $cat->description ); ?></p>
							<?php endif; ?>
						</div>
						<ul class="mvpd-card-list">
							<?php while ( $mvpd_cat_docs->have_posts() && $mvpd_shown < $mvpd_per_cat ) : $mvpd_cat_docs->the_post(); $mvpd_shown++; ?>
								<li>
									<a href="<?php the_permalink(); ?>">
										<svg class="mvpd-card-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
										<span class="mvpd-card-link-text"><?php echo esc_html( get_the_title() ); ?></span>
										<svg class="mvpd-card-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
									</a>
								</li>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
							<?php if ( $mvpd_has_more ) : ?>
								<li>
									<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
										<svg class="mvpd-card-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
										<span class="mvpd-card-link-text"><?php esc_html_e( 'View all', 'mvp-docs' ); ?></span>
										<svg class="mvpd-card-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	$mvpd_uncategorized = new WP_Query( array_merge( [
		'post_type'      => 'mvp_doc',
		'posts_per_page' => 200,
		'tax_query'      => [ [
			'taxonomy' => 'mvpd_category',
			'operator' => 'NOT EXISTS',
		] ],
	], $mvpd_sort_args ) );
	?>
	<?php if ( $mvpd_uncategorized->have_posts() ) : ?>
		<div class="mvpd-grid mvpd-grid--other">
			<div class="mvpd-card">
				<div class="mvpd-card-header">
					<div class="mvpd-card-header-top">
						<h2 class="mvpd-card-title"><?php esc_html_e( 'Other', 'mvp-docs' ); ?></h2>
						<span class="mvpd-card-badge"><?php echo esc_html( $mvpd_uncategorized->found_posts ); ?></span>
					</div>
				</div>
				<ul class="mvpd-card-list">
					<?php while ( $mvpd_uncategorized->have_posts() ) : $mvpd_uncategorized->the_post(); ?>
						<li>
							<a href="<?php the_permalink(); ?>">
								<svg class="mvpd-card-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
								<span class="mvpd-card-link-text"><?php echo esc_html( get_the_title() ); ?></span>
								<svg class="mvpd-card-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
							</a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ( empty( $categories ) || is_wp_error( $categories ) ) && 0 === $mvpd_uncategorized->post_count ) : ?>
		<p><?php esc_html_e( 'No documentation yet.', 'mvp-docs' ); ?></p>
	<?php endif; ?>

</div>
