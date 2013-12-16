<?php
/*
Class for managing multiple authors for posts & pages inside WordPress admin
*/

class multiAuthorAdmin {
	public $cc_author_meta, $cc_author_postauthor; // Declare public variables
	
	/* Class constructor */
	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) ); // Hook into 'admin_init'
		add_action( 'save_post', array( $this, 'cc_author_multiauthor_update_post' ) ); // Action when saving/updating a post
	}
	
	/* Initialize for admin */
	function admin_init() {
		
	}
	
	/* Save/update post */
	function cc_author_multiauthor_update_post( $post, $post_id ) {
		
	}
}
?>