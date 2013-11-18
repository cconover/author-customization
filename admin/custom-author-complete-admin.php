<?php
/**
 * Functions for managing the plugin inside wp-admin
*/


/**
 * Create entry in Settings menu
 * A submenu entry titled 'Custom Authors' is shown under Settings
 */
function cc_cac_create_menu() {
	add_options_page(
		'Custom Author Complete',			// Page title. This is displayed in the browser title bar.
		'Custom Authors',					// Menu title. This is displayed in the Settings submenu.
		'manage_options',					// Capability required to access the options page for this plugin
		'cc-cac',							// Menu slug
		'cc_cac_options_page'				// Function to render the options page
	);
}
add_action( 'admin_menu', 'cc_cac_create_menu' );

/**
 * Options Page
 */
function cc_cac_options_page() {
	/* Prevent users with insufficient permissions from accessing settings */
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '<p>You do not have sufficient permissions to access this page.</p>' );
	}
	?>
	
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Custom Author Complete</h2>

		<form action="options.php" method="post">
			<?php
			// OPTIONS FIELDS
			?>
		</form>
	</div>
	
	<?php	
}
?>