jQuery( document ).ready( function( $ ) {
	'use strict';

	var current_path = window.location.pathname; // Current path.

	// If it's the checkout page.
	if ( -1 !== current_path.indexOf( 'checkout' ) ) {
		// Run the interval time to check if the OTP notice link is available to click.
		var check_otp_link_interval = setInterval( check_otp_auth_link_available, 2000 );

		function check_otp_auth_link_available() {
			if ( $( '.phicommerce-auth-otp-notice' ).length ) {
				clearInterval( check_otp_link_interval );
				window.location.href = $( '.phicommerce-auth-otp-notice' ).attr( 'href' );
			}
		}

		var place_order = parseInt( get_query_string_parameter_value( 'place_order' ) );

		// If the order needs to be placed.
		if ( 1 === place_order ) {
			$( '#place_order' ).click();
		}
	}

	// Add hyphens after every 4 digits on the card number.
	$( document ).on( 'keyup', '.phicommerce_payment_gateway #card_number', function() {
		var this_input = $( this );
		// Function to add - after 4 character
		add_after_character( /(\d{4})(?=\d)/g, '-', this_input );
	} );

	// Execute card payments.
	$( document ).on( 'click', '.phicommerce_execute_card_payments', function() {
		$( '#place_order' ).click();
	} );

	// Manage the tabbed content on the payment section.
	$( document ).on( 'click', '.tab_box_title h3', function () {
		if ( ! $( this ).closest( '.tab_box_title' ).hasClass( 'active_tab ' ) ) {
			$( '.panel_tab_box .tab_box_title' ).removeClass( 'active_tab' );
			$( '.panel_tab_box .tab_box_content' ).slideUp().removeClass( 'active_content' );
			$( this).closest( '.tab_box_title' ).addClass( 'active_tab' );
			$( this ).closest( '.panel_tab_box' ).find( '.tab_box_content' ).slideDown().addClass( 'active_content' );
		}
	})

	/**
	 * Check if a number is valid.
	 *
	 * @param {number} data
	 */
	function is_valid_number( data ) {

		return ( '' === data || undefined === data || isNaN( data ) || 0 === data ) ? -1 :1;
	}

	/**
	 * Check if a string is valid.
	 *
	 * @param {string} $data
	 */
	function is_valid_string( data ) {

		return ( '' === data || undefined === data || ! isNaN( data ) || 0 === data ) ? -1 : 1;
	}

	/**
	 * Check if a email is valid.
	 *
	 * @param {string} email
	 */
	function is_valid_email( email ) {
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		return ( ! regex.test( email ) ) ? -1 : 1;
	}

	/**
	 * Check if a website URL is valid.
	 *
	 * @param {string} email
	 */
	function is_valid_url( url ) {
		var regex = /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/;

		return ( ! regex.test( url ) ) ? -1 : 1;
	}

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
	 * Add symbols after x character
	 *
	 * @param {string} character_after
	 * @param {string} symbol_add
	 * @param {string} location
	 */
	function add_after_character( character_after, symbol_add, location ) {
		var v = location.val().replace(/\D/g, ''); // Remove non-numerics
		v = v.replace( character_after, '$1'+symbol_add ); // Add dashes every 4th digit
		location.val(v);
	}

	/**
	 * Get query string parameter value.
	 *
	 * @param {string} string
	 * @return {string} string
	 */
	function get_query_string_parameter_value( param_name ) {
		var url_string = window.location.href;
		var url        = new URL( url_string );
		var val        = url.searchParams.get( param_name );

		return val;
	}

	/**
	 * Show the notification text.
	 *
	 * @param {string} bg_color Holds the toast background color.
	 * @param {string} icon Holds the toast icon.
	 * @param {string} heading Holds the toast heading.
	 * @param {string} message Holds the toast body message.
	 */
	function show_notification( bg_color, icon, heading, message ) {
		$( '.wcpp-notification-wrapper .toast' ).removeClass( 'bg-success bg-warning bg-danger' );
		$( '.wcpp-notification-wrapper .toast' ).addClass( bg_color );
		$( '.wcpp-notification-wrapper .toast .wcpp-notification-icon' ).removeClass( 'fa-skull-crossbones fa-check-circle fa-exclamation-circle' );
		$( '.wcpp-notification-wrapper .toast .wcpp-notification-icon' ).addClass( icon );
		$( '.wcpp-notification-wrapper .toast .wcpp-notification-heading' ).text( heading );
		$( '.wcpp-notification-wrapper .toast .wcpp-notification-message' ).html( message );
		$( '.wcpp-notification-wrapper .toast' ).removeClass( 'hide' ).addClass( 'show' );

		setTimeout( function() {
			$( '.wcpp-notification-wrapper .toast' ).removeClass( 'show' ).addClass( 'hide' );
		}, 10000 );
	}

	// Show notification.
	// show_notification( 'bg-success', 'fa-check-circle', toast_success_heading, 'Success text.' );
	// show_notification( 'bg-warning', 'fa-exclamation-circle', toast_notice_heading, 'Notice text.' );
	// show_notification( 'bg-danger', 'fa-skull-crossbones', toast_error_heading, 'Error text.' );
} );
