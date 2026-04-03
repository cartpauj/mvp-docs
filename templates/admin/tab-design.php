<?php
/**
 * Design tab template.
 *
 * @package MVP_Docs
 * @var array $s Current settings.
 */

defined( 'ABSPATH' ) || exit;
?>
<form method="post" action="options.php">
	<?php settings_fields( 'mvpd_settings' ); ?>

	<h2><?php esc_html_e( 'Archive Header', 'mvp-docs' ); ?></h2>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="mvpd-archive-title"><?php esc_html_e( 'Title', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[archive_title]" id="mvpd-archive-title" value="<?php echo esc_attr( $s['archive_title'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-archive-subtitle"><?php esc_html_e( 'Subtitle', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[archive_subtitle]" id="mvpd-archive-subtitle" value="<?php echo esc_attr( $s['archive_subtitle'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-category-title"><?php esc_html_e( 'Category Page Title', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[category_title]" id="mvpd-category-title" value="<?php echo esc_attr( $s['category_title'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-search-title"><?php esc_html_e( 'Search Page Title', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[search_title]" id="mvpd-search-title" value="<?php echo esc_attr( $s['search_title'] ); ?>" class="regular-text" /></td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Archive Layout', 'mvp-docs' ); ?></h2>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="mvpd-columns"><?php esc_html_e( 'Columns', 'mvp-docs' ); ?></label></th>
			<td>
				<select name="mvpd_settings[columns]" id="mvpd-columns">
					<?php foreach ( [ '1', '2', '3', '4' ] as $mvpd_n ) : ?>
						<option value="<?php echo esc_attr( $mvpd_n ); ?>" <?php selected( $s['columns'], $mvpd_n ); ?>><?php echo esc_html( $mvpd_n ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-radius"><?php esc_html_e( 'Card Border Radius', 'mvp-docs' ); ?></label></th>
			<td>
				<input type="number" name="mvpd_settings[border_radius]" id="mvpd-radius" value="<?php echo esc_attr( $s['border_radius'] ); ?>" min="0" max="24" class="small-text" /> px
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-per-cat"><?php esc_html_e( 'Docs Per Category', 'mvp-docs' ); ?></label></th>
			<td>
				<input type="number" name="mvpd_settings[docs_per_category]" id="mvpd-per-cat" value="<?php echo esc_attr( $s['docs_per_category'] ); ?>" min="1" max="50" class="small-text" />
				<p class="description"><?php esc_html_e( 'Max docs shown per card. A "View all" link appears when there are more.', 'mvp-docs' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Sort Docs By', 'mvp-docs' ); ?></th>
			<td>
				<select name="mvpd_settings[sort_by]" id="mvpd-sort-by">
					<option value="title" <?php selected( $s['sort_by'], 'title' ); ?>><?php esc_html_e( 'Alphabetically', 'mvp-docs' ); ?></option>
					<option value="date" <?php selected( $s['sort_by'], 'date' ); ?>><?php esc_html_e( 'Created Date', 'mvp-docs' ); ?></option>
					<option value="modified" <?php selected( $s['sort_by'], 'modified' ); ?>><?php esc_html_e( 'Updated Date', 'mvp-docs' ); ?></option>
				</select>
				<select name="mvpd_settings[sort_order]" id="mvpd-sort-order">
					<option value="asc" <?php selected( $s['sort_order'], 'asc' ); ?>><?php esc_html_e( 'Ascending', 'mvp-docs' ); ?></option>
					<option value="desc" <?php selected( $s['sort_order'], 'desc' ); ?>><?php esc_html_e( 'Descending', 'mvp-docs' ); ?></option>
				</select>
			</td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Colors', 'mvp-docs' ); ?></h2>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="mvpd-card-bg"><?php esc_html_e( 'Card Background', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[card_bg]" id="mvpd-card-bg" value="<?php echo esc_attr( $s['card_bg'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-card-border"><?php esc_html_e( 'Card Border', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[card_border]" id="mvpd-card-border" value="<?php echo esc_attr( $s['card_border'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-header-bg"><?php esc_html_e( 'Card Header Background', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[header_bg]" id="mvpd-header-bg" value="<?php echo esc_attr( $s['header_bg'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-title-color"><?php esc_html_e( 'Category Title', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[title_color]" id="mvpd-title-color" value="<?php echo esc_attr( $s['title_color'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-link-color"><?php esc_html_e( 'Link Color', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[link_color]" id="mvpd-link-color" value="<?php echo esc_attr( $s['link_color'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-link-hover"><?php esc_html_e( 'Link Hover', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[link_hover]" id="mvpd-link-hover" value="<?php echo esc_attr( $s['link_hover'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-badge-bg"><?php esc_html_e( 'Badge Background', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[badge_bg]" id="mvpd-badge-bg" value="<?php echo esc_attr( $s['badge_bg'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-badge-color"><?php esc_html_e( 'Badge Text', 'mvp-docs' ); ?></label></th>
			<td><input type="text" name="mvpd_settings[badge_color]" id="mvpd-badge-color" value="<?php echo esc_attr( $s['badge_color'] ); ?>" class="mvpd-color-field" /></td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>
