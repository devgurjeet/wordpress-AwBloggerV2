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

		$rssPosts 		= array();

		foreach ($rssDocument->getElementsByTagName('item') as $node ){

			$description 	=	preg_replace('~>\s+<~m', '><', $node->getElementsByTagName('description')->item(0)->nodeValue);
			$description 	= 	trim($description);

			$enclosurelink 	= $node->getElementsByTagName('enclosure');

			if( $enclosurelink->item(0)!=""){
				$URL 		= $enclosurelink->item(0)->getAttribute('url');
				$imagetype 	= $enclosurelink->item(0)->getAttribute('type');
			}else{
				$URL 		= "";
				$imagetype 	= "";
			}

			$title 	= preg_replace('/\s+/',' ',trim($node->getElementsByTagName('title')->item(0)->nodeValue));




			$postItem['post_title'] 		=	$title;
			$postItem['post_name'] 			= 	sanitize_title($title);
			$postItem['post_date'] 			= 	date("Y-m-d H:i:s",strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue));
			$postItem['description'] 		= 	$description;
			$postItem['excerpt'] 			= 	$description;

			$postItem['post_author'] 		= 	1;
			$postItem['category'] 			= 	preg_replace('~>\s+<~m', '><', $node->getElementsByTagName('category')->item(0)->nodeValue);

			$postItem['smblock'] 			= 	$node->getElementsByTagName('block')->item(0)->nodeValue;
			$postItem['smmetatitle']		= 	$node->getElementsByTagName('meta-title')->item(0)->nodeValue;
			$postItem['smmetadesc']			=	$node->getElementsByTagName('meta-description')->item(0)->nodeValue;
			$postItem['smmetaimage']		= 	$node->getElementsByTagName('meta-image')->item(0)->nodeValue;
			$postItem['enclosure'] 			= 	$URL;
			$postItem['post_mimie_type'] 	= 	$imagetype;
			$postItem['sourcelink'] 		= 	$node->getElementsByTagName('link')->item(0)->nodeValue;


			$rssPosts[] = $postItem;

		}
		return $rssPosts;
	}


}/* class ends here */
?>