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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/MIMEServerReport");
importer::import("API", "Resources", "filesystem/fileManager");

use \ESS\Protocol\reports\MIMEServerReport;
use \API\Resources\filesystem\fileManager;

/**
 * Multipurpose Internet Mail Extensions (MIME) Content Builder
 * 
 * Builds a MIME server report and it can be used to download a server file.
 * 
 * @version	2.0-1
 * @created	December 23, 2014, 11:12 (EET)
 * @updated	September 12, 2015, 23:56 (EEST)
 */
class MIMEContent extends MIMEServerReport
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
	public function set($fileName, $type = self::CONTENT_APP_STREAM)
	{
		$this->fileName = $fileName;
		$this->fileType = $type;
		
		return $this;
	}
	
	/**
	 * Prepares the file contents to be downloaded.
	 * 
	 * @param	mixed	$contents
	 * 		The contents to be downloaded.
	 * 
	 * @param	string	$type
	 * 		The type of the contents given.
	 * 		See HTTPResponse constants for a list of file types.
	 * 
	 * @return	MIMEContent
	 * 		The MIMEContent object.
	 */
	public function setFileContents($contents, $type = self::CONTENT_APP_STREAM)
	{
		// Create temp file
		$fileName = sys_get_temp_dir()."/mime_file_".mt_rand();
		fileManager::create($fileName, $contents);
		
		// Set file
		return $this->set($fileName, $type);
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
	 * @return	mixed
	 * 		The file stream report to be sent to the output buffer.
	 */
	public function getReport($suggestedFileName = "", $ignore_user_abort = FALSE, $removeFile = FALSE)
	{
		// Return Report
		$report = parent::get($this->fileName, $this->fileType, $suggestedFileName, $ignore_user_abort);
		
		// Remove file (if told)
		if ($removeFile)
			fileManager::remove($this->fileName);
		
		return $report;
	}
}
//#section_end#
?>