<?php
/*
Plugin Name: Author Customization
Plugin URI: https://christiaanconover.com/code/wp-author-customization
Description: Full-featured author management. Adds support for multiple post authors and custom authors outside WordPress' user account structure. No theme editing required.
Version: 0.1.0-alpha
Author: Christiaan Conover
Author URI: https://christiaanconover.com
License: GPLv2
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
		deactivate_plugins( basename(__FILE__) ); // Deactivate the plugin
	}
	
	/* Set default features for plugin */
	$features = array (
		'perpost'		=>	'Post',		// Save author info to each individual post, rather than pulling from global author data
		'wysiwyg'		=>	'WYSIWYG'	// Enable the WYSIWYG editor for author bio fields
	);
	add_option( 'cc_author_features', $features );
} // cc_author_activate()
register_activation_hook( __FILE__, 'cc_author_activate' ); // Register activation function with WordPress' activation hook
/**
 * End Plugin Activation
 */
?>