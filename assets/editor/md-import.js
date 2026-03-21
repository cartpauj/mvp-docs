/**
 * MVP Docs — Markdown Import button for the block editor.
 *
 * Two-stage pipeline:
 * 1. AJAX sends markdown to PHP (league/commonmark with GFM) → returns HTML
 * 2. wp.blocks.rawHandler converts HTML → native Gutenberg blocks
 */
( function () {
	var el             = wp.element.createElement;
	var useState       = wp.element.useState;
	var useEffect      = wp.element.useEffect;
	var useSelect      = wp.data.useSelect;
	var PluginDocumentSettingPanel = wp.editor.PluginDocumentSettingPanel;
	var Button         = wp.components.Button;
	var TextControl    = wp.components.TextControl;
	var registerPlugin = wp.plugins.registerPlugin;
	var dispatch       = wp.data.dispatch;
	var rawHandler     = wp.blocks.rawHandler;

	var MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB.

	/**
	 * Strip HTML tags from a string safely (no innerHTML).
	 */
	function stripTags( html ) {
		return html.replace( /<[^>]*>/g, '' ).trim();
	}

	/**
	 * Extract title from the first <h1> in the HTML string.
	 * Returns { title, html } with the h1 removed if found.
	 */
	function extractTitle( html ) {
		var match = html.match( /^\s*<h1[^>]*>(.*?)<\/h1>/i );
		if ( match ) {
			return {
				title: stripTags( match[1] ),
				html: html.replace( match[0], '' ).trim(),
			};
		}
		return { title: '', html: html };
	}

	/**
	 * Send markdown to the server for parsing via league/commonmark.
	 */
	function parseMarkdown( markdown ) {
		var formData = new FormData();
		formData.append( 'action', 'mvpd_parse_markdown' );
		formData.append( 'nonce', mvpdImport.nonce );
		formData.append( 'markdown', markdown );

		return fetch( mvpdImport.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} )
			.then( function ( response ) { return response.json(); } )
			.then( function ( result ) {
				if ( result.success && result.data && result.data.html ) {
					return result.data.html;
				}
				var msg = ( result.data && typeof result.data === 'string' ) ? result.data : 'Parse failed.';
				throw new Error( msg );
			} );
	}

	/**
	 * Panel component rendered in the document sidebar.
	 */
	function MdImportPanel() {
		var statusState = useState( '' );
		var status      = statusState[0];
		var setStatus   = statusState[1];

		var errorState = useState( false );
		var isError    = errorState[0];
		var setIsError = errorState[1];

		var orderState   = useState( null );
		var sortOrder    = orderState[0];
		var setSortOrder = orderState[1];

		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var currentTitle = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'title' ) || '';
		}, [] );

		useEffect( function () {
			if ( sortOrder === null && meta.mvpd_sort_order !== undefined ) {
				setSortOrder( String( meta.mvpd_sort_order || 0 ) );
			}
		}, [ meta.mvpd_sort_order ] );

		function showStatus( msg, error ) {
			setStatus( msg );
			setIsError( !! error );
			if ( ! error ) {
				setTimeout( function () { setStatus( '' ); }, 4000 );
			}
		}

		function onImportClick() {
			var input = document.createElement( 'input' );
			input.type = 'file';
			input.accept = '.md,.markdown,.txt';

			input.addEventListener( 'change', function () {
				var file = input.files[0];
				if ( ! file ) return;

				if ( file.size > MAX_FILE_SIZE ) {
					showStatus( 'File too large (max 2 MB).', true );
					return;
				}

				setStatus( 'Reading...' );
				setIsError( false );

				var reader = new FileReader();
				reader.onload = function ( e ) {
					var markdown = e.target.result;

					if ( ! markdown || ! markdown.trim() ) {
						showStatus( 'File is empty.', true );
						return;
					}

					showStatus( 'Parsing markdown...' );

					parseMarkdown( markdown )
						.then( function ( html ) {
							// Auto-set title from first H1 if post title is empty.
							if ( ! currentTitle ) {
								var extracted = extractTitle( html );
								if ( extracted.title ) {
									dispatch( 'core/editor' ).editPost( { title: extracted.title } );
									html = extracted.html;
								}
							}

							// Convert HTML to Gutenberg blocks.
							var blocks = rawHandler( { HTML: html } );

							if ( ! blocks || ! blocks.length ) {
								showStatus( 'No content found in file.', true );
								return;
							}

							dispatch( 'core/block-editor' ).resetBlocks( blocks );
							showStatus( 'Imported ' + blocks.length + ' blocks.', false );
						} )
						.catch( function ( err ) {
							showStatus( 'Error: ' + ( err.message || 'Parse failed.' ), true );
						} );
				};

				reader.onerror = function () {
					showStatus( 'Error reading file.', true );
				};

				reader.readAsText( file );
			} );

			input.click();
		}

		function onSortOrderChange( value ) {
			var num = parseInt( value, 10 ) || 0;
			if ( num < 0 ) num = 0;
			setSortOrder( String( num ) );
			dispatch( 'core/editor' ).editPost( { meta: { mvpd_sort_order: num } } );
		}

		return el(
			PluginDocumentSettingPanel,
			{ name: 'mvpd-tools', title: 'Doc Settings', className: 'mvpd-tools-panel' },
			sortOrder !== null
				? el( TextControl, {
					label: 'Sort Order',
					type: 'number',
					value: sortOrder,
					onChange: onSortOrderChange,
					help: 'Lower numbers appear first in the archive.',
				} )
				: null,
			el( 'div', { className: 'mvpd-import-section' },
				el( Button, {
					variant: 'secondary',
					onClick: onImportClick,
					className: 'mvpd-import-btn',
				}, 'Import from Markdown' ),
				status
					? el( 'p', { className: 'mvpd-import-status' + ( isError ? ' is-error' : '' ) }, status )
					: null
			)
		);
	}

	registerPlugin( 'mvpd-tools', {
		render: MdImportPanel,
		icon: 'book-alt',
	} );
} )();
