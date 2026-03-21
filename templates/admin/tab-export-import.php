<?php
/**
 * Export / Import tab template.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;
?>

<h2><?php esc_html_e( 'Export', 'mvp-docs' ); ?></h2>
<p class="description"><?php esc_html_e( 'Download your docs and/or settings as a JSON file.', 'mvp-docs' ); ?></p>
<table class="form-table">
	<tr>
		<th scope="row"><?php esc_html_e( 'Include', 'mvp-docs' ); ?></th>
		<td>
			<fieldset>
				<label><input type="checkbox" id="mvpd-export-docs" checked /> <?php esc_html_e( 'Docs & Categories', 'mvp-docs' ); ?></label><br />
				<label><input type="checkbox" id="mvpd-export-settings" checked /> <?php esc_html_e( 'Settings', 'mvp-docs' ); ?></label>
			</fieldset>
		</td>
	</tr>
</table>
<p>
	<button type="button" class="button button-primary" id="mvpd-export-btn"><?php esc_html_e( 'Download Export File', 'mvp-docs' ); ?></button>
</p>

<hr />

<h2><?php esc_html_e( 'Import', 'mvp-docs' ); ?></h2>
<p class="description"><?php esc_html_e( 'Upload a previously exported JSON file. Existing docs with the same title will be skipped.', 'mvp-docs' ); ?></p>
<table class="form-table">
	<tr>
		<th scope="row"><label for="mvpd-import-file"><?php esc_html_e( 'File', 'mvp-docs' ); ?></label></th>
		<td><input type="file" id="mvpd-import-file" accept=".json" /></td>
	</tr>
</table>
<p>
	<button type="button" class="button button-primary" id="mvpd-import-btn" disabled><?php esc_html_e( 'Import', 'mvp-docs' ); ?></button>
	<span id="mvpd-import-status"></span>
</p>
