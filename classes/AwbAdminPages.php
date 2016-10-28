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

		$log_fileName = "logs/awb_".date("Y_m_d_H_i_s").".txt";
		$creation_log =  AwbLog::getPluginDir().$log_fileName;

		$message = "======================= Blog Creation Log ==========================";
		AwbLog::writeLog($creation_log, $message);

		$message = "Source Blogname: ".$siteTemplate;
		AwbLog::writeLog($creation_log, $message);

		$message = "Config URL: ".$site_config;
		AwbLog::writeLog($creation_log, $message);

		$sourceBlog = AwbAdminPages::checkSourceBlog( $siteTemplate );

		if( $sourceBlog  ) {
			/* XML Config Reader Valid */
			$reader  = 	new AwbConfigReader( $site_config );
			$isConfigXMLValid = AwbXmlInterface::checkConfigXML($reader, $creation_log);

			if( $isConfigXMLValid ) {
				AwbXmlInterface::readConfigXML($reader);
				echo AwbXmlInterface::$title;

				echo "<br>";
				echo AwbXmlInterface::$address;
				echo "<br>";
				echo AwbXmlInterface::$title;
				echo "<br>";
				echo AwbXmlInterface::$email;
				echo "<br>";
				echo AwbXmlInterface::$language;

			} else {
				echo '<div style="color: red"><h3>Error in Reading config URL</h3>
						<p>Please check log for more details</p>.
					</div>';
			}

		} else {
			$message = "ERROR: Error in Selected Source Blog";
			AwbLog::writeLog($creation_log, $message);
			echo "<p>Error in Selected Source Blog.</p>";
		}

		$message = "====================================================================";
		AwbLog::writeLog($creation_log, $message);

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