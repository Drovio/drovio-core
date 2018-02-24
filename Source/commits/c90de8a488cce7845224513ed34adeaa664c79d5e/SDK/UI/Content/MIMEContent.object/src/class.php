<?php
//#section#[header]
// Namespace
namespace UI\Content;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Content
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "http/HTTPResponse");
importer::import("ESS", "Protocol", "reports/MIMEServerReport");
importer::import("API", "Resources", "filesystem/fileManager");

use \ESS\Protocol\http\HTTPResponse;
use \ESS\Protocol\reports\MIMEServerReport;
use \API\Resources\filesystem\fileManager;

/**
 * Multipurpose Internet Mail Extensions (MIME) Content Builder
 * 
 * Builds a MIME server report and it can be used to download a server file.
 * 
 * @version	0.1-1
 * @created	December 23, 2014, 11:12 (EET)
 * @revised	December 23, 2014, 11:12 (EET)
 */
class MIMEContent
{
	/**
	 * The filename to download.
	 * 
	 * @type	string
	 */
	private $fileName;
	
	/**
	 * The type of the file. See HTTPResponse.
	 * 
	 * @type	string
	 */
	private $fileType;
	
	/**
	 * Prepares the file to be downloaded.
	 * 
	 * @param	string	$fileName
	 * 		The full path of the file to be downloaded.
	 * 
	 * @param	string	$type
	 * 		The type of the file.
	 * 		See HTTPResponse constants for a list of file types.
	 * 
	 * @return	MIMEContent
	 * 		The MIMEContent object.
	 */
	public function set($fileName, $type = HTTPResponse::CONTENT_APP_STREAM)
	{
		$this->fileName = $fileName;
		$this->fileType = $type;
		
		return $this;
	}
	
	/**
	 * Creates a stream report for downloading the file.
	 * 
	 * @param	string	$suggestedFileName
	 * 		The suggested file name for downloading the server file.
	 * 		Leave empty and it will be the file original name.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$ignore_user_abort
	 * 		Indicator for aborting the running script upon user cancelation.
	 * 
	 * @param	boolean	$removeFile
	 * 		Set to true to delete the file after downloading.
	 * 		If is false by default.
	 * 
	 * @return	void
	 */
	public function getReport($suggestedFileName = "", $ignore_user_abort = FALSE, $removeFile = FALSE)
	{
		// Return Report
		ob_clean();
		MIMEServerReport::get($this->fileName, $this->fileType, $suggestedFileName, $ignore_user_abort);
		
		// Remove file (if told)
		if ($removeFile)
			fileManager::remove($this->fileName);
	}
}
//#section_end#
?>