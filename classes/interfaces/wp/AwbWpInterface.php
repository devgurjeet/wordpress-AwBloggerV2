<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbWpInterface {

	/**
	 * [isBlogExists : check if the destination blog already exists.]
	 * @param  String  $destination
	 * @return boolean
	 */
	public static function isBlogExists( $destination ){
		$root_path = "/var/www/html/".$destination;
		if (file_exists( $wordpress_folder ) &&  is_dir( $wordpress_folder ) ){
    		return true;
    	} else {
    		return false;
    	}
	}
}/* class ends here */

?>