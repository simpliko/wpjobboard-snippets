<?php
/**
Plugin Name: WPJobBoard Snippets - Customize Single Job
Version: 1.0
Author: Greg Winiarski
Description: Customizes the single job details page in various ways.
*/

/**
 * Shows additional info in company info box
 * 
 * Displays additional information on job details page inside the gray company box
 * for example https://demo.wpjobboard.net/job/web-developer/
 * 
 * This code will be executed only if:
 * - the job was posted by registered employer (if(!$company) { ... })
 * - you created a custom field named is_remote in wp-admin / Settings (WPJB) / Custom Fields / Company panel
 * 
 * This function is applied using wpjb_template_job_company_meta action
 * 
 * @see wpjb_template_job_company_meta action
 * 
 * @param Wpjb_Model_Job $job
 * @return void
 */
function csj_show_company_is_remote($job) {
    
    $company = $job->getCompany(true);
    
    if(!$company) {
        return;
    }
    
    ?>
    <li>
        <?php if(isset($company->meta->is_remote) && $company->meta->is_remote->value()): ?>
        <span class="wpjb-glyphs wpjb-icon-paper-plane"></span> 
        <?php _e("Remote Company", "wpjobboard") ?>
        <?php else: ?>
        <span class="wpjb-glyphs wpjb-icon-paper-plane-empty"></span> 
        <?php _e("NON Remote Company", "wpjobboard") ?>
        <?php endif; ?>
    </li>
    <?php
}
add_action( "wpjb_template_job_company_meta", "csj_show_company_is_remote" );

/**
 * Shows Job ID on job details page
 * 
 * Displays a job ID in the job information table on job details page
 * for example https://demo.wpjobboard.net/job/web-developer/
 * 
 * This function is applied using wpjb_template_job_meta_text action
 * 
 * @see wpjb_template_job_meta_text action
 * 
 * @param Wpjb_Model_Job $job
 */
function csj_show_job_id( $job ) {
    ?>
    <div class="wpjb-grid-row">
        <div class="wpjb-grid-col wpjb-col-30"><?php _e("ID", "wpjobboard"); ?></div>
        <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs wpjb-icon-key">
            <?php echo esc_html($job->id) ?>
        </div>
    </div>
    <?php
}
add_action( "wpjb_template_job_meta_text", "csj_show_job_id" );

/**
 * Shows Job expiration date on job details page
 * 
 * Displays a job expiration date in the job information table on job details page
 * for example https://demo.wpjobboard.net/job/web-developer/
 * 
 * This function is applied using wpjb_template_job_meta_text action
 * 
 * @see wpjb_template_job_meta_text action
 * 
 * @param Wpjb_Model_Job $job
 */
function csj_show_job_expires_at( $job ) {
    ?>
    <div class="wpjb-grid-row">
        <div class="wpjb-grid-col wpjb-col-30"><?php _e("Expires At", "wpjobboard"); ?></div>
        <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs wpjb-icon-eye-off">
            <?php echo esc_html(wpjb_date_display(get_option('date_format'), $job->job_expires_at)) ?>
        </div>
    </div>
    <?php
}
add_action( "wpjb_template_job_meta_text", "csj_show_job_expires_at" );

/**
 * Shows company email on job details page
 * 
 * Displays a company email in the job information table on job details page
 * for example https://demo.wpjobboard.net/job/web-developer/
 * 
 * This function is applied using wpjb_template_job_meta_text action
 * 
 * @see wpjb_template_job_meta_text action
 * 
 * @param Wpjb_Model_Job $job
 */
function csj_show_company_email( $job ) {
    ?>
    <div class="wpjb-grid-row">
        <div class="wpjb-grid-col wpjb-col-30"><?php _e("Company Email", "wpjobboard"); ?></div>
        <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs wpjb-icon-mail-alt">
            <?php echo esc_html($job->company_email) ?>
        </div>
    </div>
    <?php
}
add_action( "wpjb_template_job_meta_text", "csj_show_company_email" );

/**
 * Shows login of a person who posted the job
 * 
 * Displays login of a person who posted the job between Apply Online button and
 * job description on job details page
 * for example https://demo.wpjobboard.net/job/web-developer/
 * 
 * This function is applied using wpjb_template_job_meta_text action
 * 
 * @see wpjb_template_job_meta_richtext action
 * 
 * @param Wpjb_Model_Job $job
 */
function csj_show_who_posted_this_job( $job ) {
    $company = $job->getCompany(true);
    $user = $company->getUser(true);
    ?>
    <div class="wpjb-text wpjb-top-header">
    <?php printf("This job was posted by <strong>%s</strong>", $user->user_login); ?>
    </div>
    <?php
}
add_action( "wpjb_template_job_meta_richtext", "csj_show_who_posted_this_job" );

/**
 * Shows custom button next to "Apply Online" button
 * 
 * Displays a button with title "Custom Button" next to "Apply Online" button
 * on job details page for example https://demo.wpjobboard.net/job/web-developer/
 * 
 * This function is applied using wpjb_tpl_single_actions action
 * 
 * @see wpjb_tpl_single_actions action
 * 
 * @param Wpjb_Model_Job $job
 */
function csj_show_custom_button( $job ) {
    ?>
    <a class="wpjb-button" href="#"><?php _e("Custom Button", "wpjobboard") ?></a>
    <?php
}
add_action( "wpjb_tpl_single_actions", "csj_show_custom_button" );

/**
 * Set logo size on job details page
 * 
 * This function is being called by wpjb_singular_logo_size filter
 * 
 * @see wpjb_singular_logo_size
 * 
 * @param string    $size   Logo size in format widthXheight (for example 128x128)
 * @param string    $type   One of "job", "resume", "company"
 * @return string           New logo size
 */
function csj_singular_logo_size( $size, $type ) {
    if($type != "job") {
        return $size;
    }
    
    add_action( "wp_footer", "csj_singular_logo_size_footer" );
    return "128x128";
}

/**
 * Prints additional CSS in the footer
 * 
 * This filter is applied by csj_singular_logo_size() and executed in wp_footer action
 * 
 * @see csj_singular_logo_size()
 * 
 * @return void
 */
function csj_singular_logo_size_footer() {
    ?>
    <style type="text/css">
    .single-job .wpjb .wpjb-top-header-image {
        width: 128px;
        height: 128px;
    }
    .single-job .wpjb .wpjb-top-header-image > img {
        max-height: 128px;
        max-width: 128px;
    }
    .single-job .wpjb .wpjb-top-header-content {
        width: calc( 100% - 128px );
    }
    .single-job .wpjb .wpjb-logo-default-size:before {
        /* this has to be a bit less than 128px you will get a perfect size by trial and error */
        font-size: 116px; 
    }
    </style>
    <?php
}
add_filter( "wpjb_singular_logo_size", "csj_singular_logo_size", 10, 2 );