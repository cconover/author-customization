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
	 public function activate() {
	 	// Check WordPress version for plugin compatibility
	 	if ( version_compare( get_bloginfo( 'version' ), self::VERSION, '<' ) ) {
	 		wp_die( 'Your version of WordPress is too old to use this plugin. Please upgrade to the latest version of WordPress.' );
	 	}
	 } // End activate()
}
/**
 * End cc_author_admin
 **/
?>