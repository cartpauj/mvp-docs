<?php
/**
 * Single category archive content — rendered via [mvpd_category] shortcode.
 * Shows breadcrumbs and a full-width card listing all docs in the category.
 *
 * @package MVP_Docs
 * @var WP_Term  $term     The current category term.
 * @var WP_Query $cat_docs Query of docs in this category.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="mvpd-archive">
	<nav class="mvpd-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'mvp-docs' ); ?>">
		<a href="<?php echo esc_url( get_post_type_archive_link( 'mvp_doc' ) ); ?>"><?php esc_html_e( 'Docs', 'mvp-docs' ); ?></a>
		<span class="mvpd-crumb-sep">/</span>
		<span class="mvpd-crumb-current"><?php echo esc_html( $term->name ); ?></span>
	</nav>

	<div class="mvpd-search" role="search">
		<svg class="mvpd-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		<input type="text" class="mvpd-search-input" placeholder="<?php esc_attr_e( 'Search docs…', 'mvp-docs' ); ?>" autocomplete="off" />
		<button type="button" class="mvpd-search-btn" aria-label="<?php esc_attr_e( 'Search', 'mvp-docs' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		</button>
		<div class="mvpd-search-dropdown"></div>
	</div>

	<div class="mvpd-grid mvpd-grid--single">
		<div class="mvpd-card">
			<div class="mvpd-card-header">
				<div class="mvpd-card-header-top">
					<h2 class="mvpd-card-title"><?php echo esc_html( $term->name ); ?></h2>
					<span class="mvpd-card-badge"><?php echo esc_html( $cat_docs->found_posts ); ?></span>
				</div>
				<?php if ( $term->description ) : ?>
					<p class="mvpd-card-desc"><?php echo esc_html( $term->description ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $cat_docs->have_posts() ) : ?>
				<ul class="mvpd-card-list">
					<?php while ( $cat_docs->have_posts() ) : $cat_docs->the_post(); ?>
						<li>
							<a href="<?php the_permalink(); ?>">
								<svg class="mvpd-card-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
								<span class="mvpd-card-link-text"><?php echo esc_html( get_the_title() ); ?></span>
								<svg class="mvpd-card-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php else : ?>
				<p class="mvpd-card-empty"><?php esc_html_e( 'No docs in this category yet.', 'mvp-docs' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
