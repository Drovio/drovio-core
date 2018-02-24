<?php
//#section#[header]
// Namespace
namespace UI\Apps;

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
 * @package	Apps
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("AEL", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Content", "MIMEContent");

use \AEL\Resources\filesystem\fileManager as AppFileManager;
use \API\Resources\filesystem\fileManager;
use \UI\Content\MIMEContent;

/**
 * Application Multipurpose Internet Mail Extensions (MIME) Content Builder
 * 
 * Builds a MIME server report and it can be used to download an application file.
 * 
 * @version	0.1-1
 * @created	September 12, 2015, 23:58 (EEST)
 * @updated	September 12, 2015, 23:58 (EEST)
 */
class APPMIMEContent extends MIMEContent
{
	/**
	 * The account file mode.
	 * 
	 * @type	integer
	 */
	const ACCOUNT_MODE = 1;
	
	/**
	 * The team file mode.
	 * 
	 * @type	integer
	 */
	const TEAM_MODE = 2;
	
	/**
	 * Prepares the file to be downloaded.
	 * 
	 * @param	string	$fileName
	 * 		The relative path of the file to be downloaded.
	 * 
	 * @param	string	$type
	 * 		The type of the file.
	 * 		See HTTPResponse constants for a list of file types.
	 * 
	 * @param	integer	$mode
	 * 		The file mode.
	 * 		See class constants for options.
	 * 		It is in account mode by default.
	 * 
	 * @return	APPMIMEContent
	 * 		The APPMIMEContent object.
	 */
	public function set($fileName, $type = self::CONTENT_APP_STREAM, $mode = self::ACCOUNT_MODE)
	{
		// Get file contents
		$fm = new AppFileManager($mode);
		$fileContents = $fm->get($fileName);
		return $this->setFileContents($fileContents, $type);
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
	 * @return	APPMIMEContent
	 * 		The APPMIMEContent object.
	 */
	public function setFileContents($contents, $type = self::CONTENT_APP_STREAM)
	{
		// Create temp file
		$fileName = sys_get_temp_dir()."/mime_file_".mt_rand();
		fileManager::create($fileName, $contents);
		
		// Set file
		return parent::set($fileName, $type);
	}
}
//#section_end#
?>