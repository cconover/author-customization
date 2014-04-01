<?php
/**
 * Admin methods for Author Customization plugin
 * admin/author-customization-admin.php
 **/

namespace cconover\author;

/**
 * Plugin admin class
 **/
class Admin extends Author {
	/** Class properties */
	private $editorid;
	private $editorsettings;
	
	// Class constructor
	function __construct() {
		// Initialize the plugin
		$this->initialize();
		$this->admin_initialize();
		
		/* Hooks and filters */
		add_action( 'admin_menu', array( &$this, 'create_options_menu' ) ); // Add menu entry to Settings menu
		add_action( 'admin_init', array( &$this, 'options_init' ) ); // Initialize plugin options
		add_action( 'add_meta_boxes', array( &$this, 'add_metabox' ) ); // Add metabox to post/page editing screen
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_metabox_scripts' ) ); // Load scripts and styles
		add_action( 'save_post', array( &$this, 'save_meta' ) ); // Hook WordPress to save meta box data when saving post/page
		
		// AJAX hooks
		add_action( 'wp_ajax_cc_author_change_postauthor', array( &$this, 'change_postauthor_callback' ) ); // Change the post author in the meta box
		
		// Hooks and filters for the editor
		if ( function_exists( 'wp_editor' ) ) {
			add_action( 'show_user_profile', array( &$this, 'editorprofile' ) ); // User profile
			add_action( 'edit_user_profile', array( &$this, 'editorprofile' ) ); // User profile
			add_action( 'admin_init', array( &$this, 'editor_remove_filters' ) ); // Remove filters from textarea
			add_action( 'admin_enqueue_scripts', array( &$this, 'profilejs' ) ); // Load JavaScript
		}
		// End hooks and filters
	} // End __construct()
	
	/*
	===== Plugin options =====
	*/
	// Create the menu entry under the Settings menu
	function create_options_menu() {
		add_options_page(
			self::NAME, // Page title. This is displayed in the browser title bar.
			self::NAME, // Menu title. This is displayed in the Settings submenu.
			'manage_options', // Capability required to access the options page for this plugin
			self::ID, // Menu slug
			array( &$this, 'options_page' ) // Function to render the options page
		);
	} // End create_options_menu()
	
	// Initialize plugin options
	function options_init() {
		// Register the plugin options call and the sanitation callback
		register_setting(
			self::PREFIX . 'options_fields', // The namespace for plugin options fields. This must match settings_fields() used when rendering the form.
			self::PREFIX . 'options', // The name of the plugin options entry in the database.
			array( &$this, 'options_validate' ) // The callback method to validate plugin options
		);
		
		// Settings section for Post/Page options
		add_settings_section(
			'postpage', // Name of the section
			'Post/Page Settings', // Title of the section, displayed on the options page
			array( &$this, 'postpage_callback' ), // Callback method to display plugin options
			self::ID // Page ID for the options page
		);
		
		// Whether to display per-post author information
		add_settings_field(
			'perpost', // Field ID
			'Use author data from post', // Field title/label, displayed to the user
			array( &$this, 'perpost_callback' ), // Callback method to display the option field
			self::ID, // Page ID for the options page
			'postpage' // Settings section in which to display the field
		);
		
		// Add rel="nofollow" to links inside an author's biographical info
		add_settings_field(
			'relnofollow', // Field ID
			'Add rel="nofollow" to links in author bio', // Field title/label, displayed to the user
			array( &$this, 'relnofollow_callback' ), // Callback method to display the option field
			self::ID, // Page ID for the options page
			'postpage' // Settings section in which to display the field
		);
	} // End options_init()
	
	/* Plugin options callbacks */
	// Callback for post/page options section
	function postpage_callback() {
		echo '<p>These options are specific to posts and pages.</p>';
	} // End postpage_callback()
	
