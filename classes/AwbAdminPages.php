<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbAdminPages {
	/**
     * Admin page for AWBloggerV2
     * @return void
    */
    public static function create_blog() {
    	if( isset($_POST['action']) ){
    		$result = AwbAdminPages::processCreateSiteForm($_POST);
    		if( $result ) {
    			echo "<h1>Site Created Successfully!";
    		}
    		die();
    	}

        $html	= 	'<div class="wrap">
						<h2 id="add-new-feed-site">Add New Feed Based Sites</h2>';
		$html	.= 		'<form enctype="multipart/form-data" action="" method="post">';
		$html   .= 			'<div class="main_dom">
								<div class="field_row">';
		$html	.= 					AwbDbInterface::getBloglist();
		$html   .=  			'</div>
								<div class="field_row">
									<textarea  name="site_config" id="site_config" placeholder="Enter Config Url."></textarea>
									<p>Note: Enter Single Congif files url.</p>
								</div>
							</div>
							<p class="submit submt_p">
								<input type="submit" value="Add Site" class="submt_btn button button-primary" id="add-aw-blog-site" name="add-aw-blog-site">
								<input type="hidden" name="action" value="add_site" />
								<input type="hidden" name="site_hurl" value="" />
								<input type="hidden" name="site-s-slug" value="" />
							</p>
						</form>
					</div>';

		echo $html;
    }

    /**
     * [processCreateSiteForm description]
     * @param  Array $formData
     * @return boolean
     */
    public static function processCreateSiteForm( $formData ) {

    	$siteTemplate = $formData['siteTemplate'];
		$site_config  = $formData['site_config'];

		$log_fileName = "awb_".date("Y_m_d_H_i_s").".txt";
		$creation_log =  AwbLog::SetLogFilename( $log_fileName );

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

			// // $pages = AwbXmlInterface::getPages();
			// $feeds = AwbXmlInterface::getFeeds();

			// foreach ($feeds as $feed) {
			// 	// $feedData = AwbRssInterface::getFeedDetails($feed);
			// 	AwbRssInterface::getPosts($feed);
			// }

			// die;


			// die;

			// echo "<pre>";
			// print_r( $pages );
			// echo "</pre>";

			// echo "<br />Break Point<br />";
			// die();

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

    /**
     * [checkSourceBlog check if blog exists on the server.]
     * @param  String $siteTemplate source Blogname
     * @return string/boolean  Returns blog path if exists or returns false if not exists
     */
    public static function checkSourceBlog( $siteTemplate ) {
    	$wordpress_folder = AwtConfig::getSourceTemplate( $siteTemplate );

    	if (file_exists( $wordpress_folder ) &&  is_dir( $wordpress_folder ) ){
    		return $wordpress_folder;
    	} else {
    		return false;
    	}
    }
}/* class ends here */

?>