<?php
//#section#[header]
// Namespace
namespace API\Content;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Content
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Security", "account");
importer::import("API", "Resources", "storage::session");

use \API\Resources\storage\session;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Security\account;

/**
 * module call analytics
 * 
 * Capture and log data associated with a module call
 * 
 * @version	{empty}
 * @created	October 25, 2013, 11:11 (EEST)
 * @revised	October 30, 2013, 12:21 (EET)
 */
class analytics
{
	/**
	 * The path of log file
	 * 
	 * @type	string
	 */
	const VISITS_FOLDER = "/analytics/visits/";
	
	
	/**
	 * Log a single data entry into log file
	 * 
	 * @param	array	$rbData
	 * 		path : The path from were the call made
	 * 		moduleID : The requested module Id
	 * 		action : The requested action (aux module)
	 * 		access : The access code returned from access control
	 * 
	 * @return	void
	 */
	public static function log($rbData)
	{
		// Get current timestamp
		$timestamp = time();
		
		// Try to identify if this module was called by a page aka its HTMLModulePage not HTMLContent
		$varNamespace = 'analytics';
		$key = "m_".$rbData['moduleID'];
		$tmSt = session::get($key, NULL, $varNamespace);
		
		// Delete from session		
		if(!is_null($tmSt))
		{
			session::clear($key, $varNamespace);
			// Best case -> not store
			//return;
		}
		
		
		
		
		// Check if log file exists
		$fileName = self::getFileName();
		$filePath = paths::getSysDynRsrcPath().self::VISITS_FOLDER;
		if (!file_exists(systemRoot.$filePath.$fileName))
			self::createFile();
		
		// Load log file
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//visits")->item(0);
		
		// Create entry file
		$entry = $parser->create("entry");
		$parser->append($root, $entry);
		$parser->attr($entry, "time", $timestamp);
		
		// Server Client Specific
		$srvEntry = $parser->create("client");
		$parser->append($entry, $srvEntry);			
			// Session Ket
			$infoEntry = $parser->create("ssKey", session::get_id());
			$parser->append($srvEntry, $infoEntry);
			// Browser
			$infoEntry = $parser->create("browser", $_SERVER['HTTP_USER_AGENT']);
			$parser->append($srvEntry, $infoEntry);
				// Parse contents futhermore for bettr use, may not work in some cases, depents on servers  browscap.ini
				$browser = get_browser(NULL, true);
				// browser and version
				//$infoEntry = $parserparser->attr($infoEntry,"", $browser[parent]);				
				// platform
				$parser->attr($infoEntry, "platform", $browser['platform']);				
	   	 		// browser
				$parser->attr($infoEntry, "browser", $browser['browser']);	
	    			// browser Version
				$parser->attr($infoEntry, "browserVer", $browser['version']);
    		
			// Client Address
			$infoEntry = $parser->create("ip", $_SERVER['REMOTE_ADDR']);
			$parser->append($srvEntry, $infoEntry);
			// Client Port
			$infoEntry = $parser->create("port", $_SERVER['REMOTE_PORT']);
			$parser->append($srvEntry, $infoEntry);
			// Request uri
			$infoEntry = $parser->create("uri", $_SERVER['HTTP_REFERER']);
			$parser->append($srvEntry, $infoEntry);
		
		
		// Redback request specific
		$rbEntry = $parser->create("action");
		$parser->append($entry, $rbEntry);
			// Requested Path
			$infoEntry = $parser->create("path", $rbData['path']);
			$parser->append($rbEntry, $infoEntry);
			// Module ID
			$infoEntry = $parser->create("moduleID", $rbData['moduleID']);
			$parser->append($rbEntry, $infoEntry);
			// Module Action
			$infoEntry = $parser->create("auxiliary", $rbData['action']);
			$parser->append($rbEntry, $infoEntry);
			// Is quest user
			$guest = account::validate() ? '0' : '1';
			$infoEntry = $parser->create("guest", $guest);
			$parser->append($rbEntry, $infoEntry);
			// Access
			$infoEntry = $parser->create("access", $rbData['access']);
			$parser->append($rbEntry, $infoEntry);
			if(!is_null($tmSt))
			{
				// by page
				$infoEntry = $parser->create("page", '1');
				$parser->append($rbEntry, $infoEntry);
				
				// tGap
				$infoEntry = $parser->create("tGap", strval(intval($timestamp) - intval($tmSt)));
				$parser->append($rbEntry, $infoEntry);
			}
		
		
		// Save File
		$parser->update();
	}
	
	public static function getData($date = "")
	{
		if (empty($date))
			$fileName = self::getFileName();
		else
			$fileName = "visits_".$date.".xml";
		
		$filePath = paths::getSysDynRsrcPath().self::VISITS_FOLDER;
		if (!file_exists(systemRoot.$filePath.$fileName))
			return FALSE;
			
		// Load log file
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//visits")->item(0);
		$entries = $parser->evaluate("//entry", $root);
		
		$data = array();
		foreach($entries as $entry)
		{
			$entr = array();
			
			$entr["time"] = $parser->attr($entry, "time");
			$entr["browser"] = $parser->evaluate("client/browser", $entry)->item(0)->nodeValue;
			$entr["ip"] = $parser->evaluate("client/ip", $entry)->item(0)->nodeValue;
			$entr["uri"] = $parser->evaluate("client/uri", $entry)->item(0)->nodeValue;
			$entr["path"] = $parser->evaluate("action/path", $entry)->item(0)->nodeValue;
			$entr["moduleID"] = $parser->evaluate("action/moduleID", $entry)->item(0)->nodeValue;
			$entr["auxiliary"] = $parser->evaluate("action/auxiliary", $entry)->item(0)->nodeValue;
			$entr["guest"] = $parser->evaluate("action/guest", $entry)->item(0)->nodeValue;
			$entr["access"] = $parser->evaluate("action/access", $entry)->item(0)->nodeValue;
			
			$entr["page"] = $parser->evaluate("action/page", $entry)->item(0)->nodeValue;
			$entr["tGap"] = $parser->evaluate("action/tGap", $entry)->item(0)->nodeValue;
			
			$data[] = $entr;
		}
		
		return $data;
	}
	
	/**
	 * Creates the log file for the day.
	 * 
	 * @return	void
	 */
	private static function createFile()
	{
		$fileName = self::getFileName();
		$filePath = paths::getSysDynRsrcPath().self::VISITS_FOLDER;
		fileManager::create(systemRoot.$filePath.$fileName, "", TRUE);
		
		// Create DOMParser
		$parser = new DOMParser();
		
		// Create root
		$root = $parser->create("visits");
		$parser->append($root);
		
		// Save Log
		$parser->save(systemRoot.$filePath, $fileName, TRUE);
	}
	
	/**
	 * Gets the log filename for the day.
	 * 
	 * @return	string
	 * 		{description}
	 */
	private static function getFileName()
	{
		return "visits_".date("Y-m-d").".xml";
	}
}
//#section_end#
?>