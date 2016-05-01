<?php
/**
Plugin Name: WPJobBoard Snippets - User Panel API
Version: 1.0
Author: Greg Winiarski
Description: This plugin explains how to modify panels in Employer and Candidate dashboards.
*/

// ********************************************* //
//          Customizing Employer Panel           //
// ********************************************* //

add_action( "wpjb_employer_panel_heading", "user_panel_api", 10, 2 );
add_filter( "wpjb_employer_panel_content", "user_panel_api_content" );
add_filter( "wpjb_employer_panel_links", "user_panel_api_links" );

// ********************************************** //
//          Customizing Candidate Panel           //
// ********************************************** //

add_action( "wpjb_candidate_panel_heading", "user_panel_api", 10, 2 );
add_filter( "wpjb_candidate_panel_content", "user_panel_api_content" );
add_filter( "wpjb_candidate_panel_links", "user_panel_api_links" );


/**
 * Insert additional text / data above or below employer dashboard.
 *
 * This function allows to insert some additional data on the employer dashboard.
 *
 * @param string $part Either "top" or "bottom"
 * @return void
 */
function user_panel_api( $part ) {
    if( $part == "top" ) {
        echo '<p>Hello <strong>' . wp_get_current_user()->display_name . '</strong>!</p>';
    }
    if( $part == "bottom" ) {
        echo '<p>Today is ' . date( "Y-m-d" ) . '.</p>';
    }
}

/**
 * This function allows to add, edit and remove links from Employer / Candidate Panel home.
 *
 * In the example below we exaplin how to remove a membership link and add
 * your own new link.
 *
 * @param array $dashboard List of User Panel links
 * @return array Modified list of User Panel links
 */
function user_panel_api_links( $dashboard ) {

    // remove membership link
    // full structure of $dashbaord you can find in:
    // Employer wpjobboard/application/libraries/Module/Frontend/Employer.php homeAction()
    // Candidate wpjobboard/application/libraries/Module/Resumes/Index.php homeAction()
    if( isset( $dashboard["manage"]["links"]["membership"] ) ) {
        unset( $dashboard["manage"]["links"]["membership"] );
    }

    // add new link
    $dashboard["manage"]["links"]["my_link"] = array(
        "url" => get_permalink() . "?panel=my_link",
        "title" => "My Link",
        "icon" => "wpjb-icon-link"
    );
    // list of availabel icons you can find at http://fontello.com/ in FontAwesome icons.
    // hover on icon to find its name (for example "link"), then prefix it with "wpjb-icon-"
    // to get a valid icon name whic WPJB will be able to recognize (for example "wpjb-icon-link")

    return $dashboard;
}

/*
 * Change Employer / Candidate Panel shortode content before its rendered.
 *
 * @param $content mixed Either content to display or false
 * @return mixed Either content to display or fals
 */
function user_panel_api_content( $content ) {
    $request = Daq_Request::getInstance();

    // URL does not have ?panel=my_link param, this means some other panel is being executed
    // return default content then.
    if( $request->get("panel") != "my_link" ) {
        return $content;
    }

    // Make sure to authenticate user!

    // Load default WPJB styles
    wp_enqueue_style("wpjb-css");

    // Create breadcrumbs, basically two links:
    // - User Panel home
    // - Current page
    $breadcrumbs = array(
        array(
            "title" => __("Home", "wpjobboard"),
            "url" => get_permalink(),
            "glyph"=>"wpjb-icon-home"
        ),
        array(
            "title"=> "My Link",
            "url" => get_permalink() . "?panel=my_link",
            "glyph"=> is_rtl() ? "wpjb-icon-left-open" : "wpjb-icon-right-open"
        )
    );

    // initiate view object and add new templates directory so we can load
    // a custom template file.
    $view = Wpjb_Project::getInstance()->getApplication("frontend")->getView();
    $view->addDir(dirname(__FILE__), true);
    $view->breadcrumbs = $breadcrumbs;

    // initatte session to allow flash messages
    $flash = new Wpjb_Utility_Session();
    $flash->addInfo("Custom My Link Panel Loaded!");
    $flash->save();

    // render template
    ob_start();
    $view->render("custom-template.php");
    $render = ob_get_clean();

    return $render;
}
