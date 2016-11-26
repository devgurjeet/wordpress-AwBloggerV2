<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbLog {
	public static $log_filename;
	public static $logRootDir = DEFAULT_PLUGIN_ROOT_PATH;

	public static function getPluginDir(){
		return $plugin_url = plugin_dir_path( __FILE__ );
	}

	public static function SetLogFilename( $filename ){
		self::$log_filename = $filename;
	}

	public static function writeLog($message = null){
		$filename = self::$logRootDir.self::$log_filename;

		if( $filename !== null AND $message !== null ){
			$timestamp     = date('Y-m-s H:i:s');
			$upadteMessage = "[".$timestamp."] ".$message;

			file_put_contents($filename, print_r( $upadteMessage, true),FILE_APPEND );
			file_put_contents($filename, print_r("\n" , true),FILE_APPEND );
		}
	}

	public static function startLogging(){
		$message = "============================== Blog Creation Log ==============================";
		self::writeLog($message);
	}
	public static function endLogging(){
		$message = "===============================================================================";
		self::writeLog($message);
	}

	public static function addLoggingStats( $args ){

		$site_name 			=	$args['site_name'];
		$site_url 			=	$args['site_url'];
		$total_time 		=	$args['total_time'];
		$total_posts 		=	$args['total_posts'];
		$total_categories 	=	$args['total_categories'];
		$total_feeds 		=	$args['total_feeds'];

		$message = "************************************************************";
		self::writeLog($message);

		$message = "* Site Name:  $site_name";
		self::writeLog($message);

		$message = "* Site URL: $site_url";
		self::writeLog($message);

		$message = "* Total Time: $total_time";
		self::writeLog($message);

		$message = "* Total posts: $total_posts";
		self::writeLog($message);

		$message = "* Total Categories: $total_categories";
		self::writeLog($message);

		$message = "* Total Feeds: $total_feeds";
		self::writeLog($message);

		$message = "************************************************************";
		self::writeLog($message);
	}

}/* class ends here */

?>