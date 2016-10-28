<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* plugin Constants */
define('DEFAULT_SITE_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);

class AwtConfig {

    public static function getRootSitePath() {

        return DEFAULT_SITE_ROOT_PATH;
    }

    public static function getSourceTemplate( $siteTemplate = null ){

		if( $siteTemplate == null OR $siteTemplate == '' ){
			return false;
		}
		$wordpress_folder = AwtConfig::getRootSitePath()."/".$siteTemplate;
		return $wordpress_folder;
		// echo "BlogTemplate: $wordpress_folder";
	}

}/*class ends here*/

?>