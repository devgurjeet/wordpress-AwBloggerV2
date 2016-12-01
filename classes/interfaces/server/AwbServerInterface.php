<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbServerInterface {

	public static $domain_name_url;
	public static $dom_alias;
	public static $config_name;
	public static $site_config;

	public static function setupDomain(){

			$blog_name 	   	    = $blogNames;
			$blog_path 	   	    = "/var/www/html/".$blog_name;
			$domain_url   	    = $_POST['domain_name_url'];
			$dom_alias  	    = $_POST['dom_alias'];
			$config_name  	    = $_POST['config_name'];
			$config_file_path 	= $_POST['site_config'];

			$domain_urls 		= str_replace("/", "", $domain_url);
			$domain_urls 		= str_replace("http:", "", $domain_urls);
			$domain_urls 		= str_replace("https:", "", $domain_urls);

			$output 		    = "aw_create_config ".$domain_urls." ".$dom_alias." ".$blog_path." ".$config_name;
			$output 			= shell_exec($output);

			unlink($blog_path."/.htaccess");

			return true;
	}
}/* class ends here */

?>

