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
		/* Hooks and filters */
		add_action( 'admin_menu', array( &$this, 'create_options_menu' ) ); // Add menu entry to Settings menu
		add_action( 'admin_init', array( &$this, 'options_init' ) ); // Initialize plugin options
	}
	
	/**
	 * Plugin options
	 **/
	// Create the menu entry under the Settings menu
	public function create_options_menu() {
		add_options_page(
			self::NAME,							// Page title. This is displayed in the browser title bar.
			self::NAME,							// Menu title. This is displayed in the Settings submenu.
			'manage_options',					// Capability required to access the options page for this plugin
			self::ID,							// Menu slug
			array( &$this, 'options_page' )		// Function to render the options page
		);
	} // End create_options_menu()
	
	// Initialize plugin options
	function options_init() {
		// Register the plugin options call and the sanitation callback
		register_setting(
			$this->prefix . 'options_fields',	// The namespace for plugin options fields. This must match settings_fields() used when rendering the form.
			$this->prefix . 'options',			// The name of the plugin options entry in the database
			array( &$this, 'options_validate' )	// The callback method to validate plugin options
		);
		
		// Settings section for Post/Page options
		add_settings_section(
			'postpage',								// Name of the section
			'Post/Page Settings',					// Title of the section, displayed on the options page
			array( &$this, 'postpage_callback' ),	// Callback method to display plugin options
			self::ID								// Page ID for the options page
		);
		
		// Settings section for admin options
		add_settings_section(
			'admin_options',							// Name of the section
			'Admin Settings',							// Title of the section, displayed on the options page
			array( &$this, 'admin_options_callback' ),	// Callback method to display plugin options
			self::ID									// Page ID for the options page
		);
		
		// Whether to display per-post author information
		add_settings_field(
			'perpost',								// Field ID
			'Use author data from post',			// Field title/label, displayed to the user
			array( &$this, 'perpost_callback' ),	// Callback method to display the option field
			self::ID,								// Page ID for the options page
			'postpage'								// Settings section in which to display the field
		);
		
		// Support for multiple authors on a single post
		add_settings_field(
			'multiple-authors',								// Field ID
			'Enable multiple authors on a single post',		// Field title/label, displayed to the user
			array( &$this, 'multiple_authors_callback' ),	// Callback method to display the option field
			self::ID,										// Page ID for the options page
			'postpage'										// Settings section in which to display the field
		);
		
		// Add rel="nofollow" to links inside an author's biographical info
		add_settings_field(
			'relnofollow',									// Field ID
			'Add rel="nofollow" to links in author bio',	// Field title/label, displayed to the user
			array( &$this, 'relnofollow_callback' ),		// Callback method to display the option field
			self::ID,										// Page ID for the options page
			'postpage'										// Settings section in which to display the field
		);
		
		// Enable WYSIWYG editor for author biographical info
		add_settings_field(
			'wysiwyg',								// Field ID
			'WYSIWYG editor for author bio',		// Field title/label, displayed to the user
			array( &$this, 'wysiwyg_callback' ),	// Callback method to display the option field
			self::ID,								// Page ID for the options page
			'admin_options'							// Settings section in which to display the field
		);
	} // End options_init()
	
	/* Plugin options callbacks */
	
	/* End plugin options callbacks */
	/**
	 * End plugin options
	 **/
	
	/**
	 * Plugin activation and deactivation methods
	 **/
	 // Plugin activation
	 public function activate() {
	 	// Check WordPress version for plugin compatibility
	 	if ( version_compare( get_bloginfo( 'version' ), self::VERSION, '<' ) ) {
	 		wp_die( 'Your version of WordPress is too old to use this plugin. Please upgrade to the latest version of WordPress.' );
	 	}
	 	
	 	/*
	 	MOVE TO UPDATE METHOD
	 	Prior to version 0.3.0 plugin options were spread out across a few database entries. From 0.3.0 on they are all in a single entry.
	 	We need to determine whether old plugin settings are present, and if so update the database with the new setup.
	 	*/
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
		// Remove the plugin options from the database
		delete_option( $this->prefix . 'options' );
	} // End deactivate()
	/**
	 *End plugin activation and deactivation methods
	 **/
}
/**
 * End cc_author_admin
 **/
?>