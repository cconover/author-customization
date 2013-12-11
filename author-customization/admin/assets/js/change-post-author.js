/*
Dynamically update post author meta box fields when post author is changed

Called by:
/admin/includes/edit-post.php
*/

$( document ).ready( function() { // Don't execute anything until the page is loaded
	$( "#cc_author_postauthor" ).change( function() {
		var display_name = "Test";
		$( "#cc_author_meta\\[0\\]\\[display_name\\]" ).val( "New Name" );
		$( "#cc_author_meta\\[0\\]\\[description\\]" ).val( "Test bio" );
	});
});