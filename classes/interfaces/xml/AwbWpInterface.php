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
		$root_path = "/var/www/html/".$destination;
		if (file_exists( $root_path ) &&  is_dir( $root_path ) ){

			$message = "ERROR: Destination directory already Exists: ($root_path)";
			AwbLog::writeLog($message);
    		return true;
    	} else {
    		self::$destination = $root_path;

    		$message = "Success: Destination directory verified: ($root_path)";
			AwbLog::writeLog($message);
    		return false;
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
		$message  = "Copy Blog initiated.";
		AwbLog::writeLog($message);

		$source      = self::$source;
		$dest        = self::$destination;
		$permissions = self::$permissions;

	    // Check for symlinks
	    if ( is_link($source) ) {
	        return symlink(readlink($source), $dest);
	    }

	    // Simple copy for a file
	    if (is_file($source)) {
	        return copy($source, $dest);
	    }

	    // Make destination directory
	    if (!is_dir($dest)) {
	        mkdir($dest, $permissions);
	    }

	    // Loop through the folder
	    $dir 		=	dir($source);
	    while (false !== $entry = $dir->read()) {
	        // Skip pointers
	        if ($entry == '.' || $entry == '..') {
	            continue;
	        }

	        // Deep copy directories
	        self::copyBlog("$source/$entry", "$dest/$entry", $permissions);
	    }

	    // Clean up
	    $dir->close();
	    $message  = "Success: Blog Copied successfully.";
		AwbLog::writeLog($message);
	    return true;
	}

}/* class ends here */

?>