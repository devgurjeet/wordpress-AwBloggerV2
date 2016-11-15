<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbRssInterface {

	public static function getFeedDetails( $rss ){
		$rssDocument = new DOMDocument();

		$rssDocument->load($rss);

		$title 			= $rssDocument->getElementsByTagName('title')->item(0)->nodeValue;
		$link_url 		= $rssDocument->getElementsByTagName('link')->item(0)->nodeValue;
		$description 	= $rssDocument->getElementsByTagName('description')->item(0)->nodeValue;
		$ttl 			= $rssDocument->getElementsByTagName('ttl')->item(0)->nodeValue;
		$pubDate 		= $rssDocument->getElementsByTagName('pubDate')->item(0)->nodeValue;

		$rssFeedDetaials = array(
									'link_name'		=>	$title,
									'link_url'		=>	$link_url,
									'description'	=>	$description,
									'ttl'			=>	$ttl,
									'pubDate'		=>	$pubDate
								);

		return $rssFeedDetaials;
	}


	public static function getPosts( $rss ){
		$rssDocument = new DOMDocument();

		$rssDocument->load($rss);

		$title 			= $rssDocument->getElementsByTagName('title')->item(0)->nodeValue;
		$link_url 		= $rssDocument->getElementsByTagName('link')->item(0)->nodeValue;
		$description 	= $rssDocument->getElementsByTagName('description')->item(0)->nodeValue;
		$ttl 			= $rssDocument->getElementsByTagName('ttl')->item(0)->nodeValue;
		$pubDate 		= $rssDocument->getElementsByTagName('pubDate')->item(0)->nodeValue;


		foreach ($doc->getElementsByTagName('item') as $node ){

			$description 	=	preg_replace('~>\s+<~m', '><', $node->getElementsByTagName('description')->item(0)->nodeValue);
			$description 	= 	trim($description);
			$chart_count	=	0;


			echo "<pre>";
			print_r($node );
			echo "</pre>";
		}



	}


}/* class ends here */
?>