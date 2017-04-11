<?php
/**
Plugin Name: WPJobBoard Snippets - Disable Double Applications
Version: 1.0
Author: Greg Winiarski
Description: Limits maximum number of job applications per user id or email to a given job.
*/

add_filter("wpjb_user_can_apply", "disable_double_applications", 10, 3);

/**
 * Disables users ability to apply for a job twice.
 * 
 * The function checks if user with the same email or user ID already applied
 * for this job.
 * 
 * @param boolean $cond         Can user apply for a job?
 * @param Wpjb_Model_Job $job   Job to which user is trying to apply to.
 * @param type $ctrl            Controller
 * @return boolean
 */
function disable_double_applications($cond, $job, $ctrl) {

    $id = get_current_user_id();

    if( $id ) {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Application t");
        $query->where("user_id = ?", $id);
        $query->where("job_id = ?", $job->id);
        $query->limit(1);

        $result = $query->execute();

        if( !empty($result) ) {
            $cond = false;
            if( get_query_var("applied") != $job->id ) {
                $ctrl->view->_flash->addError("You already applied for this job.");
            }
        }
        
        return $cond;
    }
    
    if( Daq_Requset::getInstance()->post( "email" ) ) {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Application t");
        $query->where("email = ?", Daq_Requset::getInstance()->post( "email" ) );
        $query->where("job_id = ?", $job->id);
        $query->limit(1);

        $result = $query->execute();

        if( !empty($result) ) {
            $cond = false;
            if( get_query_var("applied") != $job->id ) {
                $ctrl->view->_flash->addError("You (or someone using your email address) already applied for this job.");
            }
        }
        
        return $cond;
    }
    
    return $cond;
}