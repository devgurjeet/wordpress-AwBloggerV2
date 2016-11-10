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
	        mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab");

	        // $message[] 	= 	"Table: [ " . $line[0] . " ] Done.";
	        $message  = "Table: [ " . $line[0] . " ] Done.";
			AwbLog::writeLog($message);
	    }

	    $message  = "Success: Database Copied Successfully.";
		AwbLog::writeLog($message);
	    return true;
	}
}/* class ends here */

?>