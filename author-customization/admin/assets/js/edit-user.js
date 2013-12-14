/*
Remove the native WordPress biographical info textarea to make room for upgraded one

Called by:
/admin/includes/class.ccAuthorDescEditor.php
*/

(function( $ ) { 
	// Remove the native textarea before adding the new one
	$( '#description' ).parents( 'tr' ).remove();
} ) ( jQuery );