<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbDbInterface {

	public static $sourceWpdb;
	public static $destinationWpdb;
	/**
	 * [getBloglist: create and return list of all blogs]
	 * @return String: list of all blogs in HTML Format.
	 */
	public static function getBloglist(){
		global $wpdb;

		$sql 	  = "SELECT * FROM wp_aw_blog_sites";
		$results  = $wpdb->get_results( $sql, OBJECT );

		$html  = '';
		$html .= "<select name='siteTemplate' id='directories'>";
		foreach ( $results as $directory ) {
			$html .= "<option value='".$directory->site_name."'>".$directory->site_name." ( ".$directory->site_url." )</option>";
		}
		$html .= "</select>";

		$html .= "<script>jQuery('#directories').select2();</script>";

		return $html;
	}

	/**
	 * [getDatabaseName: return the database name from Blogname]
	 * @param  String $blogAddress
	 * @return String
	 */
	public static function getDatabaseName( $blogAddress ) {
		return 	str_replace('-', '_', $blogAddress);
	}

	/**
	 * [setSourceDb Update the sourceDb class variable]
	 */
	public static function setSourceDb(){
		global $wpdb;
		self::$sourceWpdb = $wpdb;

		$message  = "Success: Connected to Source Database";
		AwbLog::writeLog($message);
	}

	/**
	 * [setDestinationDb: Update the destinationDb class variable. ]
	 * @param String $destination
	 */
	public static function setDestinationDb( $destination  ){
		$host 		   = DB_HOST;
		$username 	   = DB_USER;
		$password 	   = DB_PASSWORD;
		$databaseName  = self::getDatabaseName( $destination );
		self::$destinationWpdb  = new wpdb( $username, $password, $databaseName, $host );

		$message  = "Success: Connected to Destination Database";
		AwbLog::writeLog($message);
	}

	/**
	 * [createDestinationDatabse]
	 * @param  Sting $destination
	 * @return boolean
	 */
	public static function createDestinationDatabse( $destination ){

		$servername 	=	DB_HOST;
		$username 		= 	DB_USER;
		$password 		= 	DB_PASSWORD;
		$mysql_database =  	self::getDatabaseName( $destination );

		$conn 			= 	new mysqli($servername, $username, $password);

		//** Code to process UTF-8 characters. **//
		mysqli_query($conn, "SET SESSION CHARACTER_SET_CLIENT =utf8;");

		if ($conn->connect_error) {

			$message  = "Error: Error in connecting database.";
			AwbLog::writeLog($message);
			return false;
		}else{

			$db_command = 	"CREATE DATABASE $mysql_database CHARACTER SET utf8 COLLATE utf8_general_ci;";

			if ($conn->query($db_command) === TRUE){

				$message  = "Success: Database [$mysql_database] created Successfully.";
				AwbLog::writeLog($message);
				return true;
			}else{
				$message  = "Error: Database [$mysql_database] not created.";
				AwbLog::writeLog($message);
				return false;
			}
			return true;
		}
	}


	//** Function to clone database.  **//
	public static function copyDatabase( $sourceDB, $destinationDB ){

	    $message 		= 	array();
		$servername 	=	DB_HOST;
		$username 		= 	DB_USER;
		$password 		= 	DB_PASSWORD;

	    $connect2  		= 	mysqli_connect( $servername, $username , $password , $destinationDB );

	    if (mysqli_connect_errno()){
	    	$message  = "Error: Destination Database Not Found - $destinationDB";
			AwbLog::writeLog($message);
	        return false;
	    }

	    set_time_limit(0);

	    $connect 		= 	mysqli_connect( $servername, $username , $password , $sourceDB );

	    if (mysqli_connect_errno()){
	    	echo "Source Database Not Found:  $sourceDB";
	        return false;
	    }

	    $tables 		=	mysqli_query( $connect,"SHOW TABLES FROM $sourceDB");

	    $message  = "Success: Database Copy Initiated";
		AwbLog::writeLog($message);

	    while ($line = mysqli_fetch_row($tables)) {
	        $tab 	= 	$line[0];
	        mysqli_query($connect, "DROP TABLE IF EXISTS $destinationDB.$tab");
	        mysqli_query($connect, "CREATE TABLE $destinationDB.$tab LIKE $sourceDB.$tab") or die(mysql_error());

	        if( $tab == 'wp_posts' ){
	        	mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab WHERE $sourceDB.$tab.post_type != 'attachment' AND $sourceDB.$tab.post_type != 'post' AND $sourceDB.$tab.post_type != 'revision'");
	        }elseif ( $tab == 'wp_postmeta') {
				mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab WHERE $sourceDB.$tab.meta_key != 'sm:meta-title' AND $sourceDB.$tab.meta_key != 'sm:meta-description' AND $sourceDB.$tab.meta_key != 'sm:meta-image' AND $sourceDB.$tab.meta_key != 'custompost' AND $sourceDB.$tab.meta_key != 'enclosure1' AND $sourceDB.$tab.meta_key != 'metablogcategory' AND $sourceDB.$tab.meta_key != 'syndication_permalink' AND $sourceDB.$tab.meta_key != '_thumbnail_id' AND $sourceDB.$tab.meta_key != 'sm:block' AND $sourceDB.$tab.meta_key != 'enclosure' AND $sourceDB.$tab.meta_key != '_edit_last' AND $sourceDB.$tab.meta_key != '_edit_lock' AND $sourceDB.$tab.meta_key != '_encloseme' $sourceDB.$tab.meta_key != '_pingme' AND $sourceDB.$tab.meta_key != '_wp_attached_file' AND $sourceDB.$tab.meta_key != '_wp_attachment_backup_sizes' AND $sourceDB.$tab.meta_key != '_wp_attachment_context'");
	        }else{
				mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab");
	        }

	        // $message[] 	= 	"Table: [ " . $line[0] . " ] Done.";
	        $message  = "Table: [ " . $tab . " ] Done.";
			AwbLog::writeLog($message);
	    }

	    $message  = "Success: Database Copied Successfully.";
		AwbLog::writeLog($message);
	    return true;
	}

	public static function updateWpOptions(){

		$wpdb = AwbDbInterface::$destinationWpdb;

		$address 		= AwbXmlInterface::$address;
		$title 			= AwbXmlInterface::$title;
		$email 			= AwbXmlInterface::$email;
		$theme 			= AwbXmlInterface::$theme;
		$template 		= AwbXmlInterface::$template;
		$description 	= AwbXmlInterface::$description;
		$topic 			= AwbXmlInterface::$topic;
		$language 		= AwbXmlInterface::$language;



		$protocol 	= 	isset($_SERVER["https"]) ? 'https' : 'http';

		$blog_path 	= 	$protocol . "://" . $_SERVER['SERVER_NAME'] . DIRECTORY_SEPARATOR . $address . DIRECTORY_SEPARATOR;


		/*update site URL */
		$wpdb->update(
			'wp_options',
			array(
				'option_value' => $blog_path,
			),
			array( 'option_name' => 'siteurl' ),
			array(
				'%s',
			),
			array( '%s' )
		);

		/*update home */
		$wpdb->update(
			'wp_options',
			array(
				'option_value' => $blog_path,
			),
			array( 'option_name' => 'home' ),
			array(
				'%s',
			),
			array( '%s' )
		);

		/*update home */
		$wpdb->update(
			'wp_options',
			array(
				'option_value' => trim($email),
			),
			array( 'option_name' => 'admin_email' ),
			array(
				'%s',
			),
			array( '%s' )
		);

		/*update title */
		$wpdb->update(
			'wp_options',
			array(
				'option_value' => trim($title),
			),
			array( 'option_name' => 'blogname' ),
			array(
				'%s',
			),
			array( '%s' )
		);

		/*update description */
		$wpdb->update(
			'wp_options',
			array(
				'option_value' => trim($description),
			),
			array( 'option_name' => 'blogdescription' ),
			array(
				'%s',
			),
			array( '%s' )
		);

		/*update WPLANG */
		$wpdb->update(
			'wp_options',
			array(
				'option_value' => trim($language),
			),
			array( 'option_name' => 'WPLANG' ),
			array(
				'%s',
			),
			array( '%s' )
		);

		/*update topic */
		$wpdb->update(
			'wp_options',
			array(
				'option_value' => trim($topic),
			),
			array( 'option_name' => 'topic' ),
			array(
				'%s',
			),
			array( '%s' )
		);


		/*update user Email  */
		$wpdb->update(
			'wp_users',
			array(
				'user_email' => trim($email),
			),
			array( 'ID' => 1 ),
			array(
				'%d',
			),
			array( '%s' )
		);

		$message  = "Success: Options updated Successfully.";
		AwbLog::writeLog($message);

		return true;
	}


	function updateAwBloggerList( $args ){
		global $wpdb;

		$address 		= AwbXmlInterface::$address;
		$title 			= AwbXmlInterface::$title;
		$email 			= AwbXmlInterface::$email;
		$theme 			= AwbXmlInterface::$theme;
		$template 		= AwbXmlInterface::$template;
		$description 	= AwbXmlInterface::$description;
		$topic 			= AwbXmlInterface::$topic;
		$language 		= AwbXmlInterface::$language;

		//** list of input arguments **//
		$site_name			=	AwbXmlInterface::$title;
		$site_slug 			=	AwbXmlInterface::$address;
		$site_theme 		=	AwbXmlInterface::$theme;
		$site_url 			=	'http://iris.scanmine.com/'.AwbXmlInterface::$address;
		$site_language  	=  	AwbXmlInterface::$language;


		$sql  =	"INSERT INTO wp_aw_blog_sites ( `site_name`,`site_slug`,`site_theme`,`site_url`, `site_language`) values( '$site_name', '$site_slug', '$site_theme', '$site_url', '$site_language')";
		$wpdb->query( $sql );

		$message  = "Success: Updated `Aw Blogger List` Successfully.";
		AwbLog::writeLog($message);
		return true;
	}

}/* class ends here */

?>