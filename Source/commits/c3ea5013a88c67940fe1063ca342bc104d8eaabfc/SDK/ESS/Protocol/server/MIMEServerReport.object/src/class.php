<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::HttpResponse");
importer::import("ESS", "Protocol", "client::environment::Url");

use \ESS\Protocol\server\AsCoProtocol;
use \ESS\Protocol\server\HttpResponse;
use \ESS\Protocol\client\environment\Url;

/**
 * Multipurpose Internet Mail Extensions Server Report
 * 
 * Returns an http response and performs a download of a server file.
 * 
 * @version	{empty}
 * @created	April 16, 2013, 14:51 (EEST)
 * @revised	April 16, 2013, 14:51 (EEST)
 */
class MIMEServerReport
{
	/**
	 * Sets the response headers and reads the given file to be downloaded.
	 * 
	 * @param	string	$file
	 * 		The given file path.
	 * 
	 * @param	string	$type
	 * 		The response file Content-type.
	 * 
	 * @param	string	$suggestedFileName
	 * 		The suggested file name for downloading the server file.
	 * 
	 * @param	boolean	$ignore_user_abort
	 * 		Indicator for aborting the running script upon user cancelation.
	 * 
	 * @return	void
	 */
	public static function get($file, $type = HttpResponse::CONTENT_APP_STREAM, $suggestedFileName = "", $ignore_user_abort = FALSE)
	{
		// Set Response Headers
		self::setHeaders($file, $type, $suggestedFileName);

		// Set buffer settings
		ignore_user_abort($ignore_user_abort);
		ob_end_clean();
		ob_start();
		
		// Read file
		@readfile($file);
	}
	
	/**
	 * Set the httpResponse headers.
	 * 
	 * @param	string	$file
	 * 		The given file path.
	 * 
	 * @param	string	$type
	 * 		The response file Content-type.
	 * 
	 * @param	string	$suggestedFileName
	 * 		The suggested file name for downloading the server file.
	 * 
	 * @return	void
	 */
	private static function setHeaders($file, $type = HttpResponse::CONTENT_APP_STREAM, $suggestedFileName = "")
	{
		// Initialize Response
		HttpResponse::initialize();
		
		// Set Content Type
		HttpResponse::setContentType($type);
		
		// Set Allow Origin
		$subdomain = Url::getSubdomain();
		$domain = Url::getDomain();
		$allowOrigin = "http://".$subdomain.".".$domain;
		HttpResponse::setAllowOrigin($allowOrigin);
		
		// Set Remaining Response Headers
		header('Content-Disposition: attachment; filename='.$suggestedFileName);
		header('Content-Length: '.filesize($file));
	}
}
//#section_end#
?>