<?php
/**
Plugin Name: WPJobBoard Snippets - Full Candidate Registration
Version: 1.0
Author: Greg Winiarski
Description: Requires WPJB 4.4.3. This plugin replaces default simple [wpjb_candidate_register] form with advanced candidate registration which allows to fill whole resume while registering.
*/

add_action("init", "full_candidate_register_init", 100);
add_action("init", "full_candidate_register_action", 100);

/**
 * Initiates Plugin
 * 
 * - updates default [wpjb_candidate_register] shortcode with extended version.
 * - registers update Resume form model (Wpjb_Form_Resume_Alt)
 * 
 * @since 1.0
 * @return void
 */
function full_candidate_register_init() {

    if(!is_admin()) {
        add_shortcode("wpjb_candidate_register", "full_candidate_register_ext");
        add_filter("wpjr_form_init_resume", "full_candidate_register_form_init", 5);
    }

    /**
     * Extended Resume Form Model
     * 
     * @since 1.0
     * @access public
     */
    class Wpjb_Form_Resume_Alt extends Wpjb_Form_Resume {
        
        /**
         * Saves resume data
         * 
         * @param array $append Additional data to save
         * @return int Resume ID
         */
        public function save($append = array()) {

            $user_email = $this->getElement("user_email")->getValue();

            if(!$this->hasElement("user_login")) {
                $user_login = $user_email;
            } else {
                $user_login = $this->getElement("user_login")->getValue();
            }

            $id = wp_insert_user(array(
                "user_login" => $user_login,
                "user_email" => $user_email,
                "user_pass" => $this->getElement("user_password")->getValue(),
                "first_name" => $this->getFieldValue("first_name"),
                "last_name" => $this->getFieldValue("ldap_start_tlsname"),
                "role" => "subscriber"
            ));

            $fullname = $this->value("first_name")." ".$this->value("last_name");

            if(wpjb_conf("cv_approval") == 1) {
                $active = 0; // manual approval
            } else {
                $active = 1;
            }

            $resume = new Wpjb_Model_Resume();
            $resume->candidate_slug = Wpjb_Utility_Slug::generate(Wpjb_Utility_Slug::MODEL_RESUME, $fullname);
            $resume->phone = "";
            $resume->user_id = $id;
            $resume->headline = "";
            $resume->description = "";
            $resume->created_at = date("Y-m-d");
            $resume->modified_at = date("Y-m-d");
            $resume->candidate_country = wpjb_locale();
            $resume->candidate_zip_code = "";
            $resume->candidate_state = "";
            $resume->candidate_location = "";
            $resume->is_public = wpjb_conf("cv_is_public", 1);
            $resume->is_active = $active;
            $resume->save();
            $resume->cpt();

            $this->setObject($resume);

            apply_filters("wpjr_form_save_register", $this);

            parent::save($append);

            $resume->created_at = current_time("mysql");
            $resume->candidate_slug = Wpjb_Utility_Slug::generate(Wpjb_Utility_Slug::MODEL_RESUME, $fullname);
            $resume->save();

            return $resume->id;
        }

        /**
         * Saves resume details (experience and education) in database
         * 
         * @access public
         * @return void
         */
        public function saveDetails()
        {
            foreach($this->_detail as $key => $detail) {
                if($detail["delete"] == true) {
                    $this->_detail[$key]["form"]->getObject()->delete();
                    unset($this->_detail[$key]);
                } else {
                    $this->_detail[$key]["form"]->getElement("resume_id")->setValue($this->getObject()->id);
                    $this->_detail[$key]["form"]->save();
                    $id = $this->_detail[$key]["form"]->getObject()->id;
                    $this->_detail[$key]["form"]->getElement("id")->setValue($id);
                }
            }
        }
    }
}

/**
 * Saves candidate in DB
 * 
 * This function is executed in "init" action.
 * 
 * Validates and saves resume in database. This action is executed only if
 * there is $_POST["_wpjb_action"] == reg_candidate_alt.
 * 
 * @since 1.0
 * @return void
 */
