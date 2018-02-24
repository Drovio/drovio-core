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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "http/HTTPResponse");
importer::import("ESS", "Environment", "url");

use \ESS\Protocol\http\HTTPResponse;
use \ESS\Environment\url;

/**
 * Multipurpose Internet Mail Extensions (MIME) Server Report
 * 
 * Returns an http response and performs a download of a server file.
 * 
 * @version	0.1-5
 * @created	July 29, 2014, 22:49 (EEST)
 * @updated	May 25, 2015, 11:49 (EEST)
 */
class MIMEServerReport extends HTTPResponse
{
	/**
	 * Sets the response headers and reads the given file to be downloaded.
	 * 
	 * @param	string	$file
	 * 		The path of the file to be downloaded.
	 * 
	 * @param	string	$type
	 * 		The response file Content-type.
	 * 		See HttpResponse content types.
	 * 
	 * @param	string	$suggestedFileName
	 * 		The suggested file name for downloading the server file.
	 * 		Leave empty and it will be the file original name.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$ignore_user_abort
	 * 		Indicator for aborting the running script upon user cancelation.
	 * 
	 * @return	void
	 */
	public static function get($file, $type = self::CONTENT_APP_STREAM, $suggestedFileName = "", $ignore_user_abort = FALSE)
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
	 * 		Leave empty and it will be the file original name.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	private static function setHeaders($file, $type = self::CONTENT_APP_STREAM, $suggestedFileName = "")
	{
		// Initialize Response
		parent::initialize();
		
		// Set Content Type
		parent::setContentType($type);
		
		// Set Allow Origin
		$urlInfo = url::info();
		$protocol = $urlInfo['protocol'];
		$subdomain = url::getSubdomain();
		$domain = url::getDomain();
		$allowOrigin = $protocol."://".$subdomain.".".$domain;
		
		parent::setAllowOrigin($allowOrigin);
		
		// Set Remaining Response Headers
		header('Content-Disposition: attachment; filename='.$suggestedFileName);
		header('Content-Length: '.filesize($file));
	}
}
//#section_end#
?>