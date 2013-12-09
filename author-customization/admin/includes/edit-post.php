<?php
/*
Functions to include when editing a post

Included/Required by:
admin/author-customization-admin.php
*/


/**
 * Author Meta Box
 * Functions for creating and displaying the meta box
 */
/* Add meta box to Edit Post and Edit Page, and remove WordPress default Author meta box */
function cc_author_add_metabox() {
	$screens = array( 'post', 'page' ); // Locations where the metabox should show
	
	/* Remove WordPress default Author meta box */
	foreach ( $screens as $screen ) {
		remove_meta_box( 'authordiv', $screen, 'normal' ); // Parameters for removing Author meta box from Post and Page edit screens
	}
	
	/* Iterate through locations to add meta box */
	foreach( $screens as $screen ) {
		add_meta_box( 'cc-author-metabox', 'Author', 'cc_author_metabox', $screen, 'normal', 'high' ); // Parameters for adding meta box
	}
	
	/* Add custom style for meta box */
	$styleurl = plugins_url( 'assets/css/edit-post.css', dirname( __FILE__ ) ); // Set URL to CSS file
	wp_enqueue_style( 'cc-author-metabox', $styleurl ); // Add style call to <head>
	
	/* Add script for changing the post author */
	$changeauthorurl = plugins_url( 'assets/js/change-post-author.js', dirname( __FILE__ ) ); // Set URL to JavaScript file
	wp_enqueue_script( 'cc-author-change-post-author', $changeauthorurl, 'jquery' ); // Add JS to <head>
} // cc_author_add_metabox()
add_action( 'add_meta_boxes', 'cc_author_add_metabox' ); // Hook meta box updates into WordPress

/* Meta box code: $post is the data for the current post */
function cc_author_metabox( $post ) {
	/* Retrieve current values if they exist */
	$cc_author_meta = get_post_meta( $post->ID, '_cc_author_meta', true ); // Author meta data (stored as an array)
	$postauthorid = $post->post_author; // Get the user ID of the post author
	
	/* If any of the values are missing from the post, retrieve them from the author's global profile */
	if ( !$cc_author_meta ) {		
		$postauthor = get_userdata( $postauthorid ); // Retrieve the details of the post author
		
		$cc_author_meta = array(); // Initialize main array
		$cc_author_meta[0] = array( // Nested array for author data
			'display_name'	=> $postauthor->display_name, // Set display name from post author's data
			'description'	=> $postauthor->description // Set bio from the post author's data
		);
	}
	
	/* Display the meta box contents */
	?>
	<div class="cc_author_metabox">
		<p>Changes made to this information will only apply to this post, and will not be saved to the user's profile.</p>
		<?php
		if ( current_user_can( 'edit_others_posts' ) || current_user_can( 'edit_others_pages' ) ) { // Check the capabilities of the current user for sufficient privileges
			wp_dropdown_users( array(
				'name'			=> 'cc_author_postauthor', // Name for the form item
				'selected'		=> $postauthorid // Select the post's author to be displayed by default
			) );
			?>
			<noscript>
				You have JavaScript disabled. If you change the post author in the dropdown, you will need to save the post for the fields below to update. Please enable JavaScript for a better experience.
			</noscript>
			<input type="hidden" name="cc_author_currentpostauthor" value="<?php echo $postauthorid; ?>">
			<?php
		}
		?>
		<label for="cc_author_meta[0][display_name]" class="selectit">Name</label>
		<input type="text" name="cc_author_meta[0][display_name]" value="<?php echo esc_attr( $cc_author_meta[0]['display_name'] ); ?>" />

		<label for="cc_author_meta[0][description]" class="selectit">Bio</label>
		<?
		/* Show WYSIWYG editor if enabled in plugin options */
		$features = get_option( 'cc_author_features' ); // Retrieve the plugin options
		if ( isset( $features['wysiwyg'] ) ) {
			$settings = array( // Settings for WYSIWYG
				'media_buttons'		=> false, // Don't display media upload options
				'quicktags'			=> false, // Disable quicktags
				'textarea_name'		=> 'cc_author_meta[0][description]', // Form item name
				'teeny'				=> true, // Keep editor to minimal button options, instead of full editor
				'tinymce'			=> array(
					'theme_advanced_buttons1'	=> 'bold,italic,underline,strikethrough,link,unlink' // Only show the listed buttons in the editor
				),
			);
			wp_editor( $cc_author_meta[0]['description'], 'cc-author-meta-description', $settings ); // Display WYSIWYG
		}
		else {
			?>
			<textarea name="cc_author_meta[0][description]" rows="5" cols="50" required><?php echo esc_attr( $cc_author_meta[0]['description'] ); ?></textarea>
			<?php
		}
		?>
	</div>
	<?php
} // cc_author_metabox( $post )
/**
 * End Author Meta Box
 */
 
 
 /**
  * Update Author Metadata
  * Functions for saving and updating author metadata
  */
/* Callback for change author JavaScript */
function cc_author_change_postauthor_callback() {
	global $wpdb; // Get access to the database
	
	$author = $_POST['cc_author_postauthor']; // Assign local variable for submitted post author
	
	$authordata = get_userdata( $author ); // Retrieve the selected user's data from their profile
	
	$authormeta = array(
		'display_name'	=> $authordata['display_name'], // Display name from profile
		'description'	=> $authordata['description'] // Biographical info from profile
	);
	
	echo $authormeta;
	
	die(); // End response. Required for callback to return a proper result.
} // cc_author_change_postauthor_callback()
add_action( 'wp_ajax_cc_author_change_postauthor', 'cc_author_change_postauthor_callback' ); // Add action hook for the callback

/* Save the meta box data to post meta */
function cc_author_save_meta( $post_id ) {
	if ( isset( $_POST['cc_author_meta'] ) ) { // Verify that values have been provided
		if ( isset( $_POST['cc_author_postauthor'] ) ) { // Check whether a value for 'cc_author_postauthor' was sent
			/* If the post author has been changed, use the new post author's profile values for post-specific data. Otherwise, use data submitted */
			if ( $_POST['cc_author_postauthor'] != $_POST['cc_author_currentpostauthor'] ) {
				$postauthor = get_userdata( $_POST['cc_author_postauthor'] ); // Retrieve the details of the post author
		
				$author = array(); // Initialize main array
				$author[0] = array( // Nested array for author data
					'display_name'	=> $postauthor->display_name, // Set display name from post author's data
					'description'	=> $postauthor->description // Set bio from the post author's data
				);
			}
			else {
				$author = $_POST['cc_author_meta']; // Assign POST data to local variable
			}
		}
		else {
			$author = $_POST['cc_author_meta']; // Assign POST data to local variable
		}
		
		/* Sanitize array values */
		foreach ( $author as $authormeta ) {
			foreach ( $authormeta as $key => $meta ) {
				$authormeta[$key] = strip_tags( $meta );
			}
		}
		update_post_meta( $post_id, '_cc_author_meta', $author ); // Save author metadata to post meta
		
		/* Save the post/page author */
		remove_action( 'save_post', 'cc_author_save_meta' ); // Remove the 'save_post' hook before updating the post author to prevent an infinite loop
		wp_update_post( array(
			'ID'			=> $post_id,
			'post_author'	=> $_POST['cc_author_postauthor'] // Use the post author ID from the dropdown
		) );
		add_action( 'save_post', 'cc_author_save_meta' ); // Re-add the 'save_post' hook after the post author is updated
	}
} // cc_author_save_meta( $post_id )
add_action( 'save_post', 'cc_author_save_meta' ); // Hook WordPress to save meta data when saving post/page
/**
 * End Update Author Metadata
 */
?>