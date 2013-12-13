<?php
/*
Functions to include when editing a post

Included/Required by:
admin/author-customization-admin.php
*/


/**
 * Includes
 */
require_once( dirname( __FILE__ ) . '/class.ccAuthorDescEditor.php' ); // File containing bio editor class
/**
 * End Includes
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
		add_meta_box(
			'cc-author-metabox',
			'Author',
			'cc_author_metabox',
			$screen,
			'normal',
			'high'
		);
	}
	
	/* Add custom style for meta box */
	wp_enqueue_style( // Add style call to <head>
		'cc-author-metabox',														// Stylesheet hook name
		plugins_url( 'assets/css/edit-post.css', dirname( __FILE__ ) ),				// URL for stylesheet
		array(),																	// Style dependencies
		$_ENV['cc_author_plugindata']['Version']									// Stylesheet version, equal to plugin version
	);
	
	/* Add script for changing the post author */
	wp_enqueue_script(																// Add JS to <head>
		'cc-author-change-post-author',												// Registered script handle
		plugins_url( 'assets/js/edit-post.js', dirname( __FILE__ ) ),				// URL to script
		array(																		// Script dependencies
			'jquery'
		),
		$_ENV['cc_author_plugindata']['Version']									// Script version, equal to plugin version
	);
	wp_localize_script(																// Localize script for AJAX calls
		'cc-author-change-post-author',												// Name of script call being localized
		'authorchange',																// AJAX object namespace, used to call values in the JS file
		array(
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),							// URL for admin AJAX calls
			'nonce'		=> wp_create_nonce( 'cc-author-change-author-nonce' )		// Nonce to authenticate request
		)
	);
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
		<p>The information below will be saved to this post, and (unless selected) will not be saved to the author's user profile.</p>
		<div style="color: #FF0000; font-weight: bold;"><noscript>
				You have JavaScript disabled. If you change the post author in the dropdown, you will need to save the post for the fields below to update. Please enable JavaScript for a better experience.
		</noscript></div>
		<?php
		if ( current_user_can( 'edit_others_posts' ) || current_user_can( 'edit_others_pages' ) ) { // Check the capabilities of the current user for sufficient privileges
			?>
			<div id="cc_author_metabox_postauthor" class="cc_author_metabox_postauthor">
			<?php
				wp_dropdown_users( array(
					'name'			=> 'cc_author_postauthor', // Name for the form item
					'id'			=> 'cc_author_postauthor', // ID for the form item
					'class'			=> 'cc_author_postauthor', // Class for the form item
					'selected'		=> $postauthorid // Select the post's author to be displayed by default
				) );
				?>
				<span class="spinner"></span>
				<input type="hidden" name="cc_author_currentpostauthor" value="<?php echo $postauthorid; ?>">
				<input type="hidden" name="cc_author_javascript" id="cc_author_javascript" value="">
				</div><!-- #cc_author_metabox_postauthor -->
			<?php
		}
		?>
		<label for="cc_author_meta[0][display_name]" class="selectit">Name</label>
		<input type="text" name="cc_author_meta[0][display_name]" id="cc_author_meta[0][display_name]" value="<?php echo esc_attr( $cc_author_meta[0]['display_name'] ); ?>" />

		<label for="cc_author_meta[0][description]" class="selectit">Bio</label>
		<?
		$descEditor = new ccAuthorDescEditor( $cc_author_meta[0]['description'], 'cc_author_meta[0][description]' ); // Create the bio editor object
		echo $descEditor->editor(); // Display the editor
		?>
		<div class="cc_author_meta_update_profile">
			<input type="checkbox" name="cc_author_meta[0][update_profile]" id="cc_author_meta[0][update_profile]" value="Profile">Update Profile
			<p class="description">Enabling this will update the author's user profile with the information you've entered.</p>
		</div>
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
	global $wpdb; // Allow access to database
	
	$nonce = $_POST['nonce']; // Assign a local variable for nonce
	
	if ( ! wp_verify_nonce( $nonce, 'cc-author-change-author-nonce' ) ) { // If the nonce doesn't check out, fail the request
		exit( 'Your request could not be authenticated' ); // Error message for unauthenticated request
	}
	
	if ( current_user_can( 'edit_others_posts' ) || current_user_can( 'edit_others_pages' ) ) { // Check for proper permissions before handling request
		$author = $_POST['authorID']; // Assign local variable for submitted post author
		$authordata = get_userdata( $author ); // Retrieve the selected user's data from their profile
		
		$admin_options = get_option( 'cc_author_admin_options' ); // Get plugin's admin options
		/* Determine whether 'wysiwyg' is enabled and set the value of $wysiwyg accordingly */
		if ( isset( $admin_options['wysiwyg'] ) ) {
			$wysiwyg = 'yes';
		}
	
		$authormeta = json_encode( array( // Encode data as JSON
			'display_name'	=> $authordata->display_name,	// Display name from profile
			'description'	=> $authordata->description,	// Biographical info from profile
			'wysiwyg'		=> $wysiwyg						// Tell JS whether or not WYSIWYG is enabled
		) );
		
		echo $authormeta; // Return the values retrieved from the database
	}
	
	exit; // End response. Required for callback to return a proper result.
} // cc_author_change_postauthor_callback()
add_action( 'wp_ajax_cc_author_change_postauthor', 'cc_author_change_postauthor_callback' ); // Add action hook for the callback

/* Save the meta box data to post meta */
function cc_author_save_meta( $post_id ) {
	if ( isset( $_POST['cc_author_meta'] ) ) { // Verify that values have been provided
		if ( isset( $_POST['cc_author_postauthor'] ) && ( $_POST['cc_author_postauthor'] != $_POST['cc_author_currentpostauthor'] ) && !isset( $_POST['cc_author_javascript'] ) ) { // If the post author has been changed and JavaScript is not enabled, use the new post author's profile values for post-specific data. Otherwise, use data submitted from the meta box.
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
		
		/* Sanitize array values */
		foreach ( $author as $authormeta ) {
			foreach ( $authormeta as $key => $meta ) {
				$authormeta['display_name'] = strip_tags( $meta );
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
		
		/* If 'Update Profile' is enabled, save the author info to the user profile of the author */
		foreach ( $author as $authormeta ) {
			foreach ( $authormeta as $key => $meta ) {
				if ( isset( $authormeta['update_profile'] ) ) {
					wp_update_user( array(
						'ID'			=>	$_POST['cc_author_postauthor'],	// Author user ID
						'display_name'	=>	$authormeta['display_name'],	// Display name
						'description'	=>	$authormeta['description'],		// Biographical info
					) );
				} // if ( isset( $authormeta['update_profile'] ) )
			} // foreach ( $authormeta as $key => $meta )
		} // foreach ( $author as $authormeta )
	}
} // cc_author_save_meta( $post_id )
add_action( 'save_post', 'cc_author_save_meta' ); // Hook WordPress to save meta data when saving post/page
/**
 * End Update Author Metadata
 */
?>