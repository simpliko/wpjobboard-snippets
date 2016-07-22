<?php
/**
Plugin Name: WPJobBoard Snippets - Upload From Dropbox
Version: 1.0
Author: Greg Winiarski
Description: Allows to upload files from your Dropbox account.
*/

add_action( "init", "upload_from_dropbox_init" );
add_action( "admin_init", "upload_from_dropbox_admin_init" );

/**
 * Init Upload From Dropbox
 *
 * This function registers JavaScript files, loads domain and adds "Dropbox ..."
 * button to file upload field.
 *
 * @since 1.0
 * @return void
 */
function upload_from_dropbox_init() {

    if( defined( "WPJB_SNIPPETS" ) ) {
        $plugin_url = plugins_url() . "/wpjobboard-snippets/upload-from-dropbox/";
    } else {
        $plugin_url = plugins_url() . "/upload-from-dropbox/";
    }

    load_plugin_textdomain( "upload_from_dropbox", false, "upload-from-dropbox/languages" );
    wp_register_script( 'upload-from-dropbox', $plugin_url . '/upload-from-dropbox.js', array(), null, true );

    add_action( "wpjb_form_field_upload_buttons", "upload_from_dropbox_button", 10, 2 );
    add_filter( "wpja_form_init_config_main", "upload_from_dropbox_config" );
}

/**
 * Init Admin and AJAX Upload From Dropbox
 *
 * This function registers AJAX actions for Upload From Dropbox
 *
 * @since 1.0
 * @return void
 */
function upload_from_dropbox_admin_init() {
    add_action('wp_ajax_upload_from_dropbox', 'upload_from_dropbox_ajax');
    add_action('wp_ajax_nopriv_upload_from_dropbox', 'upload_from_dropbox_ajax');
}

/**
 * Adds "Dropbox ..." button to file upload field.
 *
 * This function is executed by "wpjb_form_field_upload_buttons" action.
 *
 * @see wpjb_form_field_upload_buttons action
 *
 * @param $e Daq_Form_Element
 * @param $form Daq_Form_ObjectAbstract
 * @since 1.0
 * @return void
 */
function upload_from_dropbox_button( $e, $form ) {

    // load all JavaScript Dropbox related libraries
    if( ! has_action( "wp_footer", "upload_from_dropbox_footer" ) ) {
        add_action( "wp_footer", "upload_from_dropbox_footer", 5 );
        add_action( "admin_footer", "upload_from_dropbox_footer", 5 );
        wp_enqueue_script( 'upload-from-dropbox' );
    }

    // select button class
    if(is_admin()) {
        $bclass = "button";
    } else {
        $bclass = "wpjb-button";
    }

    // add button
    ?>
    <a href="#" id="<?php esc_attr_e('wpjb-upload-media-'.$e->getName()) ?>" class="wpjb-upload-dropbox <?php echo $bclass ?>"><span class="wpjb-glyphs wpjb-icon-dropbox"></span><?php _e("Dropbox ...", "upload-from-dropbox") ?></a>
    <?php
}

/**
 * Load Dropbox script
 *
 * This script cannot be loaded using wp_enquque_script because it needs to have
 * some additional attributes in <script> tag.
 *
 * This function is being called by upload_from_dropbox_button() function.
 *
 * @since 1.0
 * @return void
 */
function upload_from_dropbox_footer() {
    $key = wpjb_conf('upload_from_dropbox_app_key', '0');
    printf('<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="%s"></script>', $key);
}

/**
 * AJAX File Upload
 *
 * This function copies file from Dropbox and tries to upload it using default
 * WPJB uploader.
 *
 * @see Wpjb_Module_AjaxNopriv_Main::uploadAction()
 *
 * @since 1.0
 * @return void
 */
function upload_from_dropbox_ajax() {

    global $blog_id;

    if($blog_id > 1) {
        $bid = "-".$blog_id;
    } else {
        $bid = "";
    }

    $dir = wp_upload_dir();
    $d = array();
    $d["baseurl"] = $dir["baseurl"]."/wpjobboard{$bid}-upload-from-dropbox";
    $d["basedir"] = $dir["basedir"]."/wpjobboard{$bid}-upload-from-dropbox";

    if(!wp_mkdir_p($d["basedir"])) {
        $response->msg = sprintf(__("Upload directory %s could not be created.", "wpjobboard"), $dir);
        die(json_encode($response));
    }

    $file = Daq_Request::getInstance()->post("data");
    $response = wp_remote_get($file["link"]);
    $new_file = $d["basedir"] . "/" . $file["name"];

    if( is_wp_error( $response ) ) {
        $response->msg = $response->get_error_message();
        die(json_encode($response));
    }

    file_put_contents( $new_file, $response["body"] );

    $stat = @stat( dirname( $new_file ) );
    $perms = $stat['mode'] & 0007777;
    $perms = $perms & 0000666;
    @ chmod( $new_file, $perms );
    clearstatcache();

    if( ! isset($_FILES) || ! is_array($_FILES) ) {
        $_FILES = array();
    }

    $type = wp_check_filetype($new_file, wp_get_mime_types());

    $_FILES["file"] = array(
        "name" => $file["name"],
        "type" => $type["type"],
        "tmp_name" => $new_file,
        "error" => UPLOAD_ERR_OK,
        "size" => $file["bytes"]
    );

    add_filter( "daq_move_uploaded_file", "upload_from_dropbox_move", 10, 3 );

    Wpjb_Module_AjaxNopriv_Main::uploadAction();
    exit;
}

/**
 * Replace upload method
 *
 * Use rename() function instead of move_uploaded_file() when uploading
 * files from Dropbox.
 *
 * @see daq_move_uploaded_file filter
 *
 * @since 1.0
 * @return void
 */
function upload_from_dropbox_move( $result, $tmp_file, $new_file ) {
    if( ! $result && stripos( $tmp_file, '-upload-from-dropbox/' ) ) {
        return rename( $tmp_file, $new_file );
    }

    return $result;
}

/**
 * Add Dropbox App Key config field to form
 *
 * This functin adds "Dropbox App Key" field to form wp-admin / Settings (WPJB)
 * / Common Settings form.
 *
 * @since 1.0
 * @return void
 */
function upload_from_dropbox_config( $form ) {

    $e = $form->create("upload_from_dropbox_app_key");
    $e->setValue(wpjb_conf("upload_from_dropbox_app_key"));
    $e->setLabel(__("Dropbox App Key", "upload-from-dropbox"));
    $form->addElement($e);

    return $form;
}
