<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbAdminPages {
	/**
     * Admin page for AWBloggerV2
     * @return void
    */
    public static function create_blog() {
    	if( isset($_POST['action']) ){
    		$result = AwbFormProcessor::processCreateSiteFormNormal($_POST);
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
								<input type="hidden" name="action" value="add_site" />
							  	<input type="hidden" name="mode" value="normal" />

							  	<input type="submit" value="Add Site" class="submt_btn button button-primary" id="add-aw-blog-site">
							</p>
						</form>
					</div>';

		echo $html;
    }


    public static function createBlogDomain(){

    	if( isset($_POST['action']) ){
    		$result = AwbFormProcessor::processCreateSiteFormAdvanced($_POST);
    		if( $result ) {
    			echo "<h1>Site Created Successfully!";
    		}
    		die();
    	}

    	$html	= 	'<div class="wrap">
						<h2 id="add-new-feed-site">Add New Feed Based Sites With Domain</h2>';
		$html	.= 		'<form enctype="multipart/form-data" action="" method="post">';
		$html   .= 			'<div class="main_dom">
						<div class="field_row">';
		$html	.= 			AwbDbInterface::getBloglist();
		$html   .=		'</div>
						<div class="field_row">
							<textarea  name="site_config" id="site_config" placeholder="Enter Config Url."></textarea>
							<p>Note: Enter Single Congif files url.</p>
						</div>
					</div>';

		$html   .=	'<div class="main_dom">
						<div class="field_row">
							<input type="text" name="domain_name_url" id="domain_name_url" class="domain_name" placeholder="Domain URL">
							<span class="error_msg_domain_name_url"> Please Enter Domain Name. </span>
						</div>
						<div class="field_row">
							<input type="text" name="dom_alias" id="dom_alias" class="domain_name" placeholder="Domain Alias">
							<span class="error_msg_domain_alias"> Please Enter Domain ALias. </span>
						</div>
						<div class="field_row">
							<input type="text" name="config_name" id="config_name" class="domain_name" placeholder="Config File Name">
							<span class="error_msg_config_name"> Please Enter Config File Name. </span>
						</div>
					</div>

					  <p class="submit submt_p">
					  	<input type="hidden" name="action" value="add_site" />
					  	<input type="hidden" name="mode" value="advanced" />

					  	<input type="submit" value="Add Site" class="submt_btn button button-primary" id="add-aw-blog-site">

					  </p>
					  </form>
					</div>';

		echo $html;
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