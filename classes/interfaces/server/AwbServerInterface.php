<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbServerInterface {

	public static $domain_name_url;
	public static $dom_alias;
	public static $config_name;
	public static $site_config;

	public static function setupDomain(){

			$blog_name 	   	    = $blogNames;
			$blog_path 	   	    = AwbWpInterface::$destination;
			$domain_url   	    = $_POST['domain_name_url'];
			$dom_alias  	    = $_POST['dom_alias'];
			$config_name  	    = $_POST['config_name'];
			$config_file_path 	= $_POST['site_config'];

			$domain_urls 		= preg_replace('#^https?://#', '', $domain_url);
			$domain_urls 		= str_replace("/", "", $domain_urls);


			$output 		    = "aw_create_config ".$domain_urls." ".$dom_alias." ".$blog_path." ".$config_name;
			$output 			= shell_exec($output);

			unlink($blog_path."/.htaccess");

			$message = "Success: Updated Site Domain ($domain_urls)";
			AwbLog::writeLog( $message );

			return true;
	}
}/* class ends here */

?>