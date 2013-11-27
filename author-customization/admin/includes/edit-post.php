<?php
/*
Functions to include when editing a post
*/


/**
 * Author Meta Box
 */
/* Add meta box to Edit Post */
function cc_author_add_metabox() {
	$screens = array( 'post', 'page' ); // Locations where the metabox should show
	
	/* Iterate through locations */
	foreach( $screens as $screen ) {
		add_meta_box( 'cc-author-metabox', 'Author', 'cc_author_metabox', $screen, 'normal', 'high' ); // Parameters for adding meta box
	}
} // cc_author_add_metabox()
add_action( 'add_meta_boxes', 'cc_author_add_metabox' ); // Hook meta box into WordPress

/* Meta box code: $post is the data for the current post */
function cc_author_metabox( $post ) {
	echo 'This is the Author meta box.';
}
/**
 * End Author Meta Box
 */
?>