<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbWpInterface {
	public static $source;
	public static $destination;
	private static $permissions = 0775;

	/**
	 * [isBlogExists : check if the destination blog already exists.]
	 * @param  String  $destination
	 * @return boolean
	 */
	public static function isBlogExists( $destination ){

		if (file_exists( $root_path ) &&  is_dir( $root_path ) ){

			$message = "ERROR: Destination directory already Exists: ($root_path)";
			AwbLog::writeLog($message);
    		return true;
    	} else {
    		return false;
    	}
	}

	public static function createDestinationDirectory( $destination ) {
		$root_path = "/var/www/html/".$destination;
		if (false === @mkdir( $root_path, 0775, true )) {
			$message = "ERROR: Unable to create Destination directory: ($root_path)";
			AwbLog::writeLog( $message );
			return false;
		}else{
			self::$destination = $root_path;
			$message = "Success: Destination directory Created Successfully: ($root_path)";
			AwbLog::writeLog( $message );
			return true;
		}
	}

	/**
	 * [setSource: set the source class variable]
	 * @param String $blogname
	 */
	public static function setSource( $blogname ) {
		$root_path = "/var/www/html/".$blogname;
		self::$source = $root_path;

	}

	//**Function to copy files. **//
	public static function copyBlog(){
		$source      = self::$source;
		$dest        = self::$destination;
		$permissions = self::$permissions;

		$sourceFiles = $source."/*";
		$destFiles   = $dest."/";

		// $command = "cp -r $sourceFiles $destFiles";
		$command = "rsync -a  $sourceFiles $destFiles --exclude='*.zip' --exclude='*.sql*' --exclude='wp-content/uploads/*' --exclude='*.tar*' --exclude='*.php-*' --exclude='*.css-*' --exclude='*.js-*' --exclude='*.log*' --exclude='wp-content/plugins/aw-newsfeed/includes/data/*'";
		shell_exec($command);


		$commandPer 	=	"chown -R artworld:testing $destFiles";
		shell_exec($commandPer);

		$commandFiles 	=	"find $destFiles -type f exec chmod 664 {} \;";
		shell_exec($commandFiles);

		$commandFolders	=	"find $destFiles -type d exec chmod 775 {} \;";
		shell_exec($commandFolders);

		$message  = "Success: Blog Copied successfully.";
		AwbLog::writeLog($message);
		return true;
	}

	public static function getSiteUrl( ) {
		if( AwbFormProcessor::$mode == 'advanced'){
			return 'http://www.pokerisverige.com/';
		}else{
			return 'http://iris.scanmine.com/'.AwbXmlInterface::$address.'/';
		}
	}


	public static function updateWPconfig( $destinationDB ) {

		$destination =  AwbWpInterface::$destination;
		$configFile  = $destination."/wp-config.php";
		//** Update the Database Name/ **//

		$file_contents 		= 	file($configFile);
		$new_content 		=	array();

		foreach ( $file_contents as $line){

			$pos 			= 	strpos($line, "define('DB_NAME', '");

			if ($pos === false) {
			} else {
				$line 		= 	"define('DB_NAME', '".$destinationDB."');\n";
			}

			$new_content[] 	= 	$line;

		}

		$str_contents 		= 	implode("", $new_content);
		$fp 				= 	fopen($configFile, "w");
		fwrite($fp, $str_contents);
		fclose($fp);

		$message  = "Success: `wp-config.php` updated Successfully.";
		AwbLog::writeLog($message);
		return true;
	}


	//** Function to Add .Htaccess **//
	public static function addHTACCESS(){

		$filenameSource 		= '/var/www/html/templates/htaccess/.htaccess';

		$filenameDestination 	= self::$destination.'/.htaccess';

		$blogName  				=	str_replace("/var/www/html/", "", self::$destination);

		copy($filenameSource, $filenameDestination);

		chmod($filenameDestination , 0664);

		$content 				= file_get_contents( $filenameDestination );

		$newContent 			= str_replace("BLOGNAME", $blogName, $content );

		file_put_contents($filenameDestination, $newContent);

		$message  = "Success: `.htaccess` Added Successfully.";
		AwbLog::writeLog($message);
		return true;

	}


}/* class ends here */

?>

