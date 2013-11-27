<?php
/*
Functions to include when editing a post
*/


/**
 * Author Info Add/Update
 * Functions to save & retrieve author info while editing a post
 */

/**
 * End Author Info Add/Update
 */

/**
 * Post Meta Box
 */
/* Add meta box to Edit Post */
function cc_author_add_metabox() {
	add_meta_box( 'custom-author-complete', 'Authors', 'cc_author_metabox', 'post' ); // Parameters for adding meta box
} // cc_author_add_metabox()
add_action( 'add_meta_boxes', 'cc_author_add_metabox' ); // Hook meta box into WordPress

/* Meta box code: $post is the object for the current post */
function cc_author_metabox( $post ) {
	
}
/**
 * End Post Meta Box
 */
?>