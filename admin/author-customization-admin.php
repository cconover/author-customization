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
	 	
	 	/* Set default features for plugin */
		$postpage = array (
			'perpost'			=>	'Post',		// Save author info to each individual post, rather than pulling from global author data
			'multiple-authors'	=>	'Multiple',	// Enable support for multiple authors per post/page
			'relnofollow'		=>	'Nofollow'	// Add rel="nofollow" to links in bio entries
		);
		add_option( $this->prefix . 'postpage', $postpage ); // Save options to database
	
		$admin_options = array(
			'wysiwyg'			=>	'WYSIWYG'	// Enable the WYSIWYG editor for author bio fields
		);
		add_option( $this->prefix . 'admin_options', $admin_options ); // Save options to database
	 } // End activate()
	 
	 // Plugin deactivation
	 public function deactivate() {
	 	
	 } // End deactivate()
}
/**
 * End cc_author_admin
 **/
?>