<?php
/*
Plugin Name: Author Customization
Plugin URI: https://christiaanconover.com/code/wp-author-customization
Description: Author Customization adds additional author management capabilities beyond the native user account structure. Save author data to each post, enable WYSIWYG editing of biographical info, and more.
Version: 0.1.1
Author: Christiaan Conover
Author URI: https://christiaanconover.com
License: GPLv2
*/


/**
 * Author Info
 * Filter WordPress author functions to replace global profile data with plugin-generated data
 */
/* Get the post author display name from post and apply to the post on display */
function cc_author_displayname() {
	global $post; // Access post data
	
	$postpage = get_option( 'cc_author_postpage' ); // Retrive plugin's post/page options
	
	$author = get_post_meta( $post->ID, '_cc_author_meta', true ); // Get the post-specific author metadata
	
	/* If the plugin setting is enabled and there's post-specific metadata stored and a post, page, or attachment is being displayed, show the post-specific display name. Otherwise use the profile display name. */
	if ( $author && isset( $postpage['perpost'] ) ) {
		$name = $author[0]['display_name']; // Set the name to the display name stored for the post
	}
	else {
		$author = get_userdata( $post->post_author ); // Get the profile data for the post author
		$name = $author->display_name; // Set the display name to the value stored in the author's profile
	}
	
	return $name; // Send the name back to WordPress for displaying on the post
} // cc_author_displayname()
if ( !is_admin() ) { // Only add filters if not in admin
	add_filter( 'the_author', 'cc_author_displayname' ); // Hook display name function into 'the_author' filter
	add_filter( 'get_the_author_display_name', 'cc_author_displayname' ); // Hook display name function into 'get_the_author_display_name' filter
}

/* Get the post author description from post and apply it to the displayed post/page */
function cc_author_description() {
	global $post; // Access post data
	
	$author = get_post_meta( $post->ID, '_cc_author_meta', true ); // Get the post-specific author metadata
	$postpage = get_option( 'cc_author_postpage' ); // Get plugin options for posts/pages
	
	/* If the plugin setting is enabled and there's post-specific metadata stored and a post, page, or attachment is being displayed, show the post-specific bio. Otherwise use the profile bio. */
	if ( $author && isset( $postpage['perpost'] ) ) {
		$description = $author[0]['description']; // Set the description to the one saved in the post metadata
	}
	else {
		$author = get_userdata( $post->post_author ); // Get the profile data for the post author
		$description = $author->description; // Set the description to the value stored in the author's profile
	}
	
	/* If 'relnofollow' is set, add rel="nofollow" to links in bio */
	if ( isset( $postpage['relnofollow'] ) ) {
		$description = str_replace( 'href', 'rel="nofollow" href', $description );
	}
	
	$description = apply_filters( 'the_content', $description ); // Enable formatting for bio
	
	return $description; // Send back the description for WordPress to display
} // cc_author_description()
if ( !is_admin() ) { // Only add filters if not in admin
	add_filter( 'get_the_author_description', 'cc_author_description' ); // Hook description into 'get_the_author_description' filter
}
/**
 * End Author Info
 */

/* If in wp-admin, load plugin's admin functions */
if ( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/admin/author-customization-admin.php' ); // Retrieve file containing admin functions
}


/**
 * Plugin Activation
 */
function cc_author_activate() {
	/* Check for WordPress version compatibility, and if it fails deactivate the plugin.
	   Current WordPress version compatibility: 3.5.2 and greater */
	if ( version_compare( get_bloginfo( 'version' ), '3.5.2', '<' ) ) {
		deactivate_plugins( basename(__FILE__) ); // Deactivate the plugin if the version of WordPress is too old
	}
	
	/* Set default features for plugin */
	$postpage = array (
		'perpost'		=>	'Post',		// Save author info to each individual post, rather than pulling from global author data
		'relnofollow'	=>	'Nofollow'	// Add rel="nofollow" to links in bio entries
	);
	add_option( 'cc_author_postpage', $postpage ); // Save options to database
	
	$admin_options = array(
		'wysiwyg'		=>	'WYSIWYG'	// Enable the WYSIWYG editor for author bio fields
	);
	add_option( 'cc_author_admin_options', $admin_options ); // Save options to database
} // cc_author_activate()
register_activation_hook( __FILE__, 'cc_author_activate' ); // Register activation function with WordPress' activation hook
/**
 * End Plugin Activation
 */
?>