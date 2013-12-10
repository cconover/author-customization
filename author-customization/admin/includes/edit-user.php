<?php
/*
Functions to include when editing a user profile

Included/Required by:
/admin/author-customization-admin.php
*/


/**
 * Includes
 */
require_once( dirname( __FILE__ ) . '/class.ccAuthorDescEditor.php' ); // File containing bio editor class
/**
 * End Includes
 */
 
$descEditorUser = new ccAuthorDescEditorUser();
?>