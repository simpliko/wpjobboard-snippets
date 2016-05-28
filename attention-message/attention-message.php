<?php
/**
Plugin Name: WPJobBoard Snippets - Attention Message
Version: 1.0
Author: Greg Winiarski
Description: Shows a notice when user is viewing old job details page.
*/

add_action("wp", "push_attention_msg");

/**
 * Insert attention message
 *
 * This function will show "Attention! This job is ..." message if current user
 * is on job details page and currently viewed job is older than X days.
 *
 * @since 1.0
 * @return void
 */
function push_attention_msg() {
  $job = null;
  if(is_singular("job")) {
    $query = new Daq_Db_Query();
    $query->from("Wpjb_Model_Job t");
    $query->where("post_id = ?", get_the_ID());
    $query->limit(1);
    $result = $query->execute();

    if(isset($result[0])) {
      $job = $result[0];
    }
  }

  if(is_wpjb() && wpjb_is_routed_to("index.single")) {
    $job = Wpjb_Project::getInstance()->placeHolder->job;
  }

  if($job === null) {
    return;
  }

  $old = wpjb_conf("front_mark_as_old");

  if($old>0 && time()-strtotime($job->job_created_at)>$old*3600*24) {
      $diff = floor((time()-strtotime($job->job_created_at))/(3600*24));
      $msg = _n(
          "Attention! This job posting is one day old and might be already filled.",
          "Attention! This job posting is %d days old and might be already filled.",
          $diff,
          "wpjobboard"
      );

      $flash = new Wpjb_Utility_Session();
      $flash->addInfo(sprintf($msg, $diff));
      $flash->save();
  }
}
