/*
Remove the native WordPress biographical info textarea to make room for the upgraded one.
*/

(function( $ ) { 
	// Remove the native textarea before adding the new one
	$( '#description' ).parents( 'tr' ).remove();
} ) ( jQuery );