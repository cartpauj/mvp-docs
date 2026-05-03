( function () {
	var exportBtn      = document.getElementById( 'mvpd-export-btn' );
	var exportProgress = document.getElementById( 'mvpd-export-progress' );
	var importBtn      = document.getElementById( 'mvpd-import-btn' );
	var importFile     = document.getElementById( 'mvpd-import-file' );
	var status         = document.getElementById( 'mvpd-import-status' );

	if ( ! exportBtn || ! importBtn ) return;

	function ajax( params, opts ) {
		opts = opts || {};
		return new Promise( function ( resolve, reject ) {
			var xhr = new XMLHttpRequest();
			xhr.open( 'POST', mvpdExportImport.ajaxUrl );
			if ( opts.responseType ) xhr.responseType = opts.responseType;
			xhr.onload = function () {
				if ( xhr.status !== 200 ) return reject( new Error( 'HTTP ' + xhr.status ) );
				if ( opts.responseType === 'blob' ) return resolve( xhr.response );
				try {
					var res = JSON.parse( xhr.responseText );
					if ( res.success ) resolve( res.data );
					else reject( new Error( res.data || 'Request failed.' ) );
				} catch ( e ) {
					reject( new Error( 'Invalid response.' ) );
				}
			};
			xhr.onerror = function () { reject( new Error( 'Network error.' ) ); };

			var form;
			if ( params instanceof FormData ) {
				form = params;
			} else {
				form = new FormData();
				Object.keys( params ).forEach( function ( k ) { form.append( k, params[k] ); } );
			}
			form.append( 'nonce', mvpdExportImport.nonce );
			xhr.send( form );
		} );
	}

	function downloadBlob( blob, filename ) {
		var a   = document.createElement( 'a' );
		var url = URL.createObjectURL( blob );
		a.href     = url;
		a.download = filename;
		a.click();
		URL.revokeObjectURL( url );
	}

	/* ── Export ── */

	exportBtn.addEventListener( 'click', function () {
		var docs     = document.getElementById( 'mvpd-export-docs' ).checked;
		var settings = document.getElementById( 'mvpd-export-settings' ).checked;
		var images   = document.getElementById( 'mvpd-export-images' ).checked;

		if ( ! docs && ! settings ) {
			alert( 'Select at least one option to export.' );
			return;
		}
		if ( images && ! docs ) {
			alert( 'Image bundle requires "Docs & Categories" to be selected.' );
			return;
		}

		exportBtn.disabled    = true;
		exportBtn.textContent = 'Exporting…';
		exportProgress.textContent = '';

		if ( ! images ) {
			// Single-shot JSON.
			ajax( { action: 'mvpd_export', docs: docs ? '1' : '0', settings: settings ? '1' : '0' }, { responseType: 'blob' } )
				.then( function ( blob ) { downloadBlob( blob, 'mvp-docs-export.json' ); } )
				.catch( function ( e ) { alert( 'Export failed: ' + e.message ); } )
				.finally( function () {
					exportBtn.disabled    = false;
					exportBtn.textContent = 'Download Export File';
				} );
			return;
		}

		// Chunked zip flow.
		var jobId = '';
		ajax( { action: 'mvpd_export_start', docs: '1', settings: settings ? '1' : '0' } )
			.then( function ( data ) {
				jobId = data.job_id;
				return processExportChunks( jobId, data.total );
			} )
			.then( function () {
				return ajax( { action: 'mvpd_export_finish', job_id: jobId } );
			} )
			.then( function ( data ) {
				exportProgress.textContent = 'Downloading…';
				window.location = data.download_url;
				setTimeout( function () { exportProgress.textContent = ''; }, 3000 );
			} )
			.catch( function ( e ) {
				exportProgress.textContent = '';
				alert( 'Export failed: ' + e.message );
			} )
			.finally( function () {
				exportBtn.disabled    = false;
				exportBtn.textContent = 'Download Export File';
			} );
	} );

	function processExportChunks( jobId, total ) {
		if ( total === 0 ) {
			exportProgress.textContent = 'Building archive…';
			return Promise.resolve();
		}
		return new Promise( function ( resolve, reject ) {
			function next() {
				ajax( { action: 'mvpd_export_chunk', job_id: jobId } )
					.then( function ( data ) {
						exportProgress.textContent = 'Bundling images: ' + data.processed + ' / ' + data.total;
						if ( data.done ) resolve();
						else next();
					} )
					.catch( reject );
			}
			next();
		} );
	}

	/* ── Import ── */

	importFile.addEventListener( 'change', function () {
		importBtn.disabled = ! this.files.length;
	} );

	importBtn.addEventListener( 'click', function () {
		var file = importFile.files[0];
		if ( ! file ) return;

		importBtn.disabled    = true;
		importBtn.textContent = 'Importing…';
		status.textContent    = '';
		status.style.color    = '';

		var isZip = /\.zip$/i.test( file.name );

		if ( ! isZip ) {
			// JSON single-shot.
			var reader = new FileReader();
			reader.onload = function ( e ) {
				ajax( { action: 'mvpd_import', data: e.target.result } )
					.then( function ( msg ) {
						status.textContent = msg;
						status.style.color = '#00a32a';
					} )
					.catch( function ( e ) {
						status.textContent = 'Import failed: ' + e.message;
						status.style.color = '#d63638';
					} )
					.finally( function () {
						importBtn.disabled    = false;
						importBtn.textContent = 'Import';
					} );
			};
			reader.readAsText( file );
			return;
		}

		// Zip chunked flow.
		var fd = new FormData();
		fd.append( 'action', 'mvpd_import_zip_start' );
		fd.append( 'file', file );

		var jobId = '';
		ajax( fd )
			.then( function ( data ) {
				jobId = data.job_id;
				status.textContent = 'Imported settings &amp; categories. Processing ' + data.total + ' docs…';
				return processImportChunks( jobId, data.total );
			} )
			.then( function ( finalSummary ) {
				status.textContent = finalSummary || 'Done.';
				status.style.color = '#00a32a';
				return ajax( { action: 'mvpd_import_zip_finish', job_id: jobId } );
			} )
			.catch( function ( e ) {
				status.textContent = 'Import failed: ' + e.message;
				status.style.color = '#d63638';
				if ( jobId ) ajax( { action: 'mvpd_import_zip_finish', job_id: jobId } ).catch( function () {} );
			} )
			.finally( function () {
				importBtn.disabled    = false;
				importBtn.textContent = 'Import';
			} );
	} );

	function processImportChunks( jobId, total ) {
		if ( total === 0 ) return Promise.resolve( '' );
		return new Promise( function ( resolve, reject ) {
			function next() {
				ajax( { action: 'mvpd_import_zip_chunk', job_id: jobId } )
					.then( function ( data ) {
						status.textContent = 'Importing docs: ' + data.processed + ' / ' + data.total;
						if ( data.done ) resolve( data.summary );
						else next();
					} )
					.catch( reject );
			}
			next();
		} );
	}
} )();
