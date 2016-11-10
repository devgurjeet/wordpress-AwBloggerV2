<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

/*
Plugin Name: Aw Blogger V2
Plugin URI:  http://iris.scanmine.com
Description: Plugin to create new blogs using XML feeds.
Version: 1.0.0
Author: G0947
Author URI:
License:
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Include external classes */
include('classes/config/awbConfig.php');
include('classes/logger/AwbLog.php');
// include('classes/interfaces/ajax/AwbAjax.php');
include('classes/interfaces/db/AwbDbInterface.php');
include('classes/interfaces/xml/AwbConfigReader.php');
include('classes/interfaces/xml/AwbXmlInterface.php');
include('classes/interfaces/wp/AwbWpInterface.php');


include('classes/AwbAdminPages.php');

include('awbMain.php');

/*  create plugin object. */
new AwBlogger;
?>
