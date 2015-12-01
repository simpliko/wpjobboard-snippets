<?php
/**
Plugin Name: WPJobBoard Snippets - Custom Application Status
Version: 1.0
Author: Greg Winiarski
Description: This plugin adds new application status. Requires WPJobBoard 4.3.4 or newer.
*/

// 'wpjb_application_status' will allow to register new application status
add_filter("wpjb_application_status", "application_status_register");

/**
 * Registers new job application status
 * 
 * 
 * 
 * @param array $list List of application statuses
 * @return array Updated list of applications statuses
 */
function application_status_register( $list ) {

    /**
     * @param array $list {
     *     Array of key value parameters
     *      
     *     @type integer    $id                     Number between 4 and 9 (use 0 to 3 if you want to overwrite 
     *                                              one of default statuses
     *     @type string     $key                    Text uniquely identifying status use only a-z and _ characters
     *                                              (example good keys could be 'accepted', 'waiting', 'invalid')
     *     @type string     $color                  Any HTML valid color can be HEX "#cccccc" or a word "blue", 
     *                                              this will be used as background when displaying application status
     *                                              on site.
     *     @type string     $bulb                   one of wpjb-bulb-expired, wpjb-bulb-expiring, wpjb-bulb-active, 
     *                                              wpjb-bulb-new, wpjb-bulb-awaiting, wpjb-bulb-pending, wpjb-bulb-inactive 
     *                                              wpjb-bulb-gray. Each of the "bulbs" represents a color. If you are not 
     *                                              sure which bulb to use, provide "color" prarm instead. Do NOT use both
     *                                              'color' and 'bulb' params.
     *     @type string     $label                  Status name visible to users browsing applications
     *     @type integer    $public                 1 or 0. if 1 the applications with this status will be visible 
     *                                              in Employer panel otherwise only in wp-admin
     *     @type string     $notify_applicant_email Name of an email that will be sent when application status 
     *                                              is set to this status
     *     @type array      $labels                 {
     *         Array of labels with some wording
     * 
     *         @type string     $multi_success      Text displayed when admin changes bulk number of application statuses 
     *                                              to this status. Note the {success} is a variable which will be replaced
     *                                              with number of applications that were modified successfully.
     *     }
     * 
     *     @type array      $callback               {
     *          Array of callback function 
     * 
     *          @type string    $multi              Callback function executed when admin changes bulk number of application
     *                                              statuses to this status. Note that this function is executed for each 
     *                                              application. So if you change 5  applications this function will be run
     *                                              5 times, each time with different $id as param. The $id is a
     *                                              Wpjb_Model_Application::$id.
     *     }
     *      
     *  
     * }
     */
    
    $list[4] = array(
        "id" => 4,
        "key" => "waiting",
        "color" => "brown",
        "bulb" => null,
        "label" => "Waiting",
        "public" => 0,
        "notify_applicant_email" => null,
        "labels" => array(
            "multi_success" => __("Number of applications set to 'Waiting': {success}", "wpjobboard"),
        ),
        "callback" => array(
            "multi" => "application_status_set_to_waiting"
        )
    );

    return $list;
}

/**
 * Changes application status to 'waiting'
 * 
 * @param integer $id Wpjb_Model_Application::$id
 * @return boolean True if status was changed, fasle otherwise
 */
function application_status_set_to_waiting( $id ) {

    $object = new Wpjb_Model_Application($id);
    $object->status = 4;
    $object->save();
    
    // Note: doing $object->save() will trigger sending 'notify_applicant_email' notification
    // configured in application_status_register() function.
    
    // do something more here ...
    
    return true;
}