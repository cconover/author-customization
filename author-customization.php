<?php
/**
 * Plugin Name: Author Customization
 * Plugin URI: https://christiaanconover.com/code/wp-author-customization
 * Description: Author Customization adds additional author management capabilities beyond the native user account structure. Save author data to each post, enable WYSIWYG editing of biographical info, and more.
 * Version: 0.3.0-alpha
 * Author: Christiaan Conover
 * Author URI: https://christiaanconover.com
 * License: GPLv2
 * @package cc-author-customization
 **/

/**
 * Main plugin class
 **/
class cc_author {
	/* Plugin-wide settings and data */
	// Plugin identifier
	const ID = 'cc-author';
	
	// Plugin name
	const NAME = 'Author Customization';
	
	// Plugin version
	const VERSION = '0.3.0-alpha';
	
	// Minimum version of WordPress required for this plugin
	const WPVER = '3.5.2';
	
	// This plugin's database prefix
	protected $prefix = 'cc_author_';
	
	/* Plugin path & file location */
	protected $pluginpath;
	protected $pluginfile;
	
	/* Plugin's class constructor */
	public function __construct() {
		// Set plugin variables
		$this->pluginpath = dirname( __FILE__ );
		$this->pluginfile = __FILE__;
		
		// Load admin class if in admin
		if ( is_admin() ) {
			require_once( $this->pluginpath . '/admin/author-customization-admin.php' );
			$admin = new cc_author_admin;
			
			// Register plugin activation and deactivation hooks
			register_activation_hook( $this->pluginfile, array( &$admin, 'activate' ) );
			register_deactivation_hook( $this->pluginfile, array( &$admin, 'deactivate' ) );
		}
	}
}
/**
 * End cc_author
 **/

/* Create an instance of the plugin in the global space */
global $cc_author;
$cc_author = new cc_author;
?>