function full_candidate_register_action() {

    $form = new Wpjb_Form_Resume_Alt();
    $request = Daq_Request::getInstance();
    $flash = new Wpjb_Utility_Session();

    if($request->post("_wpjb_action") != "reg_candidate_alt") {
        return;
    }

    $isValid = $form->isValid($request->getAll());

    if(!$isValid) {
        return;
    }

    $form->save();

    $url = wpjr_link_to("login");
    $password = $form->value("user_password");
    $email = $form->value("user_email");
    $username = $form->value("user_login");
    if(empty($username)) {
        $username = $email;
    }

    $mail = Wpjb_Utility_Message::load("notify_canditate_register");
    $mail->setTo($email);
    $mail->assign("username", $username);
    $mail->assign("password", $password);
    $mail->assign("login_url", $url);
    $mail->send();

    do_action("wpjb_user_registered", "candidate");

    $form = new Wpjb_Form_Resumes_Login();
    if($form->hasElement("recaptcha_response_field")) {
        $form->removeElement("recaptcha_response_field");
    }

    $form->isValid(array(
        "user_login" => $username,
        "user_password" => $password,
        "remember" => 0
    ));

    $flash->addInfo(__("You have been registered.", "wpjobboard"));
    $flash->save();

    wp_redirect(wpjr_link_to("myresume_home"));
    exit;
}

/**
 * Adds registration fields to Wpjb_Form_Resume form.
 * 
 * The form needs additional registration fields as by default it is not equipped
 * to handle user registration.
 * 
 * Functiona is applied using wpjr_form_init_resume filter.
 * 
 * @see wpjr_form_init_resume filter
 * 
 * @since 1.0
 * @param Daq_Form_ObjectAbstract $form
 * @return Daq_Form_ObjectAbstract
 */
function full_candidate_register_form_init( $form ) {
    if($form->getId() > 0) {
        return $form;
    }

    $form->addGroup("auth", __("Account", "wpjobboard"), 0);

    $e = $form->create("_wpjb_action", "hidden");
    $e->setValue("reg_candidate_alt");
    $form->addElement($e, "_internal");

    $e = $form->create("user_login");
    $e->setOrder(1);
    $e->setLabel(__("Username", "wpjobboard"));
    $e->setRequired(true);
    $e->addFilter(new Daq_Filter_Trim());
    $e->addFilter(new Daq_Filter_WP_SanitizeUser());
    $e->addValidator(new Daq_Validate_WP_Username());
    $form->addElement($e, "auth");

    $e = $form->create("user_password", "password");
    $e->setOrder(1.01);
    $e->setLabel(__("Password", "wpjobboard"));
    $e->addFilter(new Daq_Filter_Trim());
    $e->addValidator(new Daq_Validate_StringLength(4, 32));
    $e->addValidator(new Daq_Validate_PasswordEqual("user_password2"));
    $e->setRequired(true);
    $form->addElement($e, "auth");

    $e = $form->create("user_password2", "password");
    $e->setOrder(1.02);
    $e->setLabel(__("Password (repeat)", "wpjobboard"));
    $e->setRequired(true);
    $form->addElement($e, "auth");

    return $form;
}

/**
 * Generates content for [wpjb_candidate_register] shortcode.
 * 
 * This function replaces default [wpjb_candidate_register] shortcode with form
 * which renders full My Resume form.
 * 
 * @param array $atts   Shortcode params
 * @return string       Shortcode HTML
 */
function full_candidate_register_ext( $atts = array() ) {

    $params = shortcode_atts(array(
        "job_id" => null
    ), $atts);

    $request = Daq_Request::getInstance();
    $view = Wpjb_Project::getInstance()->getApplication("resumes")->getView();

    if(get_current_user_id() > 0) {
        $view->_flash->addError(__("You are already registered.", "wpjobboard"));
        ob_start();
        wpjb_flash();
        return ob_get_clean();
    }

    wp_enqueue_script("jquery");
    wp_enqueue_script("wpjb-js");
    wp_enqueue_script("wpjb-myresume");
    wp_enqueue_script("wpjb-plupload");
    wp_enqueue_style("wpjb-css");

    $form = new Wpjb_Form_Resume_Alt();

    if(isset($_POST) && !empty($_POST)) {
        if($form->isValid($request->getAll())) {
            // do nothing
        } else {
            $view->_flash->addError(__("There are errors in your form.", "wpjobboard"));
        }

    }

    $form->buildPartials();

    $view->form = $form;
    $view->submit = __("Send Application", "wpjobboard");
    $view->breadcrumbs = array();
    $view->resume = new Wpjb_Model_Resume();
    $view->shortcode = true;

    if(Wpjb_Project::getInstance()->placeHolder === null) {
        Wpjb_Project::getInstance()->placeHolder = new stdClass();
    }

    Wpjb_Project::getInstance()->placeHolder->_flash = $view->_flash;

    add_filter("wpjb_breadcrumbs", "__return_empty_string");

    ob_start();
    ?>
    <style type="text/css">
    .wpjb.wpjr-page-my-resume #wpjb-resume.wpjb-form > fieldset:nth-of-type(1) {
        display: none;
    }
    </style>
    <?php
    $view->render("my-resume.php");
    return str_replace('"Update"', '"Register"', ob_get_clean());
}