<?php
/**
Plugin Name: WPJobBoard Snippets - Password Reminder Link
Version: 1.0
Author: Greg Winiarski
Description: This plugin will add "Remind Password" link to the WPJB login forms.
*/

// The code below you can paste in your theme functions.php or create
// new plugin and paste the code there.

add_filter("wpjb_shortcode_login", "wpjb_snipp_password_reminder_link");

/**
 * Adds "Remind Password" link to WPJB login forms.
 *
 * This function uses wpjb_shortcode_login filter to create and insert
 * remind password link.
 *
 * @param stdClass View object to be updated
 * @return stdClass Updated View object
 */
function wpjb_snipp_password_reminder_link($view) {

    $buttons = $view->buttons;
    
    // Add another button (technically this can be any HTML tag)
    $buttons[] = array(
        "tag" => "a", 
        "href" => wp_lostpassword_url(), 
        "html" => "Remind Password"
    );
    // Note you cannot modify $view->buttons array directly, 
    // you can only assign value to it.
    $view->buttons = $buttons;
    return $view;
}
