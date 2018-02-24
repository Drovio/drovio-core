<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\reports;

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
 * @namespace	\reports
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "HttpResponse");
importer::import("ESS", "Protocol", "environment::Url");

use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\HttpResponse;
use \ESS\Protocol\environment\Url;

/**
 * Multipurpose Internet Mail Extensions (MIME) Server Report
 * 
 * Returns an http response and performs a download of a server file.
 * 
 * @version	0.1-1
 * @created	July 29, 2014, 22:49 (EEST)
 * @revised	July 29, 2014, 22:49 (EEST)
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
	 * 		See HttpResponse content types.
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
		ob_clean();
		
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
	 * 		See HttpResponse content types.
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