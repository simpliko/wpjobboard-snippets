<?php
/**
Plugin Name: WPJobBoard Snippets - CSS Snippets
Version: 1.0
Author: Greg Winiarski
Description: A bunch of CSS files which customize different parts of WPJB
*/

add_action("init", "css_snippets_init");
add_action("wp_footer", "css_snippets_footer");

/**
 * Init action
 * 
 * Registers various stylesheets
 */
function css_snippets_init() {
    
    if( defined( "WPJB_SNIPPETS" ) ) {
        $plugin_url = plugins_url() . "/wpjobboard-snippets/css-snippets/";
    } else {
        $plugin_url = plugins_url() . "/css-snippets/";
    }
    
    wp_register_style("css-snippets-single-job", $plugin_url."single-job.css");
}

/**
 * Enqueues snippets in the footer
 * 
 * This function is called using wp_footer action
 * 
 * @see wp_footer action
 */
function css_snippets_footer() {
    if(is_singular('job')) {
        wp_enqueue_style( 'css-snippets-single-job');
    }
}