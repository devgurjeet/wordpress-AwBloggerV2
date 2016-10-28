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

	function getDatabaseName( $blogAddress ) {
		return 	str_replace('-', '_', $blogAddress);
	}

	public static function setSourceDb(){
		global $wpdb;
		self::$sourceWpdb = $wpdb;
	}

	public static function setDestinationDb( $destination  ){
		$host 		   = DB_HOST;
		$username 	   = DB_USER;
		$password 	   = DB_PASSWORD;
		$databaseName  = self::getDatabaseName( $destination );

		self::$destinationWpdb  = new wpdb( $username, $password, $databaseName, $host );
	}


	function createDestinationDatabse( $destination ){

		$servername 	=	DB_HOST;
		$username 		= 	DB_USER;
		$password 		= 	DB_PASSWORD;
		$mysql_database =  	self::getDatabaseName( $destination );

		$conn 			= 	new mysqli($servername, $username, $password);

		//** Code to process UTF-8 characters. **//
		mysqli_query($conn, "SET SESSION CHARACTER_SET_CLIENT =utf8;");

		if ($conn->connect_error) {
			return false;
		}else{

			$db_command = 	"CREATE DATABASE $mysql_database CHARACTER SET utf8 COLLATE utf8_general_ci;";

			if ($conn->query($db_command) === TRUE){
				return true;
			}else{
				return false;
			}
			return true;
		}
	}

}/* class ends here */

?>