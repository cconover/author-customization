<?php
/**
 * Admin methods for Author Customization plugin
 * admin/author-customization-admin.php
 **/

/**
 * Plugin admin class
 **/
class cc_author_admin extends cc_author {
	// Class constructor
	public function __construct() {
		
	}
	
	/**
	 * Plugin activation and deactivation methods
	 **/
	 // Plugin activation
	 public function activate() {
	 	// Check WordPress version for plugin compatibility
	 	if ( version_compare( get_bloginfo( 'version' ), self::VERSION, '<' ) ) {
	 		wp_die( 'Your version of WordPress is too old to use this plugin. Please upgrade to the latest version of WordPress.' );
	 	}
	 	
	 	/* Prior to version 0.3.0 plugin options were spread out across a few database entries. From 0.3.0 on they are all in a single entry.
	 	   We need to determine whether old plugin settings are present, and if so update the database with the new setup. */
		if ( get_option( 'cc_author_postpage' ) ) {
			// If the old options entries are present, we need to retrieve those values and assign them to the new structure
			$postpage = get_option( 'cc_author_postpage' );
			$adminoptions = get_option( 'cc_author_admin_options' );
			
			// Set up the new options structure with old values
			$options = array (
				'perpost'			=>	$postpage['perpost'],			// Save author info to each individual post, rather than pulling from global author data
				'multiple-authors'	=>	$postpage['multiple-authors'],	// Enable support for multiple authors per post/page
				'relnofollow'		=>	$postpage['relnofollow'],		// Add rel="nofollow" to links in bio entries
				'wysiwyg'			=>	$adminoptions['wysiwyg']		// Enable the WYSIWYG editor for author bio fields
			);
			
			// Delete the old options entries from the database
			delete_option( 'cc_author_postpage' );
			delete_option( 'cc_author_admin_options' );
		}
		// If old options are not present, we can proceed to set up our options unchanged
		else {
	 		/* Set options for plugin */
			$options = array (
				'perpost'			=>	'Post',		// Save author info to each individual post, rather than pulling from global author data
				'multiple-authors'	=>	'Multiple',	// Enable support for multiple authors per post/page
				'relnofollow'		=>	'Nofollow',	// Add rel="nofollow" to links in bio entries
				'wysiwyg'			=>	'WYSIWYG'	// Enable the WYSIWYG editor for author bio fields
			);
		}
		add_option( $this->prefix . 'options', $options ); // Save options to database
	 } // End activate()
	 
	 // Plugin deactivation
	 public function deactivate() {
	 	
	 } // End deactivate()
}
/**
 * End cc_author_admin
 **/
?>