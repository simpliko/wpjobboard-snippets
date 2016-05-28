<?php
/**
Plugin Name: WPJobBoard Snippets - Post Import Action
Version: 1.0
Author: Greg Winiarski
Description: Executes action after an item is imported.
*/

/**
 * Register action which will be executed when item finishes import
 *
 * Note. This action is executed for every imported item (NOT when WPJB finishes
 * all imports).
 *
 * Possible actions:
 * - wpjb_imported_resume - Resume data imported
 * - wpjb_imported_application - Application data imported
 * - wpjb_imported_job - Job data imported
 * - wpjb_imported_company - Company data imported
 */
add_action( "wpjb_imported_resume", "post_import_action", 10, 3 );

/**
 * Execute custom action when item (in this case Resume) is imported.
 *
 * @param mixed $id         Either int id (in this case Wpjb_Model_Resume::$id), NULL or WP_Error on fail
 * @param bool $is_new      True if Resume was added to DB, false if existing Resume was updated
 * @param stdClass $import  Data used to import
 * @return void
 */
function post_import_action( $id, $is_new, $import ) {
    if( is_numeric( $id ) ) {
        $resume = new Wpjb_Model_Resume( $id );
        // do something here
    }
}
