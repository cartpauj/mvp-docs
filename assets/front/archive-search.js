( function () {
	var input    = document.querySelector( '.mvpd-search-input' );
	var dropdown = document.querySelector( '.mvpd-search-dropdown' );
	var wrap     = document.querySelector( '.mvpd-search' );

	if ( ! input || ! dropdown || ! wrap ) return;

	var timer       = null;
	var activeIndex = -1;

	/* ── Dropdown (typeahead) ── */

	function fetchResults( query ) {
		var xhr = new XMLHttpRequest();
		xhr.open( 'GET', mvpdSearch.ajaxUrl + '?action=mvpd_search_docs&mvpd_s=' + encodeURIComponent( query ) );
		xhr.onload = function () {
			if ( xhr.status !== 200 ) return;
			var data = JSON.parse( xhr.responseText );
			if ( ! data.success ) return;
			if ( input.value.trim().toLowerCase() !== query.toLowerCase() ) return;
			renderDropdown( data.data, query );
		};
		xhr.send();
	}

	function renderDropdown( docs, query ) {
		activeIndex = -1;
		if ( ! docs.length ) {
			dropdown.innerHTML = '<div class="mvpd-search-empty">No docs found.</div>';
			dropdown.style.display = 'block';
			return;
		}

		var html = '';
		for ( var i = 0; i < docs.length; i++ ) {
			var title = highlightMatch( docs[ i ].title, query );
			var cat   = docs[ i ].category ? '<span class="mvpd-search-cat">' + escHtml( docs[ i ].category ) + '</span>' : '';
			html += '<a class="mvpd-search-item" href="' + escAttr( docs[ i ].url ) + '">' +
				'<svg class="mvpd-card-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>' +
				'<span class="mvpd-search-item-title">' + title + '</span>' +
				cat +
				'</a>';
		}
		dropdown.innerHTML = html;
		dropdown.style.display = 'block';
	}

	function highlightMatch( text, query ) {
		var safe  = escHtml( text );
		var lower = safe.toLowerCase();
		var idx   = lower.indexOf( query.toLowerCase() );
		if ( idx === -1 ) return safe;
		return safe.substring( 0, idx ) + '<mark>' + safe.substring( idx, idx + query.length ) + '</mark>' + safe.substring( idx + query.length );
	}

	function closeDropdown() {
		dropdown.style.display = 'none';
		dropdown.innerHTML = '';
		activeIndex = -1;
	}

	input.addEventListener( 'input', function () {
		var q = this.value.trim();
		clearTimeout( timer );

		if ( q.length < 2 ) {
			closeDropdown();
			return;
		}

		timer = setTimeout( function () {
			fetchResults( q );
		}, 250 );
	} );

	/* ── Keyboard navigation ── */

	input.addEventListener( 'keydown', function ( e ) {
		var items = dropdown.querySelectorAll( '.mvpd-search-item' );

		if ( e.key === 'ArrowDown' && items.length ) {
			e.preventDefault();
			activeIndex = Math.min( activeIndex + 1, items.length - 1 );
			updateActive( items );
		} else if ( e.key === 'ArrowUp' && items.length ) {
			e.preventDefault();
			activeIndex = Math.max( activeIndex - 1, -1 );
			updateActive( items );
		} else if ( e.key === 'Enter' ) {
			e.preventDefault();
			if ( activeIndex >= 0 && items.length && items[ activeIndex ] ) {
				items[ activeIndex ].click();
			} else {
				submitSearch();
			}
		} else if ( e.key === 'Escape' ) {
			closeDropdown();
		}
	} );

	function updateActive( items ) {
		for ( var i = 0; i < items.length; i++ ) {
			items[ i ].classList.toggle( 'mvpd-search-item--active', i === activeIndex );
		}
	}

	/* ── Submit button ── */

	var btn = document.querySelector( '.mvpd-search-btn' );
	if ( btn ) {
		btn.addEventListener( 'click', function () {
			submitSearch();
		} );
	}

	/* ── Close dropdown on outside click ── */

	document.addEventListener( 'click', function ( e ) {
		if ( ! wrap.contains( e.target ) ) {
			closeDropdown();
		}
	} );

	/* ── Submit navigates to search page ── */

	function submitSearch() {
		var q = input.value.trim();
		if ( q.length < 2 ) return;
		window.location.href = mvpdSearch.searchUrl + '?mvpd_s=' + encodeURIComponent( q );
	}

	/* ── Helpers ── */

	function escHtml( str ) {
		var div = document.createElement( 'div' );
		div.appendChild( document.createTextNode( str ) );
		return div.innerHTML;
	}

	function escAttr( str ) {
		return str.replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
	}
} )();
