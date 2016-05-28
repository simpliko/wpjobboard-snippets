<?php
/**
Plugin Name: WPJobBoard Snippets
Version: 1.0
Author: Greg Winiarski
Description: This is collection of useful and practical WPJobBoard snippets.
*/

define( "WPJB_SNIPPETS", true );

$file = dirname( __FILE__ ) . "/wpjobboard-tester.php";

if( is_file( $file ) ) {
    include_once $file;
}
