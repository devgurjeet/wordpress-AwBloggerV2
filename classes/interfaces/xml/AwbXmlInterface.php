<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AwbXmlInterface {

	public static $reader;

	public static $address;
	public static $title;
	public static $email;
	public static $theme;
	public static $template;
	public static $description;
	public static $topic;
	public static $language;


	/**
	 * [checkConfigXML check if the input xml $url is valid.]
	 * @param  Object $reader
	 * @param  string $creation_log: name of the error log.
	 * @return boolean
	 */
	public static function checkConfigXML( $reader ){
		$errorMessages = array();

		/*check input XML file */
		$reader->parse();

		//** Loop All Error in Config file. **//
		foreach ($reader->getErrors() as $error){
			$errorMessages[] = $error;
		}

		if( !empty( $errorMessages ) ){
			$message = "Error: Problem Reading config URL";
			AwbLog::writeLog($message);

			foreach($errorMessages as $errorMessage){

				$message = $errorMessage;
				AwbLog::writeLog($message);
			}
			return false;
		} else {
			self::$reader = $reader;
			$message = "Success: XML parsed Successfully.";
			AwbLog::writeLog($message);
			return true;
		}
	}

	public static function readConfigXML( $reader ){
		self::$address 		= (string) $reader->getProperty('address');
		self::$title 		= (string) $reader->getProperty('title');
		self::$email 		= (string) $reader->getProperty('owner');
		self::$theme 		= (string) $reader->getProperty('theme');
		self::$template 	= (string) $reader->getProperty('template');
		self::$description 	= (string) $reader->getProperty('description');
		self::$topic 		= (string) $reader->getProperty('topic');
		self::$language 	= (string) AwbXmlInterface::getBlogLanguageFromXml($reader);

		$message = "Destination Blog Address: ".self::$address;
		AwbLog::writeLog($message);

		$message = "Destination Blog Title: ".self::$title;
		AwbLog::writeLog($message);

	}



	/*function to get blog Language. */
	public static function getBlogLanguageFromXml( $xmlOptions ) {
		$language       = 'en';
		$languageReturn = '';
		$options_data   = $xmlOptions->getProperty('options');
		$multi_array    = json_decode( json_encode($options_data) , 1);

		$options = array_values($multi_array)[0];

		foreach ($options as $option) {
			if( $option['name'] !== 'language'){
				continue;
			}
			$language = $option['value'];
		}

		switch ($language) {
			case 'en':
				$languageReturn = 'en_GB';
				break;

			case 'se':
				$languageReturn = 'sv_SE';
				break;

			case 'no':
				$languageReturn = 'nb_NO';
				break;

			default:
				$languageReturn = 'en_GB';
				break;
		}
		return $languageReturn;
	}

	/* Retuns the pages form  XML */
	/* these pagas will be stroed as categories. */
	public static function getPages() {

		$returnPages = array();

		$pages 	= self::getArrayFormXMLObject( 'pages' );

		if (isset($pages['title'])) {
			$returnPages[] =$pages['title'];
		}else{
			foreach ($pages as $page ) {
				$returnPages[] =$page['title'];
			}
		}
		return $returnPages;

	}

	/* Retuns the Feeds form  XML */
	/* these pagas will be stroed as Links. */
	public static function getFeeds() {
		$returnFeeds = array();
		$feeds 	= self::getArrayFormXMLObject( 'feeds' );

		if (!is_array($feeds)) {
			$returnFeeds[] = trim($feeds);
		}else{
			foreach ($feeds as $feed ) {
				$returnFeeds[] = trim($feed);
			}
		}
		return $returnFeeds;
	}


	public static function getArrayFormXMLObject( $itemTitle = '' ){
		if( $itemTitle == '' ){
			return false;
		}

		$xmlObject = self::$reader;
		$options_data   = $xmlObject->getProperty($itemTitle);
		$multi_array    = json_decode( json_encode($options_data) , 1);

		$items = array_values($multi_array)[0];

		return $items;
	}


}/* class ends here */

?>