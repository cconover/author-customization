<?php
/*
Functions to include when editing a post
*/


/**
 * Author Meta Box
 */
/* Add meta box to Edit Post and Edit Page, and remove WordPress default Author meta box */
function cc_author_add_metabox() {
	$screens = array( 'post', 'page' ); // Locations where the metabox should show
	
	/* Iterate through locations */
	foreach( $screens as $screen ) {
		add_meta_box( 'cc-author-metabox', 'Author Information', 'cc_author_metabox', $screen, 'normal', 'default' ); // Parameters for adding meta box
	}
	
	/* Add custom style for meta box */
	$styleurl = plugins_url( 'assets/css/edit-post.css', dirname( __FILE__ ) ); // Set URL to CSS file
	wp_enqueue_style( 'cc-author-metabox', $styleurl ); // Add style call to <head>
} // cc_author_add_metabox()
add_action( 'add_meta_boxes', 'cc_author_add_metabox' ); // Hook meta box into WordPress

/* Meta box code: $post is the data for the current post */
function cc_author_metabox( $post ) {
	/* Retrieve current values if they exist */
	$cc_author_displayname = get_post_meta( $post->ID, 'cc_author_displayname', true ); // Author display name
	$cc_author_bio = get_post_meta( $post->ID, 'cc_author_bio', true ); // Author bio
	
	/* If any of the values are missing from the post, retrieve them from the author's global profile */
	if ( !$cc_author_displayname || !$cc_author_bio ) {
		$currentuserid = get_current_user_id(); // Get the user ID of the current user
		
		$currentuser = get_userdata( $currentuserid ); // Retrieve the details of the current user
		
		$cc_author_displayname = $currentuser->display_name; // Set display name from current user's data
		$cc_author_bio = $currentuser->description; // Set bio from the current user's data
	}
	
	/* Display the meta box contents */
	?>
	<div class="cc_author_metabox">
		<label for="cc_author_displayname" class="selectit">Name</label>
		<input type="text" name="cc_author_displayname" value="<?php echo esc_attr( $cc_author_displayname ); ?>" />

		<label for="cc_author_bio" class="selectit">Bio</label>
		<textarea name="cc_author_bio" rows="5" cols="50" required><?php echo esc_attr( $cc_author_bio ); ?></textarea>
	</div>
	<?php
}
/**
 * End Author Meta Box
 */
?>