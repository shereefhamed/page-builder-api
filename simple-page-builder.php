<?php
/**
 * Plugin Name: Simple Page Builder
 * Description: Create Bulk pages via a secure REST API endpoint accessible from external applications, with advanced authentication and webhook notifications.
 * Author:Shereef Hamed
 * Author URI: https://shereefhamed.github.io/portfolio/
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define('SIMPLE_PAGE_BUILDER_PATH', plugin_dir_path(__FILE__));
define('SIMPLE_PAGE_BUILDER_URI', plugin_dir_url(__FILE__));
define('SIMPLE_PAGE_BUILDER_VERSION', '1.0.0');
//define('CREATED_PAGES_TABLE', 'page_builder_created_pages');

require_once(SIMPLE_PAGE_BUILDER_PATH. 'includes/simple-page-builder.class.php');
if(class_exists('Simple_Page_Builder')){
    register_activation_hook( __FILE__,array('Simple_Page_Builder','activate'));
    register_deactivation_hook( __FILE__,array('Simple_Page_Builder','deactivate'));
    register_uninstall_hook( __FILE__,array('Simple_Page_Builder','uninstall'));
 
    $simple_page_builder = new Simple_Page_Builder();
}

