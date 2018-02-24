<?php
//#section#[header]
// Namespace
namespace DEV\Profiler;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Profiler
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Platform status handler
 * 
 * Sets and gets the platform status data.
 * 
 * @version	0.1-1
 * @created	February 11, 2014, 12:04 (EET)
 * @revised	August 24, 2014, 20:59 (EEST)
 */
class status
{
	/**
	 * The status resource index file.
	 * 
	 * @type	string
	 */
	const STATUS_FILE = "/System/Resources/status.xml";
	
	/**
	 * The platform status code for healthy platform.
	 * 
	 * @type	string
	 */
	const STATUS_OK = 200;
	
	/**
	 * The platform status code for platform with errors.
	 * 
	 * @type	string
	 */
	const STATUS_ERROR = 500;
	
	/**
	 * Initializes the platform status parser.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Create file
		$this->createFile();
	}
	
	/**
	 * Sets a specific platform status given a status code (class constant) and status description.
	 * 
	 * @param	string	$statusCode
	 * 		The status code.
	 * 		Use class constants.
	 * 		Otherwise it will be considered non valid and will return false.
	 * 
	 * @param	string	$statusValue
	 * 		The status description, usually used to describe errors.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setStatus($statusCode = self::STATUS_OK, $statusValue = "")
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		$root = $parser->evaluate("/platform")->item(0);
		
		// Get status entry
		$status = $parser->evaluate("/platform/status")->item(0);
		if (empty($status))
		{
			$status = $parser->create("status");
			$parser->prepend($root, $status);
		}
		
		// Check for valid status code.
		if ($statusCode != self::STATUS_OK && $statusCode != self::STATUS_ERROR)
			return FALSE;
		
		// Update status code and value and file
		$parser->attr($status, "code", $statusCode);
		$parser->nodeValue($status, $statusValue);
		return $parser->update();
	}
	
	/**
	 * Gets the platform status with code and description.
	 * 
	 * @return	array
	 * 		An array with the status values as:
	 * 		['code'] = The platform status code (see class constants)
	 * 		['description'] = The code description, usually in case of errors.
	 */
	public function getStatus()
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		$status = $parser->evaluate("/platform/status")->item(0);
		
		$platformStatus = array();
		$platformStatus['code'] = $parser->attr($status, "code");
		$platformStatus['description'] = $parser->nodeValue($status);
		
		return $platformStatus;
	}
	
	/**
	 * Creates the status index file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function createFile()
	{
		// Get file path
		if (file_exists(systemRoot.self::STATUS_FILE))
			return TRUE;
		
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("platform");
		$parser->append($root);
		
		fileManager::create(systemRoot.self::STATUS_FILE, "", TRUE);
		return $parser->save(systemRoot.self::STATUS_FILE, "", TRUE);
	}
}
//#section_end#
?>