<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbLog {
	public static $log_filename;
	public static $logRootDir = plugin_dir_path( __FILE__ )."/logs/";

	public static function writeLog($filename = null, $message = null){
		if( $filename !== null AND $message !== null ){
			file_put_contents($filename, print_r( $message, true),FILE_APPEND );
			file_put_contents($filename, print_r("\n" , true),FILE_APPEND );
		}
	}
}/* class ends here */

?>