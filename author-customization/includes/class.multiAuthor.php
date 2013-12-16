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
	
	function cc_author_multiauthors() {
		$this->__construct();
	}
	
	/* Main class initialization */
	function init() {
		
	}
	
	/* Register the 'author' taxonomy. Delayed until late in initialization. */
	function late_init() {
		/* Register the author taxonomy */
		$taxonomy_args = array(				// Arguments for the register_taxonomy function
			'hierarchical'		=>	false,	// Non-hierarchical element
			'label'				=>	false,	// No label needed, as this will not be shown in the UI
			'rewrite'			=>	false,	// No rewrite needed, this taxonomy will piggyback off existing taxonomy
			'query_var'			=>	false,	// No need to access this taxonomy through standard queries
			'public'			=>	false,	// Do not display in UI
			'sort'				=>	true	// Sort by the order stored in the term list
		);
		
		/* Determine which post types can support author attribution, and set that list for use in taxonomy registration */
		$post_types_with_author = array_values( get_post_types() ); // Get list of all available post types, and return them as numeric array
		foreach ( $post_types_with_author as $key => $posttype ) { // Cycle through every post type
			if ( !post_type_supports( $posttype, 'author' ) ) { // If post type does not support an author designation
				unset ( $post_types_with_author[$key] ); // Remove the post type from the list of supported post types
			}
		}
		$this->post_types = $post_types_with_author; // Set the class post types to the list created above
		
		register_taxonomy(
			$this->author_taxonomy,			// Taxonomy name. This is the same as Co-Authors Plus for compatibility
			$this->post_types,				// Taxonomy objects
			$taxonomy_args					// Arguments for the taxonomy registration
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