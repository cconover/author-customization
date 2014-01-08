<?php
/*
Functions for managing the plugin inside wp-admin

Included/Required by:
author-customization.php
*/


/**
 * Plugin Admin Initialization
 * Environment data and other elements to initialize the plugin admin
 */
function cc_author_admin_init() {
	/* Set plugin data for use elsewhere in the plugin */
	if ( function_exists( 'get_plugin_data' ) ) {
		$_ENV['cc_author_plugindata'] = get_plugin_data( plugin_dir_path( dirname( __FILE__ ) ) . 'author-customization.php', false );
	}
	else { // If the function get_plugin_data does not exist, return empty array
		$_ENV['cc_author_plugindata'] = array(
			'Version'	=>	''
		);
	}
}
add_action( 'admin_init', 'cc_author_admin_init' ); // Hook plugin admin initialization
/**
 * End Plugin Admin Initialization
 */


/**
 * Create entry in Settings menu
 * A submenu entry titled 'Custom Authors' is shown under Settings
 */
function cc_author_create_menu() {
	add_options_page(
		'Author Customization',				// Page title. This is displayed in the browser title bar.
		'Author Custom',					// Menu title. This is displayed in the Settings submenu.
		'manage_options',					// Capability required to access the options page for this plugin
		'cc-author',						// Menu slug
		'cc_author_options_page'			// Function to render the options page
	);
} // cc_author_create_menu()
add_action( 'admin_menu', 'cc_author_create_menu' ); // Hook menu entry into API
/**
 * End Create entry in Settings menu
 */


/**
 * Post/Page options configuration
 * Settings specific to posts and pages
 */
add_action( 'admin_init', 'cc_author_postpage_init' ); // Hook admin initialization for plugin postpage

function cc_author_postpage_init() {
	register_setting( 'cc_author_options', 'cc_author_postpage', 'cc_author_postpage_validate' ); // Register the settings group and specify validation and database locations
	
	add_settings_section(
		'postpage',									// Name of the section
		'Post/Page Settings',						// Title of the section, displayed on the options page
		'cc_author_postpage_callback',				// Callback function for displaying information
		'cc-author'									// Page ID for the options page
	);		
			
	add_settings_field(								// Set whether author info is pulled from post meta or global user data
		'perpost',									// Field ID
		'Use author data from post',				// Field title, displayed to the left of the field on the options page
		'cc_author_perpost_callback',				// Callback function to display the field
		'cc-author',								// Page ID for the options page
		'postpage'									// Settings section in which to display the field
	);		
	add_settings_field(								// Set whether author info is pulled from post meta or global user data
		'relnofollow',								// Field ID
		'Add rel="nofollow" to bio links',			// Field title, displayed to the left of the field on the options page
		'cc_author_relnofollow_callback',			// Callback function to display the field
		'cc-author',								// Page ID for the options page
		'postpage'									// Settings section in which to display the field
	);
} // cc_author_postpage_list()

/* Settings section callback */
function cc_author_postpage_callback() {
	echo '<p>These options are specific to posts and pages.</p>';
} // cc_author_postpage_callback()

/* Callback for 'perpost' option */
function cc_author_perpost_callback() {
	$postpage = get_option( 'cc_author_postpage' ); // Retrieve plugin options from the database
	
	/* Determine whether the box should be checked based on setting in database */
	if ( $postpage['perpost'] ) {
		$checked = 'checked';
	}
	else {
		$checked = '';
	}
	
	echo '<input id="cc_author_postpage[perpost]" name="cc_author_postpage[perpost]" type="checkbox" value="Post" ' . $checked . '>'; // Print the input field to the screen
	echo '<p class="description">Display author information from the post metadata instead of the user database. Useful for keeping author information specific to the time a post was published.</p><p class="description"><strong>Note:</strong> You can toggle this at any time, as this plugin always saves author information to post metadata regardless of this setting.</p>'; // Description of option
} // cc_author_perpost_callback()

