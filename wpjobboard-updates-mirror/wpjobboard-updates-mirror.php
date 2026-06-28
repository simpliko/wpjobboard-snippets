<?php
/*
 * Plugin Name: WPJobBoard - Updates Mirror
 * Plugin URI: http://wpjobboard.net/
 * Description: This plugin changes the default updates URL to a mirror site. Use this plugin only if you are having a problem with activating licenses for extensions.
 * Author: Greg Winiarski
 */

add_action( "plugins_loaded", function() {
    if( defined( "WPJOBBOARD" ) ) {
        include_once Wpjb_Project::getInstance()->getBaseDir() . "/application/libraries/Upgrade/Manager.php";
        Wpjb_Upgrade_Manager::$url = "https://mirror.wpjobboard.net/";
    }
} );