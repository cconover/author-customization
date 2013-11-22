<?php
/*
Functions to include when editing a post
*/


/**
 * Post Meta Box
 */
/* Add meta box to Edit Post */
function cc_author_add_metabox() {
	add_meta_box( 'custom-author-complete', 'Authors', 'cc_author_metabox', 'post' ); // Parameters for adding meta box
}
add_action( 'add_meta_boxes', 'cc_author_add_metabox' ); // Hook meta box into WordPress

/*
Meta box code
$post is the object for the current post
*/
function cc_author_metabox( $post ) {
	
}
/**
 * End Post Meta Box
 */
?>