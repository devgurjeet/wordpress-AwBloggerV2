<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbFormProcessor {
	public static $mode;
	/**
	 * [processCreateSiteForm: create blog ]
	 * @param  Array $formData
	 * @return boolean
	 */

	public static function processCreateSiteFormNormal( $formData ) {
		self::$mode =  $formData['mode'];

    	$siteTemplate = $formData['siteTemplate'];
		$site_config  = $formData['site_config'];

		$log_fileName = "awb_".date("Y_m_d_H_i_s").".txt";
		$creation_log =  AwbLog::SetLogFilename( $log_fileName );


		/*start time */
		$start = microtime(true);

		echo "<h1>Blog Creation log.</h1>";
		AwbLog::startLogging();

		$message = "Source Blogname: ".$siteTemplate;
		AwbLog::writeLog($message);

		$message = "Config URL: ".$site_config;
		AwbLog::writeLog($message);

		$sourceBlog = AwbAdminPages::checkSourceBlog( $siteTemplate );

		if( $sourceBlog  ) {
			/* XML Config Reader Valid */
			$reader  			= 	new AwbConfigReader( $site_config );
			$isConfigXMLValid 	= AwbXmlInterface::checkConfigXML($reader);

			if( $isConfigXMLValid ) {
				AwbXmlInterface::readConfigXML($reader);

				$result = AwbDbInterface::createDestinationDatabse(AwbXmlInterface::$address );
				if( $result ) {
					AwbWpInterface::setSource($siteTemplate);
					AwbDbInterface::setSourceDb();
					AwbDbInterface::setDestinationDb( AwbXmlInterface::$address );

					/*create destination Blog */
					$isCreated = AwbWpInterface::createDestinationDirectory(AwbXmlInterface::$address );

					if( $isCreated ){

						/* Initiate Blog Copy  */
						$isCopied = AwbWpInterface::copyBlog();
						if( $isCopied ) {
							/** copy database */
							$sourceDB      = AwbDbInterface::getDatabaseName($siteTemplate);
							$destinationDB = AwbDbInterface::getDatabaseName(AwbXmlInterface::$address);

							/* update wp-config.php */
							AwbWpInterface::updateWPconfig($destinationDB);

							$isDbCopied    = AwbDbInterface::copyDatabase($sourceDB, $destinationDB );
							if( $isDbCopied ){
								AwbDbInterface::updateWpOptions();
								$siteUrl = AwbWpInterface::getSiteUrl();

								/*add Htacess */
								AwbWpInterface::addHTACCESS();


								/*udpate AW Blogger List */
								AwbDbInterface::updateAwBloggerList();


								/*Insert Categories in destination blog.*/
								AwbDbInterface::insertCategories();

								/*Insert Feeds in destination blog.*/
								AwbDbInterface::insertFeeds();

								/*Insert Top Menu Items.*/
								AwbDbInterface::createTopMenu();

								/*Insert footer Menu Items.*/
								AwbDbInterface::createFooterMenu();

								/*Update post in Database*/
								AwbDbInterface::setupPosts();


								/*Add logging stats */
								$total 			= microtime(true) - $start;
								// $totalTime 		= $total / 1000;
								$totalTimeInSec = round($total, 2);

								$args['site_name']  		=	AwbXmlInterface::$title;
								$args['site_url']  			=	$siteUrl;
								$args['total_time']  		=	$totalTimeInSec."ms";
								$args['total_posts']  		=	AwbDbInterface::$total_posts;
								$args['total_categories']  	=	AwbDbInterface::$total_categories;
								$args['total_feeds'] 		=	AwbDbInterface::$total_feeds;

								AwbLog::addLoggingStats($args);

								echo '<h2>
										<span>Site created Successfully:</span> <a href="'.$siteUrl.'" target="_blank"> Click here to check site.</a>
									</h2>';

							} else {
								echo "<p>Database Not Cloned!</p>";
							}
						}
					}

				} else {
					$message  = "ERROR: Error in creating new Database.";
					echo "<p>".$message."</p>";
				}
			} else {

				echo '<div style="color: red"><h3>Error in Reading config URL</h3></div>';
			}

		} else {
			$message = "ERROR: Error in Selected Source Blog";
			AwbLog::writeLog($message);
			echo "<p>Error in Selected Source Blog.</p>";
		}
		AwbLog::endLogging();
		echo '<h4><a href="http://iris.scanmine.com/wp-content/plugins/awBloggerV2/logs/'.$log_fileName.'" target="_blank"> Click here to check log. </a></h4>';

	}

	public static function processCreateSiteFormAdvanced( $formData ) {
		self::$mode =  $formData['mode'];

    	$siteTemplate = $formData['siteTemplate'];
		$site_config  = $formData['site_config'];

		$log_fileName = "awb_".date("Y_m_d_H_i_s").".txt";
		$creation_log =  AwbLog::SetLogFilename( $log_fileName );


		/*start time */
		$start = microtime(true);

		echo "<h1>Blog Creation log.</h1>";
		AwbLog::startLogging();

		$message = "Source Blogname: ".$siteTemplate;
		AwbLog::writeLog($message);

		$message = "Config URL: ".$site_config;
		AwbLog::writeLog($message);

		$sourceBlog = AwbAdminPages::checkSourceBlog( $siteTemplate );

		if( $sourceBlog  ) {
			/* XML Config Reader Valid */
			$reader  			= 	new AwbConfigReader( $site_config );
			$isConfigXMLValid 	= AwbXmlInterface::checkConfigXML($reader);

			if( $isConfigXMLValid ) {
				AwbXmlInterface::readConfigXML($reader);

				$result = AwbDbInterface::createDestinationDatabse(AwbXmlInterface::$address );
				if( $result ) {
					AwbWpInterface::setSource($siteTemplate);
					AwbDbInterface::setSourceDb();
					AwbDbInterface::setDestinationDb( AwbXmlInterface::$address );

					/*create destination Blog */
					$isCreated = AwbWpInterface::createDestinationDirectory(AwbXmlInterface::$address );

					if( $isCreated ){

						/* Initiate Blog Copy  */
						$isCopied = AwbWpInterface::copyBlog();
						if( $isCopied ) {
							/** copy database */
							$sourceDB      = AwbDbInterface::getDatabaseName($siteTemplate);
							$destinationDB = AwbDbInterface::getDatabaseName(AwbXmlInterface::$address);

							/* update wp-config.php */
							AwbWpInterface::updateWPconfig($destinationDB);

							$isDbCopied    = AwbDbInterface::copyDatabase($sourceDB, $destinationDB );
							if( $isDbCopied ){
								AwbDbInterface::updateWpOptions();
								$siteUrl = AwbWpInterface::getSiteUrl();

								/*add Htacess */
								AwbWpInterface::addHTACCESS();


								/*udpate AW Blogger List */
								AwbDbInterface::updateAwBloggerList();


								/*Insert Categories in destination blog.*/
								AwbDbInterface::insertCategories();

								/*Insert Feeds in destination blog.*/
								AwbDbInterface::insertFeeds();

								/*Insert Top Menu Items.*/
								AwbDbInterface::createTopMenu();

								/*Insert footer Menu Items.*/
								AwbDbInterface::createFooterMenu();

								/*Update post in Database*/
								AwbDbInterface::setupPosts();


								/*Add logging stats */
								$total 			= microtime(true) - $start;
								// $totalTime 		= $total / 1000;
								$totalTimeInSec = round($total, 2);

								$args['site_name']  		=	AwbXmlInterface::$title;
								$args['site_url']  			=	$siteUrl;
								$args['total_time']  		=	$totalTimeInSec."ms";
								$args['total_posts']  		=	AwbDbInterface::$total_posts;
								$args['total_categories']  	=	AwbDbInterface::$total_categories;
								$args['total_feeds'] 		=	AwbDbInterface::$total_feeds;

								AwbLog::addLoggingStats($args);

								echo '<h2>
										<span>Site created Successfully:</span> <a href="'.$siteUrl.'" target="_blank"> Click here to check site.</a>
									</h2>';

							} else {
								echo "<p>Database Not Cloned!</p>";
							}
						}
					}

				} else {
					$message  = "ERROR: Error in creating new Database.";
					echo "<p>".$message."</p>";
				}
			} else {

				echo '<div style="color: red"><h3>Error in Reading config URL</h3></div>';
			}

		} else {
			$message = "ERROR: Error in Selected Source Blog";
			AwbLog::writeLog($message);
			echo "<p>Error in Selected Source Blog.</p>";
		}
		AwbLog::endLogging();
		echo '<h4><a href="http://iris.scanmine.com/wp-content/plugins/awBloggerV2/logs/'.$log_fileName.'" target="_blank"> Click here to check log. </a></h4>';
    }

}/* class ends here */

?>

