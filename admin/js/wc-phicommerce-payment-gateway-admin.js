jQuery( document ).ready( function( $ ) {
	'use strict';

	// Localized variables.
	var ajaxurl          = WCPP_Admin_JS_Vars.ajaxurl;
	var please_wait_text = WCPP_Admin_JS_Vars.please_wait_text;

	// Click to copy.
	$( document ).on( 'click', '.click-to-copy', function() {
		var this_element = $( this );
		copy_to_clipboard( this_element );
		alert( 'Text copied' );
	} );

	// Custom function to copy the targetted text to clipboard.
	function copy_to_clipboard( element ) {
		var $temp = $( '<input>' );
		$( 'body' ).append( $temp );
		$temp.val( element.text() ).select();
		document.execCommand( 'copy' );
		$temp.remove();
	}

	// Get the transaction status.
	$( document ).on( 'click', '.get-transaction-status', function() {
		var this_button = $( this );
		var button_text = this_button.text();

		// Shoot the AJAX now.
		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'get_transaction_status',
				order_id: this_button.data( 'orderid' ),
			},
			beforeSend: function() {
				this_button.addClass( 'ajax-in-progress' );
				block_element( this_button ); // Block the element.
				this_button.text( please_wait_text ); // Update the button text.
				$( '.admin-phicommerce-payment-details .transaction-status span.click-to-copy' ).text( '--' ); // Update the message on the screen.
			},
			success: function ( response ) {
				if ( 1 === is_valid_string( response.data.code ) && 'transaction-status-fetched' === response.data.code ) {
					$( '.admin-phicommerce-payment-details .transaction-status span.click-to-copy' ).text( response.data.payphi_status_message ); // Update the message on the screen.
				}
			},
			complete: function() {
				this_button.removeClass( 'ajax-in-progress' );
				unblock_element( this_button ); // Unblock the element.
				this_button.text( button_text ); // Update the button text.
			},
		} );
	} );

	// Get the transaction status from the orders listing page.
	$( document ).on( 'click', '.wc-action-button-wcpp-get-transaction-status', function( evt ) {
		evt.preventDefault();
		var this_button  = $( this );
		var parent_tr    = this_button.parents( 'tr' );
		var parent_tr_id = parent_tr.attr( 'id' );

		// Hit the AJAX.
		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'get_transaction_status',
				order_id: parseInt( parent_tr_id.replace( 'post-', '' ) ),
			},
			beforeSend: function() {
				block_element( this_button ); // Block the element.
			},
			success: function ( response ) {
				if ( 1 === is_valid_string( response.data.code ) && 'transaction-status-fetched' === response.data.code ) {
					alert( 'Response: ' + response.data.payphi_status_message );
				}
			},
			complete: function() {
				unblock_element( this_button ); // Unblock the element.
			},
		} );
	} );

	// Initiate the payphi refund.
	$( document ).on( 'click', '.payphi-refund-order', function() {
		var this_button = $( this );
		var button_text = this_button.text();
		$( '.payphi-refund-error, .payphi-refund-success' ).text('');

		// Hit the AJAX.
		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'process_refund',
				order_id: this_button.data( 'orderid' ),
			},
			beforeSend: function() {
				block_element( this_button ); // Block the element.
				this_button.text( please_wait_text ); // Update the button text.
			},
			success: function ( response ) {
				if ( 1 === is_valid_string( response.data.code ) && 'payphi-refund-processed' === response.data.code ) {
					// If the error/success message needs to be shown.
					if ( 1 === is_valid_string( response.data.show_error ) ) {
						if ( 'yes' === response.data.show_error ) {
							$( '.payphi-refund-error' ).text( response.data.error_message );
						} else {
							$( '.payphi-refund-success' ).text( response.data.success_message );
							location.reload();
						}
					}
				}
			},
			complete: function( data ) {
				var response_json = data.responseJSON;
				this_button.text( button_text ); // Update the button text.

				if ( 1 === is_valid_string( response_json.data.show_error ) && 'yes' === response_json.data.show_error ) {
					unblock_element( this_button ); // Unblock the element.
				}
			},
		} );
	} );

	/**
	 * Block element.
	 *
	 * @param {string} element
	 */
	function block_element( element ) {
		element.addClass( 'non-clickable' );
	}

	/**
	 * Unblock element.
	 *
	 * @param {string} element
	 */
	function unblock_element( element ) {
		element.removeClass( 'non-clickable' );
	}

	/**
	 * Check if a number is valid.
	 * 
	 * @param {number} data 
	 */
	function is_valid_number( data ) {
		if ( null === data || '' === data || undefined === data || isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}

	/**
	 * Check if a string is valid.
	 *
	 * @param {string} $data
	 */
	function is_valid_string( data ) {
		if ( null === data || '' === data || undefined === data || ! isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}
} );