	// Callback for admin option section
	function admin_options_callback() {
		echo '<p>These options are for things that happen inside WordPress admin.</p>';
	} // End admin_options_callback()
	
	// Callback for per-post author information option
	function perpost_callback() {
		// Check the status of this option in the database
		if ( isset( $this->options['perpost'] ) ) {
			$checked = 'checked';
		}
		else {
			$checked = NULL;
		}
	
		echo '<input id="' . self::PREFIX . 'options[perpost]" name="' . self::PREFIX . 'options[perpost]" type="checkbox" value="yes" ' . $checked . '>'; // Print the input field to the screen
		echo '<p class="description">Display author information from the post metadata instead of the user database. Useful for keeping author information specific to the time a post was published.</p><p class="description"><strong>Note:</strong> You can toggle this at any time, as this plugin always saves author information to post metadata regardless of this setting.</p>'; // Description of option
	} // End perpost_callback()
	
	// Callback for rel="nofollow" option
	function relnofollow_callback() {
		// Check the status of this option in the database
		if ( isset( $this->options['relnofollow'] ) ) {
			$checked = 'checked';
		}
		else {
			$checked = NULL;
		}
		
		echo '<input id="' . self::PREFIX . 'options[relnofollow]" name="' . self::PREFIX . 'options[relnofollow]" type="checkbox" value="yes" ' . $checked . '>'; // Print the input field to the screen
		echo '<p class="description">Add a <a href="https://support.google.com/webmasters/answer/96569?hl=en" target="_blank">rel="nofollow"</a> attribute to any links in an author\'s biographical info when displayed. This prevents search engines from counting those links as part of your rank score. If you\'re unsure what this is, leave it checked.</p>'; // Description of option
	} // End relnofollow_callback()
	
	// Validate options when submitted
	function options_validate( $input ) {
		// Set local variable for plugin options stored in the database
		$options = $this->options;
		
		// Directly set options that require no validation (such as checkboxes)
		$options['perpost'] = $input['perpost'];
		$options['relnofollow'] = $input['relnofollow'];
		
		return $options;
	} // End options_validate()
	
	// Options page
	function options_page() {
		// Make sure the user has permissions to access the plugin options
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( '<p>You do not have sufficient privileges to access this page.' );
		}
		?>
		
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php echo self::NAME; ?></h2>

			<form action="options.php" method="post">
				<?php
				settings_fields( self::PREFIX . 'options_fields' ); // Retrieve the fields created for plugin options
				do_settings_sections( self::ID ); // Display the section(s) for the options page
				submit_button(); // Form submit button generated by WordPress
				?>
			</form>
			
			<h4>Debug Info</h4>
			<div class="<?php echo self::ID; ?>-debug-info">
				<p>Please copy and paste the information below when reporting issues, and when requested in the support forums.</p>
				<strong>Site URL:</strong> <?php echo get_bloginfo( 'url' ); ?><br />
				<strong>WordPress version:</strong> <?php echo get_bloginfo( 'version' ); ?><br />
				<strong>Plugin version:</strong> <?php echo self::VERSION; ?><br />
				<strong>Multisite enabled:</strong> <?php if ( is_multisite() ) { echo 'yes'; } else { echo 'no'; } ?><br />
				<strong>Server software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br />
				<strong>PHP version:</strong> <?php echo phpversion(); ?><br />
			</div>
		</div>
		
