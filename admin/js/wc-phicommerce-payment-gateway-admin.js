jQuery( document ).ready( function( $ ) {
	'use strict';

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
} );
