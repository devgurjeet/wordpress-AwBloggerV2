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
	        	mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab WHERE $sourceDB.$tab.post_type != 'attachment' AND $sourceDB.$tab.post_type != 'post' AND $sourceDB.$tab.post_type != 'revision' AND $sourceDB.$tab.post_type != 'nav_menu_item' AND $sourceDB.$tab.post_status  != 'trash'");

	        }elseif ( $tab == 'wp_postmeta') {
				mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab WHERE $sourceDB.$tab.meta_key != 'sm:meta-title' AND $sourceDB.$tab.meta_key != 'sm:meta-description' AND $sourceDB.$tab.meta_key != 'sm:meta-image' AND $sourceDB.$tab.meta_key != 'custompost' AND $sourceDB.$tab.meta_key != 'enclosure1' AND $sourceDB.$tab.meta_key != 'metablogcategory' AND $sourceDB.$tab.meta_key != 'syndication_permalink' AND $sourceDB.$tab.meta_key != '_thumbnail_id' AND $sourceDB.$tab.meta_key != 'sm:block' AND $sourceDB.$tab.meta_key != 'enclosure' AND $sourceDB.$tab.meta_key != '_edit_last' AND $sourceDB.$tab.meta_key != '_edit_lock' AND $sourceDB.$tab.meta_key != '_encloseme' $sourceDB.$tab.meta_key != '_pingme' AND $sourceDB.$tab.meta_key != '_wp_attached_file' AND $sourceDB.$tab.meta_key != '_wp_attachment_backup_sizes' AND $sourceDB.$tab.meta_key != '_wp_attachment_context'");

	        }elseif ( $tab == 'wp_term_relationships') {
				mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab WHERE object_id IN ( SELECT ID FROM $destinationDB.wp_posts )");
	        }elseif ($tab == 'wp_links') {
	        	//No content saved for wp_links
	        	// mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab");
	        }else{
				mysqli_query($connect, "INSERT INTO $destinationDB.$tab SELECT * FROM $sourceDB.$tab");
	        }

	        // Delete categories.
	        $sql = "DELETE $destinationDB.`wp_terms`, $destinationDB.`wp_term_taxonomy` FROM $destinationDB.`wp_terms` INNER JOIN $destinationDB.`wp_term_taxonomy` ON $destinationDB.`wp_terms`.`term_id` = $destinationDB.`wp_term_taxonomy`.`term_id` WHERE $destinationDB.`wp_term_taxonomy`.`taxonomy` = 'category'";
	        mysqli_query( $connect, $sql );

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


	function updateAwBloggerList( ){
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
		$site_language  	=  	AwbXmlInterface::$language;

		$site_url 			=	AwbWpInterface::getSiteUrl();


		$sql  =	"INSERT INTO wp_aw_blog_sites ( `site_name`,`site_slug`,`site_theme`,`site_url`, `site_language`) values( '$site_name', '$site_slug', '$site_theme', '$site_url', '$site_language')";
		$wpdb->query( $sql );

		$message  = "Success: Updated `Aw Blogger List` Successfully.";
		AwbLog::writeLog($message);
		return true;
	}

	public static function insertCategories( ){
		$wpdb 		= AwbDbInterface::$destinationWpdb;
		$categories = AwbXmlInterface::getPages();

		$message  = "Insert: categories Initiated.";
		AwbLog::writeLog($message);

		foreach ( $categories as $category ) {
			$wpdb->insert(
				'wp_terms',
				array(
					'name' => $category,
					'slug' => sanitize_title($category),
				),
				array(
					'%s',
					'%s'
				)
			);
			$term_id = $wpdb->insert_id;


			$wpdb->insert(
				'wp_term_taxonomy',
				array(
					'term_id'			=>	$term_id,
					'taxonomy'			=>	'category',
					'description'		=>	'',
					'parent'			=>	0,
					'count'				=>	0,
				),
				array(
					'%s',
					'%s',
					'%s',
					'%d',
					'%d'
				)
			);

			$message  = "Inserted: `$category`.";
			AwbLog::writeLog($message);

		}

		$message  = "Insert: categories inserted successfully.";
		AwbLog::writeLog($message);
		return false;
	}

	public static function insertFeeds( ){
		$wpdb 	= AwbDbInterface::$destinationWpdb;
		$feeds 	= AwbXmlInterface::getFeeds();

		$message  = "Insert: Feeds Initiated.";
		AwbLog::writeLog($message);

		foreach ( $feeds as $feed ) {
			$feedData 		= AwbRssInterface::getFeedDetails($feed);

			$link_name 		= $feedData["link_name"];
			$link_url 		= $feedData["link_url"];
			$description 	= $feedData["description"];

			$wpdb->insert(
				'wp_links',
				array(
					'link_url' 			=> $link_url,
					'link_name' 		=> $link_name,
					'link_description' 	=> $description ,
					'link_rss' 			=> $feed,
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s'
				)
			);

			$message  = "Inserted: `$feed`.";
			AwbLog::writeLog($message);
		}

		$message  = "Insert: Feeds inserted successfully.";
		AwbLog::writeLog($message);
		return true;
	}

	public static function createFooterMenu(){

		$wpdb 	= AwbDbInterface::$destinationWpdb;
		$message  = "Create: Footer Menu started.";
		AwbLog::writeLog($message);

		$sql 	= "SELECT ID, post_title FROM wp_posts WHERE wp_posts.post_type = 'page' ORDER BY post_title";

		$results = $wpdb->get_results ( $sql  );

		$menuOrder 		= 0;
		$menuLocationID 	= self::getMenuID( 'secondary' );

		foreach ($results as $page ) {
			$menuID = self::createMenuPosts($menuOrder);
			self::addMenuPostMeta($menuID, 'page', 'post_type', $menuLocationID, $page->ID);
			self::updateTermRelation( $menuID,  $menuLocationID);
			$menuOrder++;

			$message  = "Create: Menu Item `$page->post_title`.";
			AwbLog::writeLog($message);
		}

		$message  = "Create: Footer Menu Completed.";
		AwbLog::writeLog($message);
		return true;
	}

	public static function createTopMenu(){

		$wpdb 	= AwbDbInterface::$destinationWpdb;
		$message  = "Create: Top Menu started.";
		AwbLog::writeLog($message);

		$sql 	= "SELECT t.term_id, t.name, t.slug
					FROM wp_terms AS t
					INNER JOIN wp_term_taxonomy AS tt ON (t.term_id = tt.term_id)
					WHERE tt.taxonomy = 'category'";

		$results = $wpdb->get_results ( $sql  );
		$menuOrder = 0;
		$menuLocationID 	= self::getMenuID( 'primary' );

		foreach ($results as $caregory ) {

			$menuID = self::createMenuPosts($menuOrder);
			self::addMenuPostMeta($menuID, 'category', 'taxonomy', $menuLocationID, $caregory->term_id);
			self::updateTermRelation( $menuID,  $menuLocationID);
			$menuOrder++;

			$message  = "Create: Menu Item `$caregory->name`.";
			AwbLog::writeLog($message);
		}

		$message  = "Create: Top Menu Completed.";
		AwbLog::writeLog($message);
		return true;

	}

	public static function updateTermRelation( $object_id, $term_taxonomy_id ){
		$wpdb 	= AwbDbInterface::$destinationWpdb;
		$wpdb->insert(
				'wp_term_relationships',
				array(
					'object_id' 			=> $object_id,
					'term_taxonomy_id' 		=> $term_taxonomy_id,

				),
				array(
					'%d',
					'%d',
				)
			);

		$message  = "Create: Added Term Relation.";
		AwbLog::writeLog($message);
	}
	public static function createMenuPosts( $menuOrder ){
		$wpdb 	= AwbDbInterface::$destinationWpdb;

		$wpdb->insert(
			'wp_posts',
			array(
				'post_author' 		=> 1,
				'post_date'			=> time(),
				'post_date_gmt'		=> time(),
				'post_content'		=> ' ',
				'post_title'		=> '',
				'post_excerpt'		=> '',
				'post_status'		=> 'publish',
				'comment_status'	=> 'closed',
				'ping_status'		=> 'closed',
				'post_password'		=> '',
				'to_ping'			=> '',
				'pinged'			=> '',
				'post_modified'		=> time(),
				'post_modified_gmt'	=> time(),
				'post_content_filtered'	=> '',
				'post_parent'		=> 0,
				'post_type'			=> 'nav_menu_item',
				'post_mime_type'	=> '',
				'comment_count' 	=> 0,
				'menu_order'		=> $menuOrder,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
			)
		);

		$lastID = $wpdb->insert_id;


		$guid = AwbWpInterface::getSiteUrl()."?".$lastID;

		$wpdb->update(
			'wp_posts',
			array(
				'post_name'		=> $lastID,
				'guid'			=> $guid,
			),
			array( 'ID' => $lastID ),
			array(
				'%d',
				'%s',
				'%s'
			),
			array( '%d' )
		);

		return  $lastID;

	}
	public static function addMenuPostMeta( $postID, $menuItemType = '', $itemType = '', $menuID, $ItemID  ){
		$wpdb 	= AwbDbInterface::$destinationWpdb;

		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'		=>	$postID,
				'meta_key'		=> '_menu_item_type',
				'meta_value' 	=> $itemType,
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);

		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'		=>	$postID,
				'meta_key'		=> '_menu_item_menu_item_parent',
				'meta_value'	=> '0',
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);
		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'		=>	$postID,
				'meta_key'		=> '_menu_item_object_id',
				'meta_value' 	=> $ItemID,
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);

		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'		=>	$postID,
				'meta_key'		=> '_menu_item_object',
				'meta_value'	=>  $menuItemType,
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);

		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'		=>	$postID,
				'meta_key'		=> '_menu_item_target',
				'meta_value' 	=> '',
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);

		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'		=>	$postID,
				'meta_key'		=> '_menu_item_classes',
				'meta_value'	=> 'a:1:{i:0;s:0:"";}',
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);

		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'		=>	$postID,
				'meta_key'		=> '_menu_item_xfn',
				'meta_value' 	=> '',
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);

		$wpdb->insert(
			'wp_postmeta',
			array(
				'post_id'	 =>	$postID,
				'meta_key'	 => '_menu_item_url',
				'meta_value' => '',
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);

		return true;
	}

	public static function getMenuID( $location ) {
		$wpdb 	= AwbDbInterface::$destinationWpdb;

		$sql 	  = "SELECT * FROM wp_options WHERE option_name = 'theme_mods_news-pro'";
		$result   = $wpdb->get_results( $sql, OBJECT );

		$data = unserialize($result[0]->option_value);

		$ID = $data['nav_menu_locations'][$location];
		return $ID;

	}

}/* class ends here */

?>