		<?php
	} // End options_page()
	/* End plugin options callbacks */
	/*
	===== End plugin options =====
	*/
	
	/*
	===== Post/Page Editing =====
	*/
	// Add metabox to post/page editing, remove default Author metabox, and initialize required elements for post/page editing
	function add_metabox() {
		$screens = array( 'post', 'page' ); // Locations where the metabox should show
		
		/* Remove WordPress default Author meta box */
		foreach ( $screens as $screen ) {
			remove_meta_box( 'authordiv', $screen, 'normal' ); // Parameters for removing Author meta box from Post and Page edit screens
		}
		
		/* Iterate through locations to add meta box */
		foreach( $screens as $screen ) {
			add_meta_box(
				self::ID . '-metabox', // Meta box ID
				'Author' . $this->spinner, // Meta box title
				array( $this, 'metabox' ), // Meta box callback (outputs the HTML for the meta box)
				$screen, // Post types where this meta box should be used
				'normal', // Context (placement) on the edit screen
				'high' // Priority within the specified context
			);
		}
	} // End add_metabox()
	
	// Scripts and stylesheets for use with the meta box
	function add_metabox_scripts() {
		// Custom stylesheet for meta box
		wp_enqueue_style(
			self::ID . '-metabox', // Stylesheet hook name
			plugins_url( 'admin/assets/css/edit-post.css', $this->pluginfile ), // URL for stylesheet
			array(), // Style dependencies
			self::VERSION // Plugin version
		);
		
		/* Add script for changing the post author */
		wp_enqueue_script(
			self::ID . '-edit-post', // Registered script handle
			plugins_url( 'admin/assets/js/edit-post.js', $this->pluginfile ), // URL to script
			array( // Script dependencies
				'jquery'
			),
			self::VERSION // Plugin version
		);
		
		// Localize the script for AJAX calls
		wp_localize_script(
			self::ID . '-edit-post', // Name of script call being localized
			self::PREFIX . 'edit_post', // AJAX object namespace, used to call values in the JS file
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ), // URL for admin AJAX calls
				'nonce' => wp_create_nonce( self::ID . '-edit-post-nonce' ) // Nonce to authenticate request
			)
		);
	} // End add_metabox_scripts()
	
	// Meta box
	function metabox( $post ) {
		// Retrieve current values if they exist
		$cc_author_meta = get_post_meta( $post->ID, '_' . self::PREFIX . 'meta', true ); // Author metadata (stored as an array)
		$postauthorid = $post->post_author; // Get the user ID of the post author
		
		// If any of the values are missing from the post, retrieve them from the author's global profile
		if ( ! $cc_author_meta ) {		
			$postauthor = get_userdata( $postauthorid ); // Retrieve the details of the post author
			
			$cc_author_meta = array(); // Initialize main array
			$cc_author_meta[0] = array( // Nested array for author data
				'display_name' => $postauthor->display_name, // Set display name from post author's data
				'description' => $postauthor->description // Set bio from the post author's data
			);
		}
		
		// Display the meta box contents
		?>
		<noscript>
			JavaScript must be enabled to use this feature.
		</noscript>
		<div id="<?php echo self::PREFIX; ?>metabox" class="<?php echo self::PREFIX; ?>metabox" style="display: none;">
			<p>The information below will be saved to this post, and (unless selected) will not be saved to the author's user profile.</p>
			<?php
			if ( current_user_can( 'edit_others_posts' ) || current_user_can( 'edit_others_pages' ) ) { // Check the capabilities of the current user for sufficient privileges
				?>
				<div id="<?php echo self::PREFIX; ?>metabox_postauthor" class="<?php echo self::PREFIX; ?>metabox_postauthor">
				<?php
					wp_dropdown_users( array(
						'name' => self::PREFIX . 'postauthor', // Name for the form item
						'id' => self::PREFIX . 'postauthor', // ID for the form item
						'class'=> self::PREFIX . 'postauthor', // Class for the form item
						'selected' => $postauthorid // Select the post's author to be displayed by default
					) );
					?>
					<input type="hidden" name="<?php echo self::PREFIX; ?>currentpostauthor" value="<?php echo $postauthorid; ?>">
					<input type="hidden" name="<?php echo self::PREFIX; ?>javascript" id="<?php echo self::PREFIX; ?>javascript" value="">
				</div><!-- #cc_author_metabox_postauthor -->
				<?php
			}
			?>
			<label id="label_<?php echo self::PREFIX; ?>meta[0][display_name]" for="<?php echo self::PREFIX; ?>meta[0][display_name]" class="selectit">Name</label>
			<input type="text" name="<?php echo self::PREFIX; ?>meta[0][display_name]" id="<?php echo self::PREFIX; ?>meta[0][display_name]" value="<?php echo esc_attr( $cc_author_meta[0]['display_name'] ); ?>" />
	
			<label for="<?php echo self::PREFIX; ?>meta[0][description]" class="selectit">Bio</label>
			<?
			// Render the editor for biographical info (editor content, editor ID, textarea name)
			$this->editor( $cc_author_meta[0]['description'], self::PREFIX . 'meta_description', self::PREFIX . 'meta[0][description]' );
			?>
			<div class="<?php echo self::PREFIX; ?>meta_update_profile">
				<input type="checkbox" name="<?php echo self::PREFIX; ?>meta[0][update_profile]" id="<?php echo self::PREFIX; ?>meta[0][update_profile]" value="Profile">Update author's default profile
				<p class="description">Checking this will overwrite the author's site-wide user profile with the information you've entered.</p>
			</div>
		</div> <!-- .cc_author_metabox -->
		<?php
	} // End metabox()
	
	// Callback for AJAX call to change the post author
	function change_postauthor_callback() {
		global $wpdb; // Allow access to database
		
		$nonce = $_POST['nonce']; // Assign a local variable for nonce
		
		if ( ! wp_verify_nonce( $nonce, self::ID . '-edit-post-nonce' ) ) { // If the nonce doesn't check out, fail the request
			exit( 'Your request could not be authenticated' ); // Error message for unauthenticated request
		}
		
		if ( current_user_can( 'edit_others_posts' ) || current_user_can( 'edit_others_pages' ) ) { // Check for proper permissions before handling request
			$author = $_POST['authorID']; // Assign local variable for submitted post author
			$authordata = get_userdata( $author ); // Retrieve the selected user's data from their profile
			
			// Encode response as JSON
			$authormeta = json_encode( array(
				'display_name' => $authordata->display_name, // Display name from profile
				'description' => $authordata->description, // Biographical info from profile
			) );
			
			echo $authormeta; // Return the values retrieved from the database
		}
		
		exit; // End response. Required for callback to return a proper result.
	} // End change_postauthor_callback()
	
	// Save the information in the meta box
	function save_meta( $post_id ) {
		// Verify that values have been provided
		if ( isset( $_POST[self::PREFIX . 'meta'] ) ) {
			/*
			If JavaScript is not enabled, get the author's data from their user profile.
			This is because the values in the author name and bio fields will still be for the old author with JS disabled.
			*/
			if ( empty( $_POST[self::PREFIX . 'javascript'] ) ) {
				// Retrieve the details of the post author
				$postauthor = get_userdata( $_POST[self::PREFIX . 'postauthor'] );
			
				// Initialize main array
				$author = array();
					
				// Nested array for author data
				$author[0] = array(
					'display_name' => $postauthor->display_name, // Set display name from post author's data
					'description' => $postauthor->description // Set bio from the post author's data
				);
			}
			// If JavaScript is enabled, use the values submitted from the meta box since they'll have been updated with the new author
			else {
				// Assign POST data to local variable
				$author = $_POST[self::PREFIX . 'meta'];
			}
			
			// Sanitize array values
			foreach ( $author as $authormeta ) {
				foreach ( $authormeta as $key => $meta ) {
					$authormeta['display_name'] = strip_tags( $meta );
				}
			}
			// Save author metadata to post meta
			update_post_meta( $post_id, '_' . self::PREFIX . 'meta', $author );
			
			// If the post author has been changed, update the post author
			if ( ! empty( $_POST[self::PREFIX . 'postauthor'] ) && ( $_POST[self::PREFIX . 'postauthor'] != $_POST[self::PREFIX . 'currentpostauthor'] ) ) {
				// Remove the 'save_post' hook before updating the post author to prevent an infinite loop
				remove_action( 'save_post', array( &$this, 'save_meta' ) );
				wp_update_post( array(
					'ID' => $post_id, // Post ID
					'post_author' => $_POST[self::PREFIX . 'postauthor'] // Use the post author ID from the dropdown
				) );
				// Re-add the 'save_post' hook after the post author is updated
				add_action( 'save_post', array( &$this, 'save_meta' ) );
			}
			
			/* If 'Update Profile' is enabled, save the author info to the user profile of the author */
			foreach ( $author as $authormeta ) {
				foreach ( $authormeta as $key => $meta ) {
					if ( isset( $authormeta['update_profile'] ) ) {
						wp_update_user( array(
							'ID' => $_POST[self::PREFIX . 'postauthor'], // Author user ID
							'display_name' => $authormeta['display_name'], // Display name
							'nickname' => $authormeta['display_name'], // Set nickname to display name
							'description' => $authormeta['description'], // Biographical info
						) );
					}
				}
			}
		}
	} // End save_meta()
	/*
	===== End Post/Page Editing =====
	*/
	
	/*
	===== WYSIWYG Editor =====
	*/
	// Initialize the editor
	function editor_initialize() {
		// Editor ID
		$this->editorid = self::ID . '-user-description';
		
		// Editor settings
		$this->editorsettings = array(
			'media_buttons' => false, // Don't display media upload button
			'teeny' => true, // Keep editor to minimal button options, instead of full editor
			'textarea_rows' => 5, // Number of rows in editor
			'tinymce' => array(
				'toolbar1' => 'bold,italic,underline,strikethrough,link,unlink', // TinyMCE 4.0+: Only show the listed buttons in the editor
				'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,link,unlink' // Legacy TinyMCE setting
			)
		);
	} // End editor_initialize()
	
	// Editor for posts and pages
	function editor( $content, $editor_id, $textarea_name ) {
		// Initialize the editor
		$this->editor_initialize();
		
		// If TinyMCE is available and the user can rich edit, use TinyMCE. Otherwise, use a standard textarea.
		if ( function_exists( 'wp_editor' ) && user_can_richedit() ) {
			// Filter the author description 
			$content = apply_filters( 'the_content', $content );
			
			// Set local variable for editor settings, to allow us to modify the settings for this specific editor
			$editorsettings = $this->editorsettings;
			
			// Set 'textarea_name' in the editor settings
			$editorsettings['textarea_name'] = $textarea_name;
			
			// Create the editor using the provided values
			wp_editor( $content, $editor_id, $editorsettings );
		}
		// If TinyMCE can't be used, use a simple textarea
		else {
			echo '<textarea id="' . $editor_id . '" name="' . $textarea_name . '" rows="5" cols="50" required>' . esc_attr( $content ) . '</textarea>';
		}
	} // End editor()
	
	// Editor for user profile
	function editorprofile( $user ) {
		// Check to make sure the user can use the rich editor
		if ( user_can_richedit() ) {
			// Initialize the editor
			$this->editor_initialize();
			?>
			<noscript>
				You currently have JavaScript disabled, which is why you're seeing duplicate Biographical Info fields and no WYSIWYG. Please enable JavaScript.
			</noscript>
			<table class="form-table">
				<tr>
					<th><label for="description">Biographical Info</label></th>
					<td>
						<?php 
						$description = get_user_meta( $user->ID, 'description', true);
						$description = apply_filters( 'the_content', $description );
						wp_editor( $description, 'description', $this->editorsettings ); 
						?>
						<span class="description">Share a little biographical information to fill out your profile. This may be shown publicly.</span>
					</td>
				</tr>
			</table>
		<?php
		}
	} // End editorprofile()
	
	// Remove filters from biographical info
	function editor_remove_filters() {
		remove_all_filters( 'pre_user_description' );
	} // End editor_remove_filters()
	
	// Load JavaScript for profile
	function profilejs( $hook ) {
		// Only load JS if editing a user
		if ( $hook == 'profile.php' || $hook == 'user-edit.php' ) {
			wp_enqueue_script(
				self::ID . '-edit-user', // Name of script in WordPress
				plugins_url ( 'admin/assets/js/edit-user.js', $this->pluginfile ), // Location of script
				'jquery', // Dependencies
				self::VERSION, // Use plugin version number
				true // Whether to load script in footer
			);
		}
	} // End profilejs()
	/*
	===== End WYSIWYG Editor =====
	*/
	
	/*
	===== Admin initialization =====
	*/
	// Initialize the admin class
	protected function admin_initialize() {
		// Run plugin upgrade
		$this->upgrade();
	} // End admin_initialize()
	
	// Plugin upgrade
	function upgrade() {
		// Check whether the database-stored plugin version number is less than the current plugin version number, or whether there is no plugin version saved in the database
		if ( ! empty( $this->options ) && version_compare( $this->options['dbversion'], self::VERSION, '<' ) ) {
			// Set local variable for options
			$options = $this->options;
			
			// Remove the option for toggling the WYSIWYG editor if it's present
			if ( isset( $options['wysiwyg'] ) ) {
				unset( $options['wysiwyg'] );
			}
			
			/* Update the plugin version saved in the database (always the last step of the upgrade process) */
			// Set the value of the plugin version
			$options['dbversion'] = self::VERSION;
				
			// Save to the database
			update_option( self::PREFIX . 'options', $options );
			/* End update plugin version */
		}
		
		/*
		If the deprecated options entries are present, we need to retrieve those values and assign them to the new structure.
		This upgrade process has to fall outside the standard upgrade version validation since the new options structure is not
		present if the old options structure is present, and therefore would never actually be executed.
		*/
		if ( get_option( self::PREFIX . 'postpage' ) ) {
			// Retrieve the current options from the database
			$postpage = get_option( 'cc_author_postpage' );
			$adminoptions = get_option( 'cc_author_admin_options' );
			
			// Set up the new options structure with old values
			$options = array (
				'perpost' => $postpage['perpost'], // Save author info to each individual post, rather than pulling from global author data
				'relnofollow' => $postpage['relnofollow'], // Add rel="nofollow" to links in bio entries
				'dbversion' => self::VERSION // Save the current plugin version
			);
			
			// Save options to the database
			add_option( self::PREFIX . 'options', $options );
			
			// Delete the old options entries from the database
			delete_option( 'cc_author_postpage' );
			delete_option( 'cc_author_admin_options' );
		}
	} // End upgrade()
	/*
	===== End Admin Initialization =====
	*/
	
	/*
	===== Plugin activation and deactivation methods =====
	*/
	 // Plugin activation
	 public function activate() {
	 	// Check WordPress version for plugin compatibility
	 	if ( version_compare( get_bloginfo( 'version' ), self::WPVER, '<' ) ) {
	 		wp_die( 'Your version of WordPress is too old to use this plugin. Please upgrade to the latest version of WordPress.' );
	 	}
	 	
	 	// Set options for plugin
		$options = array (
			'perpost' => 'yes', // Save author info to each individual post, rather than pulling from global author data
			'relnofollow' => 'yes', // Add rel="nofollow" to links in bio entries
			'dbversion' => self::VERSION // Save the current plugin version
		);
		
		add_option( self::PREFIX . 'options', $options ); // Save options to database
	} // End activate()
	 
	// Plugin deactivation
	public function deactivate() {
		// Remove the plugin options from the database
		delete_option( self::PREFIX . 'options' );
	} // End deactivate()
	/*
	===== End plugin activation and deactivation methods =====
	*/
}
/**
 * End cc_author_admin
 **/
?>