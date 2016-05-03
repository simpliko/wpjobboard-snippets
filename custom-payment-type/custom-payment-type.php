<?php
/**
Plugin Name: WPJobBoard Snippets - Custom Payment Type
Version: 1.0
Author: Greg Winiarski
Description: This plugin explains how to create a payment type which will allow to charge candidate memberships.
*/

// init pricing config form
add_action( "init", "custom_payment_type_init");
// register new pricing type
add_filter( "wpjb_pricing_list", "custom_payment_type_list" );
// pricing edit link
add_action( "wpjb_payment_for", "custom_payment_type_for" );
// generate object id and type for payment
add_filter( "wpjb_payment_object", "custom_payment_type_object", 10, 2 );
// accept pricing
add_filter( "wpjb_payment_accept", "custom_payment_type_accept", 10, 2 );
// display custom payment success message
add_filter( "wpjb_payment_success_messages", "custom_payment_type_messages", 10, 2 );
// shortcode where we can test the payment method
add_shortcode( "custom_payment_type_form", "custom_payment_type_form" );

/**
 * Register class which will allow to configure payments
 *
 * This class is used in wp-admin / Settings (WPJB) / Pricing panel when editing
 * or adding your custom payment type.
 *
 * @return void
 */
function custom_payment_type_init( ) {

    // this class will handle  payment configuration (price, duration, etc.)
    class Custom_Payment_Type_Config_Form extends Wpjb_Form_Admin_Pricing
    {
        public function init()
        {
            $e = $this->create("price_for", "hidden");
            $e->setValue(210);
            $this->addElement($e);

            parent::init();
        }

    }
}

/**
 * Register new payment type
 *
 * @param array $list List of payment types
 * @return array Modified $list
 */
function custom_payment_type_list( $list ) {
    $list[] = array(
        "id" => 210,
        "name" => "my-candidate-membership",
        "form" => "Custom_Payment_Type_Config_Form",
        "title" => "Candidate Membership"
    );
    return $list;
}

/**
 * Generate edit link for wp-admin / Job Board / Payments panel.
 *
 * @param $item Wpjb_Model_Payment
 * @return void
 */
function custom_payment_type_for( $item ) {
    $request = Daq_Request::getInstance();
    $action = $request->get("action");

    $pricing = new Wpjb_Model_Pricing($item->pricing_id);
    $wrap = "span";

    if( $pricing->price_for != 210 ) {
        return;
    }

    if( $action != "edit" ) {
        // show icon only on payments list page
        $text = sprintf('Candidate Membership (ID %d)', $item->id);
        $icon = sprintf('<span class="wpjb-glyphs wpjb-icon-users" style="vertical-align: top" title="%s"></span>', $text);
        $wrap = "strong";
        echo $icon;
    }

    $object = new Wpjb_Model_Resume($item->object_id);

    $user = $object->getUser(true);

    $object_url = esc_attr(wpjb_admin_url("resumes", "edit", $object->id));
    $object_title = esc_html(trim($user->display_name));

    $title = '<%1$s><a href="%2$s">%3$s</a></%1$s>';

    printf( $title, $wrap, $object_url, $object_title );
}

/**
 * Set object id and type when creatng a payment
 *
 * @param array $data Array("id"=>..., "type"=>...);
 * @param Wpjb_Model_Pricing $pricing Pricing object
 * @return array
 */
function custom_payment_type_object( $data, $pricing ) {

    if($pricing->price_for != 210) {
        return $data;
    }

    return array(
        "id" => Daq_Request::getInstance()->post("object_id"),
        "type" => 9 // <= set your object type here (9 - 250)
    );
}

/**
 * Accepts payment made by user
 *
 * This function is executed by wpjb_payment_accept filter in Wpjb_Model_Payment::accepted()
 *
 * @see wpjb_payment_accept filter
 * @see Wpjb_Model_Payment::accepted();
 *
 * @param boolean $accepted
 * @param Wpjb_Model_Payment $payment Payment object
 * @return boolean True if payment was accepted properly, false otherwise
 */
function custom_payment_type_accept( $accepted, $payment ) {
    if( $accepted === true) {
        // most likely one of default payment types
        return true;
    }

    $pricing = new Wpjb_Model_Pricing($payment->pricing_id);
    $list = new Wpjb_List_Pricing();
    $listing = $list->getBy("id", $pricing->price_for);

    if( is_null($listing) || $listing['id'] != 210 ) {
        return $accepted;
    }

    $payment->log("Success!!!");

    return true;
}

/**
 * Payment form which allows to make payment for candidate membership
 *
 * Always use this shortcode with pricing_id param for example
 * [custom_payment_type_form pricing_id="100"]
 * where 100 is actual ID of pricing created in wp-admin / Settings (WPJB) / Pricing
 * panel.
 *
 * @param array $atts List of shortcode params
 * @return string Rendered payment form
 */
function custom_payment_type_form( $atts ) {
    $params = shortcode_atts(array(
        "pricing_id" => null,
    ), $atts);

    $flash = new Wpjb_Utility_Session();
    $pricing = new Wpjb_Model_Pricing($params["pricing_id"]);

    if( !$pricing->exists() ) {
        return;
    }

    if( $pricing->price_for != 210 ) {
        return;
    }

    $list = new Wpjb_List_Pricing();
    $listing = $list->getBy("id", $pricing->price_for);

    $view = Wpjb_Project::getInstance()->getApplication("frontend")->getView();
    $view->atts = $atts;
    $view->pricing = $pricing;
    $view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
    $view->pricing_item = $listing["title"] . " &quot;" . $pricing->title . "&quot;";
    $view->defaults = new Daq_Helper_Html("span", array(
        "id" => "wpjb-checkout-defaults",
        "class" => "wpjb-none",

        "data-pricing_id" => $pricing->id,
        "data-object_id" => Wpjb_Model_Resume::current()->id,
        "data-fullname" => wp_get_current_user()->display_name,
        "data-email" => wp_get_current_user()->user_email,

    ), " ");

    Wpjb_Project::getInstance()->placeHolder = $view;

    wp_enqueue_style("wpjb-css");
    wp_enqueue_script('wpjb-js');

    ob_start();
    $view->render("../default/payment.php");
    $render = ob_get_clean();


    return $render;
}

/**
 * Allows to display custom payment success message
 *
 * @param array $messages List of success messages
 * @param Wpjb_Model_Payment $payment Current payment object
 * @return string Rendered payment form
 */
function custom_payment_type_messages($messages, $payment) {

    $pricing = new Wpjb_Model_Pricing($payment->pricing_id);
    $list = new Wpjb_List_Pricing();
    $listing = $list->getBy("id", $pricing->price_for);

    if( is_null($listing) || $listing['id'] != 210 ) {
        return $messages;
    }

    $messages = array();
    $messages[] = "<strong>Thank you for submitting your order.</strong>";
    // more $messages[] here ...

    return $messages;
}