/* Callback for 'relnofollow' option */
function cc_author_relnofollow_callback() {
	$postpage = get_option( 'cc_author_postpage' ); // Retrieve plugin options from the database
	
	/* Determine whether the box should be checked based on setting in database */
	if ( $postpage['relnofollow'] ) {
		$checked = 'checked';
	}
	else {
		$checked = '';
	}
	
	echo '<input id="cc_author_postpage[relnofollow]" name="cc_author_postpage[relnofollow]" type="checkbox" value="Nofollow" ' . $checked . '>'; // Print the input field to the screen
	echo '<p class="description">Add a <a href="https://support.google.com/webmasters/answer/96569?hl=en" target="_blank">rel="nofollow"</a> attribute to any links in an author\'s biographical info when displayed. This prevents search engines from counting those links as part of your rank score.</p>'; // Description of option
} // cc_author_relnofollow_callback()

/* Validate submitted options */
function cc_author_postpage_validate( $input ) {
	$postpage = get_option( 'cc_author_postpage' ); // Retrieve existing options values from the database
	
	/* Directly set values that don't require validation */
	$postpage['perpost']			=	$input['perpost'];
	$postpage['relnofollow']		=	$input['relnofollow'];
	
	return $postpage; // Send values to database
} // cc_author_postpage_validate()
/**
 * End Post/Page options configuration
 */


/**
 * Admin Options
 * Options for things that happen inside WordPress admin
 */
add_action( 'admin_init', 'cc_author_admin_options_init' ); // Hook admin initialization for plugin admin

function cc_author_admin_options_init() {
	register_setting( 'cc_author_options', 'cc_author_admin_options', 'cc_author_admin_options_validate' ); // Register the settings group and specify validation and database locations
	
	add_settings_section(
		'admin_options',					// Name of the section
		'Admin Settings',					// Title of the section, displayed on the options page
		'cc_author_admin_options_callback',	// Callback function for displaying information
		'cc-author'							// Page ID for the options page
	);
	
	add_settings_field(						// Set whether author info is pulled from post meta or global user data
		'wysiwyg',							// Field ID
		'WYSIWYG editor for author bio',	// Field title, displayed to the left of the field on the options page
		'cc_author_wysiwyg_callback',		// Callback function to display the field
		'cc-author',						// Page ID for the options page
		'admin_options'						// Settings section in which to display the field
	);
} // cc_author_admin_options_list()

/* Settings section callback */
function cc_author_admin_options_callback() {
	echo '<p>These options are for things that happen inside WordPress admin.</p>';
} // cc_author_admin_options_callback()

/* Call back for 'wysiwyg' option */
function cc_author_wysiwyg_callback() {
	$admin_options = get_option( 'cc_author_admin_options' ); // Retrieve plugin options from the database
	
	/* Determine whether the box should be checked based on setting in database */
	if ( $admin_options['wysiwyg'] ) {
		$checked = 'checked';
	}
	else {
		$checked = '';
	}
	
	echo '<input id="cc_author_admin_options[wysiwyg]" name="cc_author_admin_options[wysiwyg]" type="checkbox" value="WYSIWYG" ' . $checked . '>'; // Print the input field to the screen
	echo '<p class="description">Enable a WYSIWYG editor for the author bio field, both in the user profile area and in the post/page meta box.</p>'; // Description of option
} // cc_author_wysiwyg_callback()/* Call back fo

/* Validate submitted options */
function cc_author_admin_options_validate( $input ) {
	$admin_options = get_option( 'cc_author_admin_options' ); // Retrieve existing options values from the database
	
	/* Directly set values that don't require validation */
	$admin_options['wysiwyg']		=	$input['wysiwyg'];
	
	return $admin_options; // Send values to database
} // cc_author_admin_options_validate()
/**
 * End Admin Options
 */


/**
 * Options Page
 */
function cc_author_options_page() {
	/* Prevent users with insufficient permissions from accessing settings */
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '<p>You do not have sufficient permissions to access this page.</p>' );
	}
	?>
	
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Author Customization</h2>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'cc_author_options' ); 			// Retrieve the fields created for plugin options
			do_settings_sections( 'cc-author' ); 				// Display the section(s) for the options page
			submit_button();									// Form submit button generated by WordPress
			?>
		</form>
	</div>
	
	<?php	
} // cc_author_options_page()
/**
 * End Options Page
 */

/**
 * Admin Includes
 */
require_once( dirname( __FILE__ ) . '/includes/edit-post.php' ); // File containing edit post functions
require_once( dirname( __FILE__ ) . '/includes/edit-user.php' ); // File containing edit user functions
/**
 * End Admin Includes
 */
?>