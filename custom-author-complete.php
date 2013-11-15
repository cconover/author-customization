<?php
/*
Plugin Name: Custom Author Complete
Plugin URI: https://christiaanconover.com/code/wp-custom-author-complete
Description: Full-featured author management. Adds support for multiple post authors and custom authors outside WordPress' user account structure. No theme editing required.
Version: 0.1.0
Author: Christiaan Conover
Author URI: https://christiaanconover.com
License: GPLv2
*/

/**
 * If in wp-admin, load plugin's admin functions
 */
if ( is_admin() ) {
	require_once( dirname(__FILE__) . '/admin/custom-author-complete-admin.php' );
}


/**
 * Plugin Activation
 */
function cc_cac_activate() {
	/* Check for WordPress version compatibility, and if it fails deactivate the plugin.
	   Current WordPress version compatibility: 3.5.2 and greater */
	if ( version_compare( get_bloginfo( 'version' ), '3.5.2', '<' ) ) {
		deactivate_plugins( basename(__FILE__) ); // Deactivate the plugin
	}
	
	/* Set the default options for the plugin */
	$options = array(
		
	);
	
	add_option( 'cc_relogo_options', $options ); // Create the options entry in the database with the specified settings
} // End cc_cac_activate()
register_activation_hook( __FILE__, 'cc_cac_activate' ); // Register activation function with WordPress' activation hook
?>