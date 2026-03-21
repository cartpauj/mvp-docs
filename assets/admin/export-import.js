( function () {
	var exportBtn  = document.getElementById( 'mvpd-export-btn' );
	var importBtn  = document.getElementById( 'mvpd-import-btn' );
	var importFile = document.getElementById( 'mvpd-import-file' );
	var status     = document.getElementById( 'mvpd-import-status' );

	if ( ! exportBtn || ! importBtn ) return;

	/* ── Export ── */

	exportBtn.addEventListener( 'click', function () {
		var docs     = document.getElementById( 'mvpd-export-docs' ).checked;
		var settings = document.getElementById( 'mvpd-export-settings' ).checked;

		if ( ! docs && ! settings ) {
			alert( 'Select at least one option to export.' );
			return;
		}

		exportBtn.disabled = true;
		exportBtn.textContent = 'Exporting…';

		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', mvpdExportImport.ajaxUrl );
		xhr.responseType = 'blob';

		var form = new FormData();
		form.append( 'action', 'mvpd_export' );
		form.append( 'nonce', mvpdExportImport.nonce );
		form.append( 'docs', docs ? '1' : '0' );
		form.append( 'settings', settings ? '1' : '0' );

		xhr.onload = function () {
			if ( xhr.status === 200 ) {
				var a    = document.createElement( 'a' );
				var url  = URL.createObjectURL( xhr.response );
				a.href   = url;
				a.download = 'mvp-docs-export.json';
				a.click();
				URL.revokeObjectURL( url );
			} else {
				alert( 'Export failed.' );
			}
			exportBtn.disabled = false;
			exportBtn.textContent = 'Download Export File';
		};

		xhr.onerror = function () {
			alert( 'Export failed.' );
			exportBtn.disabled = false;
			exportBtn.textContent = 'Download Export File';
		};

		xhr.send( form );
	} );

	/* ── Import ── */

	importFile.addEventListener( 'change', function () {
		importBtn.disabled = ! this.files.length;
	} );

	importBtn.addEventListener( 'click', function () {
		var file = importFile.files[0];
		if ( ! file ) return;

		importBtn.disabled = true;
		importBtn.textContent = 'Importing…';
		status.textContent = '';

		var reader = new FileReader();
		reader.onload = function ( e ) {
			var xhr  = new XMLHttpRequest();
			var form = new FormData();

			xhr.open( 'POST', mvpdExportImport.ajaxUrl );
			form.append( 'action', 'mvpd_import' );
			form.append( 'nonce', mvpdExportImport.nonce );
			form.append( 'data', e.target.result );

			xhr.onload = function () {
				var res;
				try {
					res = JSON.parse( xhr.responseText );
				} catch ( err ) {
					status.textContent = 'Import failed: invalid response.';
					importBtn.disabled = false;
					importBtn.textContent = 'Import';
					return;
				}

				if ( res.success ) {
					status.textContent = res.data;
					status.style.color = '#00a32a';
				} else {
					status.textContent = res.data || 'Import failed.';
					status.style.color = '#d63638';
				}
				importBtn.disabled = false;
				importBtn.textContent = 'Import';
			};

			xhr.onerror = function () {
				status.textContent = 'Import failed.';
				status.style.color = '#d63638';
				importBtn.disabled = false;
				importBtn.textContent = 'Import';
			};

			xhr.send( form );
		};

		reader.readAsText( file );
	} );
} )();
