<?php
/*
Code to create WYSIWYG editor for editing user biographical info

Included/Required by:
/admin/includes/edit-post.php
/admin/includes/edit-user.php
*/

/* Edit post/page author editor */
class ccAuthorDescEditor {
	/* Properties */
	public $cc_author_bio_content;
	public $cc_author_bio_editor_id;
	
	/* Construct class variables */
	public function __construct( $cc_author_bio_content, $cc_author_bio_editor_id ) {
		$this->content	= $cc_author_bio_content; // The content that should be in the editor when an object is created
		$this->editorid	= $cc_author_bio_editor_id; // The editor's HTML ID
	} // __construct
	
	/* Create the editor */
	public function editor() {
		$admin_options = get_option( 'cc_author_admin_options' ); // Get plugin's admin options
	
		/* If 'wysiwyg is enabled, show the WYSIWYG editor. Otherwise, show standard textarea. */
		if ( isset( $admin_options['wysiwyg'] ) && function_exists( 'wp_editor' ) ) {
			/* Create the WYSIWYG */
			$settings = $this->editorsettings(); // Editor settings

			$editor = wp_editor( $this->content, $this->editorid, $settings ); // Call the WordPress WYSIWYG
			$editor .= '<span style="color: #FF0000; font-weight: bold;"><noscript>You have JavaScript disabled. The WYSIWYG can\'t run without JavaScript. Please enable JavaScript.</noscript></span>'; // Message to display to users with JavaScript disabled
		}
		else {
			$editor = '<textarea id="' . $this->editorid . '" name="' . $this->editorid . '" rows="5" cols="50" required>' . esc_attr( $this->content ) . '</textarea>'; // Set the editor as a simple textarea
		}
		return $editor; // Display the editor
	} // editor()
	
	/* Editor settings */
	public function editorsettings() {
		$settings = array( // Settings for WYSIWYG
			'media_buttons'		=> false,	// Don't display media upload options
			'quicktags'			=> false,	// Disable quicktags
			'teeny'				=> true,	// Keep editor to minimal button options, instead of full editor
			'textarea_rows'		=> 10,		// Number of rows in editor
			'tinymce'			=> array(
				'theme_advanced_buttons1'	=> 'bold,italic,underline,strikethrough,link,unlink' // Only show the listed buttons in the editor
			),
		);
		
		return $settings;
	} // editorsettings()
} // ccAuthorDescEditor

/* User profile editor */
class ccAuthorDescEditorUser {
	public function __construct() {
		$this->editorid			= 'description'; // The editor's HTML ID
		$this->name				= $this->editorid; // The textarea name is assigned when the object is created
		$this->admin_options	= get_option( 'cc_author_admin_options' ); // Get admin options for the plugin
		
		/* If 'wysiwyg' is enabled in the plugin options, show the WYSIWYG in the profile edit page */
		if ( isset( $this->admin_options['wysiwyg'] ) && function_exists( 'wp_editor' ) ) {
			/* Hook into user profile fields */
			add_action( 'show_user_profile', array( $this, 'editor' ) );
			add_action( 'edit_user_profile', array( $this, 'editor' ) );
		
			add_action( 'admin_init', array( $this, 'remove_filters' ) ); // Remove filters from textarea
		
			add_action( 'admin_enqueue_scripts', array($this, 'loadjs' ) ); // Load JavaScript
		}
	} // _construct
	
	/* Create the editor */
	public function editor( $user ) {
		$admin_options = get_option( 'cc_author_admin_options' ); // Get plugin's admin options
	
		$settings = ccAuthorDescEditor::editorsettings(); // Editor settings
			
		?>
		<div style="color: #FF0000; font-weight: bold;"><noscript>
			You currently have JavaScript disabled, which is why you're seeing duplicate Biographical Info fields and no WYSIWYG. Please enable JavaScript.
		</noscript></div>
		<table class="form-table">
			<tr>
				<th><label for="description">Biographical Info</label></th>
				<td>
					<?php 
					$description = get_user_meta( $user->ID, 'description', true);
					wp_editor( $description, $this->editorid, $settings ); 
					?>
					<p class="description">Share a little biographical information to fill out your profile. This may be shown publicly.</p>
				</td>
			</tr>
		</table>
		<?php
	} // editor()
	
	/* Load JavaScript */
	public function loadjs( $hook ) {
		if ( $hook == 'profile.php' || $hook == 'user-edit.php' ) { // Only load JS if editing a user
			wp_enqueue_script(
				'edit-user-bio-editor', // Name of script in WordPress
				plugins_url ( 'assets/js/edit-user-bio-editor.js', dirname( __FILE__ ) ), // Location of script
				'jquery', // Dependencies
				false, // No version number specified; allow WordPress to handle this
				true // Load script in footer
			);
		}
	} // loadjs( $hook )
	
	/* Remove filters from textarea for description */
	public function remove_filters() {
		remove_all_filters( 'pre_user_description' );
	}
} // ccAuthorDescEditorUser
?>