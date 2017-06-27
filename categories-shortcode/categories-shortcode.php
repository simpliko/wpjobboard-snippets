<?php
/*
Plugin Name: WPJobBoard Snippets - Categories Shortcode
Plugin URI: http://wpjobboard.net/
Version: 1.0
Author: Simpliko, Mark Winiarski
Author URI: https://simpliko.pl
Description: Modification adds [wpjb_categories_list count="true" hide_empty="true"] shortcode with list of all categories.
Text Domain: wpjb-snippet-cs
Domain Path: /languages
*/

// Register shortcode
add_shortcode( 'wpjb_categories_list', 'wpjb_snippet_categories');

/**
 * Function generates list of all jobs
 * 
 * @param array $atts
 * @return string
 */
function wpjb_snippet_categories($atts) {
    
    $atts = shortcode_atts( array(
            'count'         => true,
            'hide_empty'    => true
    ), $atts, 'wpjb_categories_list' );
    
    $categories = wpjb_get_categories();
    
    ob_start() 
    ?>
    <ul class="<?php if($atts['count']): ?>wpjb-widget-with-count<?php endif; ?>">
        <?php if(!empty($categories)): foreach($categories as $category): ?>
        <?php if($atts['hide_empty'] && !$category->getCount()) continue; ?>
        <li>
            <a href="<?php echo wpjb_link_to("category", $category) ?>">
                <?php esc_html_e($category->title) ?>
            </a>
            <?php if($atts['count']): ?>
            <div class="wpjb-widget-item-count">
                <div class="wpjb-widget-item-num"><?php echo intval($category->getCount()) ?></div>
            </div>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php else: ?>
        <li><?php _e("No categories found.", "wpjobboard") ?></li>
        <?php endif; ?>
    </ul>
    <?php
    $content = ob_get_clean();
    
    return $content;
}