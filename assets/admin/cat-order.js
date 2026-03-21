/**
 * MVP Docs — Category drag-and-drop ordering.
 */
jQuery( function ( $ ) {
	var $list   = $( '#mvpd-cat-sortable' );
	var $status = $( '#mvpd-cat-order-status' );

	if ( ! $list.length ) {
		return;
	}

	$list.sortable( {
		axis: 'y',
		cursor: 'grabbing',
		handle: '.dashicons-menu',
		placeholder: 'mvpd-sortable-placeholder',
		update: function () {
			var order = [];
			$list.children( 'li' ).each( function () {
				order.push( $( this ).data( 'term-id' ) );
			} );

			$status.text( 'Saving...' ).css( 'color', '#666' );

			$.post( mvpdOrder.ajaxUrl, {
				action: 'mvpd_save_category_order',
				nonce: mvpdOrder.nonce,
				order: order,
			} )
				.done( function ( res ) {
					if ( res.success ) {
						$status.text( 'Order saved.' ).css( 'color', '#1e7e34' );
					} else {
						$status.text( 'Error saving order.' ).css( 'color', '#cc1818' );
					}
					setTimeout( function () { $status.text( '' ); }, 3000 );
				} )
				.fail( function () {
					$status.text( 'Error saving order.' ).css( 'color', '#cc1818' );
				} );
		},
	} );
} );
