<?php
/**
 * Permalinks tab template.
 *
 * @package MVP_Docs
 * @var array $s Current settings.
 */

defined( 'ABSPATH' ) || exit;

$slug_errors = get_transient( 'mvpd_slug_errors' );
if ( $slug_errors ) {
	delete_transient( 'mvpd_slug_errors' );
} else {
	$slug_errors = [];
}
?>
<form method="post" action="options.php">
	<?php settings_fields( 'mvpd_settings' ); ?>

	<?php
	// Preserve all existing settings as hidden fields.
	foreach ( $s as $key => $val ) {
		if ( 'docs_slug' === $key || 'category_slug' === $key ) {
			continue;
		}
		printf(
			'<input type="hidden" name="mvpd_settings[%s]" value="%s" />',
			esc_attr( $key ),
			esc_attr( $val )
		);
	}
	?>

	<table class="form-table">
		<tr>
			<th scope="row"><label for="mvpd-docs-slug"><?php esc_html_e( 'Docs Slug', 'mvp-docs' ); ?></label></th>
			<td>
				<code><?php echo esc_html( home_url( '/' ) ); ?></code>
				<input type="text" name="mvpd_settings[docs_slug]" id="mvpd-docs-slug" value="<?php echo esc_attr( $s['docs_slug'] ); ?>" class="regular-text<?php echo isset( $slug_errors['docs_slug'] ) ? ' mvpd-field-error' : ''; ?>" />
				<code>/</code>
				<button type="button" class="button mvpd-copy-url" data-url="<?php echo esc_attr( home_url( '/' . $s['docs_slug'] . '/' ) ); ?>"><?php esc_html_e( 'Copy URL', 'mvp-docs' ); ?></button>
				<?php if ( isset( $slug_errors['docs_slug'] ) ) : ?>
					<p class="mvpd-inline-error"><?php echo esc_html( $slug_errors['docs_slug'] ); ?></p>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Base slug for the docs archive and individual docs.', 'mvp-docs' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mvpd-cat-slug"><?php esc_html_e( 'Category Slug', 'mvp-docs' ); ?></label></th>
			<td>
				<code><?php echo esc_html( home_url( '/' ) ); ?></code>
				<input type="text" name="mvpd_settings[category_slug]" id="mvpd-cat-slug" value="<?php echo esc_attr( $s['category_slug'] ); ?>" class="regular-text<?php echo isset( $slug_errors['category_slug'] ) ? ' mvpd-field-error' : ''; ?>" />
				<code>/</code>
				<button type="button" class="button mvpd-copy-url" data-url="<?php echo esc_attr( home_url( '/' . $s['category_slug'] . '/' ) ); ?>"><?php esc_html_e( 'Copy URL', 'mvp-docs' ); ?></button>
				<?php if ( isset( $slug_errors['category_slug'] ) ) : ?>
					<p class="mvpd-inline-error"><?php echo esc_html( $slug_errors['category_slug'] ); ?></p>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Base slug for doc category archives. Must be different from the docs slug.', 'mvp-docs' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
	</table>

	<?php submit_button( __( 'Save Permalinks', 'mvp-docs' ) ); ?>
</form>
