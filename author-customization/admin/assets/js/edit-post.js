/*
Dynamically update post author meta box fields when post author is changed

Called by:
admin/includes/edit-post.php
*/

jQuery( document ).ready( function( $ ) { // Don't execute anything until the page is loaded
	$( "#cc_author_metabox" ).show(); // Show the meta box form if JavaScript is enabled
	$( "#cc_author_postauthor" ).change( function() { // If the author dropdown value is changed, execute the script
		$( "#cc-author-metabox .spinner" ).css( 'display', 'inline-block' ); // Display the 'loading' spinner
		
		/* Data to pass to the server. Called below during $.post() */
		var data = {
			action : 'cc_author_change_postauthor',				// Action hook for the server-side callback
			nonce : cc_author_edit_post.nonce,					// Nonce received from server to authenticate request
			authorID : $( "#cc_author_postauthor" ).val()		// author ID for retrieving profile data
		};
		
		/* Send request to the server and process response */
		$.post(
			cc_author_edit_post.ajaxurl,								// URL to send the request to the server
			data,												// Data to send to the server, stored in var data
			function( jsonString ) {							// Script to execute upon successful response from server
				var authormeta = $.parseJSON( jsonString );		// Parse the JSON received from the server response
				
				$( "#cc_author_meta\\[0\\]\\[display_name\\]" ).val( authormeta.display_name );	// Change the value of the author display name to the value received from the server
				
				/* Handle description update differently depending on whether 'wysiwyg' is enabled in plugin options */
				if ( authormeta.wysiwyg == 'yes' && typeof tinymce == 'object' ) {
					tinymce.get( 'cc_author_meta[0][description]' ).setContent( authormeta.description );
				}
				else {
					$( "#cc_author_meta\\[0\\]\\[description\\]" ).val( authormeta.description );	// Change the value of the author bio to the value received from the server
				}
				
				$( "#cc-author-metabox .spinner" ).css( 'display', 'none' ); // Hide the 'loading' spinner
			} // function( jsonString )
		); // $.post
	}); // $( "#cc_author_postauthor" ).change()

	$( "#cc_author_create_author" ).click( function() {
		$( "#cc-author-metabox .spinner" ).css( 'display', 'inline-block' ); // Display the 'loading' spinner
		
		/* Author data to pass to the server */
		var authorinfo = {
			action : 'cc_author_create_author',
			nonce : cc_author_edit_post.nonce,
			cc_author_create : {
				first_name : $( "#cc_author_create\\[first_name\\]" ).val(),
				last_name : $( "#cc_author_create\\[last_name\\]" ).val(),
				email : $( "#cc_author_create\\[email\\]" ).val()
			}
		};
		
		var requestform = {
			action : 'cc_author_create_user_metabox_request_form',
			nonce : cc_author_edit_post.nonce,
			newuserform : 'yes'
		};
		
		$.post(
			cc_author_edit_post.ajaxurl,
			requestform,
			function( response ) {
				var newuserform = response; // Parse the JSON sent back from the server
				
				$( "#cc_author_metabox" ).hide(); // Hide the standard metabox fields
				
				$( "#cc-author-metabox .inside" ).append( response ); // Show new user form
				console.log( response ); // Output server info to console
				
				$( "#cc-author-metabox .spinner" ).css( 'display', 'none' ); // Hide the 'loading' spinner
			}
		);
	}); // $( "#cc_author_create_author" ).click()
	
	$( "#cc_author_create_submit" ).click( function() {
		/* Send POST request to the server and process the response */
		$.post(
			cc_author_edit_post.ajaxurl,
			authorinfo,
			function( jsonString ) {
				var newauthor = $.parseJSON( jsonString ); // Parse the JSON returned from the server
			}
		);
	} ); // $( "#cc_author_create_submit" ).click()
	
	$( "#cc_author_create_cancel" ).click( function() {
		$( "#cc-author-metabox .spinner" ).css( 'display', 'inline-block' ); // Display the 'loading' spinner
		
		$( "#cc_author_create_metabox" ).remove(); // Remove the meta box for creating a new author
		$( "#cc_author_metabox" ).show(); // Show the normal author box that was previously hidden
		
		$( "#cc-author-metabox .spinner" ).css( 'display', 'none' ); // Hide the 'loading' spinner
	} ); // $( "#cc_author_create_cancel" ).click()
	
	$( "#cc_author_javascript" ).val( "yes" ); // Set JavaScript field to 'yes' so the server can know whether JavaScript is working
}); // jQuery ready check