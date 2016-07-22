<?php
/**
Plugin Name: WPJobBoard Snippets - Register Using Email
Version: 1.0
Author: Greg Winiarski
Description: Requires WPJB 4.4.3. Removes "Username" field from registration forms and allows to use email as login.
*/

add_filter("wpjr_form_init_resume", "register_using_email_as_login");
add_filter("wpjr_form_init_register", "register_using_email_as_login");
add_filter("wpjb_form_init_company", "register_using_email_as_login");
add_filter("wpja_form_init_company", "register_using_email_as_login");

/**
 * Modifies registration form.
 * 
 * If currently loaded form is registration form then this function does few things:
 * - removes Username field (field with name "user_login")
 * - moves password fields to the end of the form
 * - validates user_email field as a usersname (and email) to make sure this email
 *   is not already being used as a username
 * 
 * @since 1.0
 * @param Daq_Form_ObjectAbstract $form
 * @return Daq_Form_ObjectAbstract 
 */
function register_using_email_as_login($form) {

    if($form->getId() > 0) {
        return $form;
    }

    if($form->hasElement("user_login")) {
        $form->removeElement("user_login");
    }

    if($form->getGroup("auth") !== null) {
        $form->getGroup("auth")->setOrder(10000);
    }

    if($form->hasElement("user_email")) {
        $form->getElement("user_email")->addValidator(new Daq_Validate_WP_Username);
    }

    return $form;
}