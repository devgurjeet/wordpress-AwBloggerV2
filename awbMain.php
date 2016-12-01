<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwBlogger {

    //** Constructor **//
    function __construct() {
        //** Register menu. **//
        add_action('admin_menu', array(&$this, 'register_plugin_menu') );
    }

    function loadAssectCss(){
         $plugin_url = plugin_dir_url( __FILE__ );

        //** Load  Styling. **//
        // wp_enqueue_style( 'AwSocialTabs_style', $plugin_url . 'css/awst_style.css' );
        // wp_enqueue_style('AwSocialTabs-font-awesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css');

        /*load frontend script. */
        // wp_enqueue_script( 'awst_custom_script', plugin_dir_url( __FILE__ ) . '/js/awst_custom_script.js', array('jquery'), '1.0.0' );

    }

    function loadAdminAssects( $hook ){
        //** Load  Styling. **//
        // $plugin_url = plugin_dir_url( __FILE__ );
        // wp_enqueue_style( 'awsocialtabs_style', $plugin_url . 'css/awst_admin_style.css' );
        // wp_enqueue_style('awsocialtabs_style_font_awesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css');
        /*load admin script. */
        // wp_enqueue_script( 'awst_admin_custom_script', plugin_dir_url( __FILE__ ) . '/js/awst_admin_custom_script.js', array('jquery'), '1.0.0' );
    }

    //** Register menu Item. **//
    function register_plugin_menu(){
            add_menu_page( 'Aw Blogger V2', 'Aw Blogger V2', 'manage_options', 'awbloggerv2', array('AwbAdminPages', 'create_blog'), 'dashicons-rss', 8 );
            add_submenu_page('awbloggerv2', 'Aw Blogger V2 | Blog Creator', 'Aw Blogger Domain', 'manage_options','awbloggerv2Domain', array('AwbAdminPages', 'createBlogDomain'));
    }

}/*class ends here*/
?>