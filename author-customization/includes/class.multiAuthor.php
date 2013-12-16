<?php
/*
Class for managing multiple authors for posts & pages inside WordPress admin
This class uses code from the WordPress plugin Co-Authors Plus (http://wordpress.org/plugins/co-authors-plus/)
I've done this both to maintain compatibility with their data structure, and also so I don't have to reinvent the wheel.
*/

class multiAuthor {
	/* Variables for use within the class */
	var $author_taxonomy = 'author';
	var $post_types = array();
	
	public $cc_author_meta, $cc_author_postauthor;
	
	/* Class constructor */
	function __construct() {
		/* Initialization */
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'late_init' ), 100 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		
		add_action( 'save_post', array( $this, 'cc_author_multiauthor_update_post' ) ); // Action when saving/updating a post
	}
	
	/* Class initialization */
	function init() {
		/* Register the author taxonomy */
		$taxonomy_args = array( // Arguments for the register_taxonomy function
			
		);
		register_taxonomy(
			$this->author_taxonomy,		// Taxonomy name. This is the same as Co-Authors Plus for compatibility
			$this->post_types,			// Taxonomy objects
			$taxonomy_args				// Arguments for the taxonomy registration
		);
		/* End register the author taxonomy */
	}
	
	/* Initialize for admin */
	function admin_init() {
		
	}
	
	/* Save/update post */
	function cc_author_multiauthor_update_post( $post, $post_id ) {
		
	}
}
?>