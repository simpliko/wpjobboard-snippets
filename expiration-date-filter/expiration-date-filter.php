<?php
/**
Plugin Name: WPJobBoard Snippets - Expiration Date Filter
Version: 1.0
Author: Greg Winiarski
Description: Customizes the expiration date when job is saved from [wpjb_jobs_add] shortcode.
*/

add_filter( "wpjb_job_expires_at", "expiration_date_filter", 10, 2 );
add_filter( "wpjb_form_init_job", "expiration_date_filter_init" );

/**
 * Sets a custom job expiration date
 * 
 * Takes a job expiration date from field "custom_expiration_date" and sets
 * job expiration date to the custom value.
 * 
 * @param string                    $job_expires_at     Expiration date set based on selected listing type
 * @param Wpjb_Form_Abstract_Job    $form               Submitted form
 * @return string
 */
function expiration_date_filter( $job_expires_at,  Wpjb_Form_Abstract_Job $form ) {
    if( $form->hasElement( "custom_expiration_date" ) ) {
        $job_expires_at = $form->getElement( "custom_expiration_date" )->getValue();
    }
    
    return $job_expires_at;
}

/**
 * Handles meta field
 * 
 * This function registers new meta field named "custom_expiration_date" and
 * adds it to the [wpjb_jobs_add] form.
 * 
 * @param Wpjb_Form_Abstract_Job  $form   Form to customize
 * @return Wpjb_Form_Abstract_Job
 */
function expiration_date_filter_init( Wpjb_Form_Abstract_Job $form ) {
    if( is_admin() ) {
        return $form;
    }
    
    wpjb_meta_register("job", "custom_expiration_date");
    
    $e = $form->create("custom_expiration_date", "text_date");
    $e->setLabel( "Expiration Date" );
    $e->setDateFormat(wpjb_date_format());
    $e->setRequired( true );
    $form->addElement($e, "job");
    
    return $form;
}