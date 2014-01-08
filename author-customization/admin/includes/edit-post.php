<?php
/*
Functions to include when editing a post

Included/Required by:
admin/author-customization-admin.php
*/


/**
 * Includes
 */
require_once( dirname( __FILE__ ) . '/class.descEditor.php' ); // File containing bio editor class
/**
 * End Includes
 */


/**
 * Author Meta Box Class
 * Create author meta box for existing user
 */
class ccAuthorMetaBox {
	/* Class constructor */
	function __construct() {
		/* Variables attached to the class */
		$this->spinner	= '<span class="spinner"></span>';
		
		add_action( 'admin_init', array( $this, 'admin_init' ) ); // Initializtion within admin
		
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) ); // Hook meta box updates into WordPress
		
		/* AJAX action hooks */
		add_action( 'wp_ajax_cc_author_change_postauthor', array( $this, 'change_postauthor_callback' ) );
		add_action( 'wp_ajax_cc_author_create_user_metabox_request_form', array( $this, 'create_user_metabox_request_form' ) );
		add_action( 'wp_ajax_cc_author_create_user_metabox_callback', array( $this, 'create_user_metabox_callback' ) );
		
		add_action( 'save_post', array( $this, 'save_meta' ) ); // Hook WordPress to save meta data when saving post/page		
	} // __construct()
	
	/* Initialization for admin */
	function admin_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) ); // Load scripts and styles
	} // admin_init()
	
	/* Add scripts and styles */
	function add_scripts() {
		/* Add custom style for meta box */
		wp_enqueue_style( // Add style call to <head>
			'cc-author-metabox',														// Stylesheet hook name
			plugins_url( 'assets/css/edit-post.css', dirname( __FILE__ ) ),				// URL for stylesheet
			array()																	// Style dependencies
		);
		
		/* Add script for changing the post author */
		wp_enqueue_script(																// Add JS to <head>
			'cc-author-edit-post',														// Registered script handle
			plugins_url( 'assets/js/edit-post.js', dirname( __FILE__ ) ),				// URL to script
			array(																		// Script dependencies
				'jquery'
			)
		);
		wp_localize_script(																// Localize script for AJAX calls
			'cc-author-edit-post',														// Name of script call being localized
			'cc_author_edit_post',														// AJAX object namespace, used to call values in the JS file
			array(
				'ajaxurl'	=> admin_url( 'admin-ajax.php' ),							// URL for admin AJAX calls
				'nonce'		=> wp_create_nonce( 'cc-author-edit-post-nonce' )			// Nonce to authenticate request
			)
		);
	} // add_scripts()
	
	/**
	 * Author Meta Box
	 * Functions for creating and displaying the meta box
	 */
	/* Add meta box to Edit Post and Edit Page, and remove WordPress default Author meta box */
	function add_metabox() {
		$screens = array( 'post', 'page' ); // Locations where the metabox should show
		
		/* Remove WordPress default Author meta box */
		foreach ( $screens as $screen ) {
			remove_meta_box( 'authordiv', $screen, 'normal' ); // Parameters for removing Author meta box from Post and Page edit screens
		}
		
		/* Iterate through locations to add meta box */
		foreach( $screens as $screen ) {
			add_meta_box(
				'cc-author-metabox',
				'Authors' . $this->spinner,
				array( $this, 'metabox' ),
				$screen,
				'normal',
				'high'
			);
		}
	} // add_metabox()
	
	/* Meta box code: $post is the data for the current post */
	function metabox( $post ) {
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
		<noscript>
				JavaScript must be enabled to use this feature.
		</noscript>
		<div id="cc_author_metabox" class="cc_author_metabox" style="display: none;">
			<p>The information below will be saved to this post, and (unless selected) will not be saved to the author's user profile.</p>
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
					<input type="hidden" name="cc_author_currentpostauthor" value="<?php echo $postauthorid; ?>">
					<input type="hidden" name="cc_author_javascript" id="cc_author_javascript" value="">
				</div><!-- #cc_author_metabox_postauthor -->
				<?php
			}
			?>
			<label id="label_cc_author_meta[0][display_name]" for="cc_author_meta[0][display_name]" class="selectit">Name</label>
			<input type="text" name="cc_author_meta[0][display_name]" id="cc_author_meta[0][display_name]" value="<?php echo esc_attr( $cc_author_meta[0]['display_name'] ); ?>" />
	
			<label for="cc_author_meta[0][description]" class="selectit">Bio</label>
			<?
			$descEditor = new ccAuthorDescEditor( $cc_author_meta[0]['description'], 'cc_author_meta[0][description]' ); // Create the bio editor object
			$descEditor->editor(); // Display the editor
			?>
			<div class="cc_author_meta_update_profile">
				<input type="checkbox" name="cc_author_meta[0][update_profile]" id="cc_author_meta[0][update_profile]" value="Profile">Update author's global profile
				<p class="description">Checking this will update the author's site-wide user profile with the information you've entered.</p>
			</div>
		</div> <!-- .cc_author_metabox -->
		<?php
	} // metabox( $post )
	/**
	 * End Author Meta Box
	 */
	 
	 
	 /**
	  * Update Author Metadata
	  * Functions for saving and updating author metadata
	  */
	/* Callback for change author JavaScript */
	function change_postauthor_callback() {
		global $wpdb; // Allow access to database
		
		$nonce = $_POST['nonce']; // Assign a local variable for nonce
		
		if ( ! wp_verify_nonce( $nonce, 'cc-author-edit-post-nonce' ) ) { // If the nonce doesn't check out, fail the request
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
	} // change_postauthor_callback()
	
	/* Save the meta box data to post meta */
	function save_meta( $post_id, $post ) {
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
			remove_action( 'save_post', array( $this, 'save_meta' ) ); // Remove the 'save_post' hook before updating the post author to prevent an infinite loop
			wp_update_post( array(
				'ID'			=> $post_id,
				'post_author'	=> $_POST['cc_author_postauthor'] // Use the post author ID from the dropdown
			) );
			add_action( 'save_post', array( $this, 'save_meta' ) ); // Re-add the 'save_post' hook after the post author is updated
			
			/* If 'Update Profile' is enabled, save the author info to the user profile of the author */
			foreach ( $author as $authormeta ) {
				foreach ( $authormeta as $key => $meta ) {
					if ( isset( $authormeta['update_profile'] ) ) {
						wp_update_user( array(
							'ID'			=>	$_POST['cc_author_postauthor'],	// Author user ID
							'display_name'	=>	$authormeta['display_name'],	// Display name
							'nickname'		=>	$authormeta['display_name'],	// Set nickname to display name
							'description'	=>	$authormeta['description'],		// Biographical info
						) );
					} // if ( isset( $authormeta['update_profile'] ) )
				} // foreach ( $authormeta as $key => $meta )
			} // foreach ( $author as $authormeta )
		}
	} // save_meta( $post_id )
	/**
	 * End Update Author Metadata
	 */
}
/**
 * End Author Meta Box Class
 */

$cc_author_metabox = new ccAuthorMetaBox();
?>