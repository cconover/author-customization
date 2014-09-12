( function ( $ ) {
	// Show the meta box form (not shown by default; requires JavaScript for it to be visible)
	$( function() {
		$( "#cc_author_metabox" ).show();

		// If the author dropdown value is changed, execute the script
		$( '[name="cc_author_postauthor"]' ).change( function() {
			// Hide the 'loading' spinner
			$( '#cc-author-metabox .spinner' ).show();

			// Data to pass to the server. Called below during $.post()
			var data = {
				action : 'cc_author_change_postauthor', // Action hook for the server-side callback
				nonce : cc_author_edit_post.nonce, // Nonce received from server to authenticate request
				authorID : $( '#cc_author_postauthor' ).val() // author ID for retrieving profile data
			};

			// Make AJAX request to the server
			author_ajax( cc_author_edit_post, data );
		} ); // $( '#cc_author_postauthor' ).change()

		// Set JavaScript field to 'yes' so the server can know whether JavaScript is working
		$( '[name=cc_author_javascript]' ).val( 'yes' );
	} );

	// AJAX call for author information
	function author_ajax( ajaxconfig, data ) {
		$.ajax( {
			// URL where to send the request
			url: ajaxconfig.url,
			// Type of request to make
			type: post,
			// Payload to send to the server
			data: $( data ).serialize(),
			// Data type expected back from the server
			datatype: 'json',
			// How long to wait for a response before timing out (milliseconds)
			timeout: 10000,

			// If the request succeeds
			success: function( json ) {
				// Change the value of the author display name to the value received from the server
				$( 'input[name="cc_author_meta\\[0\\]\\[display_name\\]"]' ).val( json.display_name );

				// If 'wysiwyg' is enabled in plugin options, update the author bio through TinyMCE
				if ( typeof tinymce == 'object' ) {
					tinymce.get( 'cc_author_meta_description' ).setContent( json.description );
				}
				// If it's not enabled, update the standard textarea
				else {
					$( 'textarea[name="cc_author_meta\\[0\\]\\[description\\]"]' ).val( json.description );
				}
			},

			// If the request ends in error
			error: function( xhr, status, errorThrown ) {
				// Output the error data to the console
				console.log( 'Error: ' + errorThrown );
				console.log( 'Status: ' + status );
				console.dir( xhr );

				// Display an error message in the meta box
				$( "#cc_author_metabox" ).prepend( 'There was a problem getting the author\'s information. Please reload the page and try again.' );
			},

			// Code to execute regardless of the outcome
			complete: function() {
				// Hide the 'loading' spinner
				$( '#cc-author-metabox .spinner' ).hide();
			}
		} );
	} // author_ajax()
} ( jQuery ) );
