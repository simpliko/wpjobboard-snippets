<?php
/**
Plugin Name: WPJobBoard Snippets - Feeds API
Version: 1.0
Author: Greg Winiarski
Description: Explains how to extend feeds using wpjb_xml_api_* actions. This add-on requires WPJB 4.4.4 or newer.
*/

/**
 * Applying action to one of job aggregation feeds, possible values are:
 * 
 * - wpjb_xml_api_indeed        Indeed
 * - wpjb_xml_api_trovit        Trovit
 * - wpjb_xml_api_simplyhired   SimplyHired
 * - wpjb_xml_api_juju          Juju
 * 
 */
add_action("wpjb_xml_api_indeed", "feeds_api_aggregators");

/**
 * Adds additional data inside Indeed <job> tag.
 * 
 * This function is being called by wpjb_xml_api_* action, you can find it in
 * wpjobboard/application/libraries/Module/Api/Xml.php
 * 
 * @param Wpjb_Model_Job $job   Job currently rendered to XML
 */
function feeds_api_aggregators(Wpjb_Model_Job $job) {
    // Daq_Helper_Xml helps to generate valid XML tags, this class definition you
    // can find in wpjobboard/framework/Helper/Xml.php
    $xml = new Daq_Helper_Xml();
    
    // Using default fields
    $xml->tag("expires_at", $job->job_expires_at);
    // Using meta fields
    $xml->tag("job_description_format", $job->meta->job_description_format->value());
    
}

/**
 * Applying filter to RSS feeds
 */
add_filter("wpjb_xml_api_rss", "feeds_api_rss", 10, 3);

/**
 * This function appends expires_at and job_description_format tags into RSS
 * <item> tag. 
 * 
 * Function is applied once for each <item> in the feed and the additional data
 * is appended to the end of "item".
 * 
 * @param DomElement $item      http://php.net/manual/en/class.domelement.php
 * @param DomDocument $rss      http://php.net/manual/en/class.domdocument.php
 * @param Wpjb_Model_Job $job   Job currently rendered to XML
 * @return DomElement           Modified DomElement object
 */
function feeds_api_rss($item, $rss, $job) {
    // Using default fields
    $item->appendChild($rss->createElement("expires_at", esc_html($job->job_expires_at)));
    // Using meta fields
    $item->appendChild($rss->createElement("job_description_format", esc_html($job->meta->job_description_format->value())));
    // Using dropdown - array fields
    $item->appendChild($rss->createElement("category", esc_html($job->getTag()->category[0]->title)));
    // Make sure to return $item
    return $item;
}
