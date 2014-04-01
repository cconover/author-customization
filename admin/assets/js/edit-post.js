/*
Dynamically update post author meta box fields when post author is changed
*/

// Don't execute anything until the page is loaded
jQuery( document ).ready( function( $ ) {
	// Show the meta box form (not shown by default; requires JavaScript for it to be visible)
	$( '#cc_author_metabox' ).show();
	
	// If the author dropdown value is changed, execute the script
	$( '[name="cc_author_postauthor"]' ).change( function() {
		// Display the 'loading' spinner
		$( '#cc-author-metabox .spinner' ).css( 'display', 'inline-block' );
		
		// Data to pass to the server. Called below during $.post()
		var data = {
			action : 'cc_author_change_postauthor', // Action hook for the server-side callback
			nonce : cc_author_edit_post.nonce, // Nonce received from server to authenticate request
			authorID : $( '#cc_author_postauthor' ).val() // author ID for retrieving profile data
		};
		
		// Send request to the server and process response
		$.post(
			cc_author_edit_post.ajaxurl, // URL to send the request to the server
			data, // Data to send to the server, stored in var data
			function( jsonString ) { // Script to execute upon successful response from server
				var authormeta = $.parseJSON( jsonString ); // Parse the JSON received from the server response
				
				// Change the value of the author display name to the value received from the server
				$( '[name="cc_author_meta\\[0\\]\\[display_name\\]"]' ).val( authormeta.display_name );
				
				// If 'wysiwyg' is enabled in plugin options, update the author bio through TinyMCE
				if ( typeof tinymce == 'object' ) {
					tinymce.get( 'cc_author_meta_description' ).setContent( authormeta.description );
				}
				// If it's not enabled, update the standard textarea
				else {
					$( '[name="cc_author_meta\\[0\\]\\[description\\]"]' ).val( authormeta.description ); // Change the value of the author bio to the value received from the server
				}
				
				// Hide the 'loading' spinner
				$( '#cc-author-metabox .spinner' ).css( 'display', 'none' );
			} // function( jsonString )
		); // $.post
	}); // $( '#cc_author_postauthor' ).change()
	
	// Set JavaScript field to 'yes' so the server can know whether JavaScript is working
	$( '[name=cc_author_javascript]' ).val( 'yes' );
}); // jQuery ready check