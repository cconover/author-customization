/*
Dynamically update post author meta box fields when post author is changed

Called by:
admin/includes/edit-post.php
*/

jQuery( document ).ready( function( $ ) { // Don't execute anything until the page is loaded
	$( "#cc_author_postauthor" ).live( "change", function() { // If the author dropdown value is changed, execute the script
		$( "#cc_author_postauthor_loading_img" ).show(); // Display the 'loading' spinner
		
		/* Data to pass to the server. Called below during $.post() */
		var data = {
			action : 'cc_author_change_postauthor',				// Action hook for the server-side callback
			nonce : authorchange.nonce,							// Nonce received from server to authenticate request
			authorID : $( "#cc_author_postauthor" ).val()		// author ID for retrieving profile data
		};
		
		/* Send request to the server and process response */
		$.post(
			authorchange.ajaxurl,								// URL to send the request to the server
			data,												// Data to send to the server, stored in var data
			function( jsonString ) {							// Script to execute upon successful response from server
				var authormeta = $.parseJSON( jsonString );		// Parse the JSON received from the server response
				
				$( "#cc_author_meta\\[0\\]\\[display_name\\]" ).val( authormeta.display_name );	// Change the value of the author display name to the value received from the server
				
				/* Handle description update differently depending on whether 'wysiwyg' is enabled in plugin options */
				if ( authormeta.wysiwyg == 'yes' && typeof tinymce == 'object' ) {
					tinymce.get( 'cc_author_meta[0][description]' ).setContent( authormeta.description );
					console.log( "WYSIWYG is enabled and update attempted" );
				}
				else {
					$( "#cc_author_meta\\[0\\]\\[description\\]" ).val( authormeta.description );	// Change the value of the author bio to the value received from the server
				}
				
				$( "#cc_author_postauthor_loading_img" ).hide(); // Hide the 'loading' spinner
			} // function( jsonString )
		); // $.post
	}); // $( "#cc_author_postauthor" ).change()
}); // jQuery ready check