<?php
//#section#[header]
// Namespace
namespace API\Content\analytics\collectors;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Security", "account");
importer::import("API", "Resources", "storage::session");
importer::import("DEV", "Resources", "paths");

use \API\Resources\storage\session;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Security\account;
use \DEV\Resources\paths;

class pageLoads
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
		
		// Add The module page will call to session
		// to avoid duplicate info logging
		$varNamespace = 'analytics';
		$key = "m_".$rbData['moduleID'];
		session::set($key, $timestamp, $varNamespace);		
		
		// Create entry file
		$entry = $parser->create("entry");
		$parser->append($root, $entry);
		$parser->attr($entry, "time", $timestamp);
		
		// Server Client Specific
		$srvEntry = $parser->create("client");
		$parser->append($entry, $srvEntry);			
			// Browser
			$infoEntry = $parser->create("browser", $_SERVER['HTTP_USER_AGENT']);
			$parser->append($srvEntry, $infoEntry);
				// Parse contents futhermore for bettr use, may not work in some cases, depents on servers  browscap.ini
				$browser = get_browser(NULL, TRUE);
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
			/*
			$infoEntry = $parser->create("port", $_SERVER['REMOTE_PORT']); 
			$parser->append($srvEntry, $infoEntry);
			*/
			// Referer
			$infoEntry = $parser->create("referer", $_SERVER['HTTP_REFERER']);
			$parser->append($srvEntry, $infoEntry);			
			// domain
			$infoEntry = $parser->create("domain", $_SERVER['HTTP_HOST']);
			$parser->append($srvEntry, $infoEntry);	
			// uri path (after the domain)
			$infoEntry = $parser->create("uri", $_SERVER['PHP_SELF']);
			$parser->append($srvEntry, $infoEntry);			
			// Query String
			$infoEntry = $parser->create("qString", $_SERVER['QUERY_STRING']);
			$parser->append($srvEntry, $infoEntry);
			// Request time
			//$infoEntry = $parser->create("rqTime", $_SERVER['REQUEST_TIME_FLOAT']);
			$infoEntry = $parser->create("rqTime", "".$_SERVER['REQUEST_TIME']);
			$parser->append($srvEntry, $infoEntry);
		
		
		// Redback request specific
		$rbEntry = $parser->create("action");
		$parser->append($entry, $rbEntry); 
			// Module ID
			$infoEntry = $parser->create("moduleID", $rbData['moduleID']);
			$parser->append($rbEntry, $infoEntry);		
			// Is Static
			$static = $rbData['static'] ? '1' : '0';
			$infoEntry = $parser->create("static", $static);
			$parser->append($rbEntry, $infoEntry);
			// Execution Time 
			$infoEntry = $parser->create("execTime", $rbData['execTime']);
			$parser->append($rbEntry, $infoEntry);
			// Access
			//$infoEntry = $parser->create("access", $rbData['access']);
			//$parser->append($rbEntry, $infoEntry);
			// domain_description
			$infoEntry = $parser->create("dDesc", $rbData['dDesc']);
			$parser->append($rbEntry, $infoEntry);
			// domain_path
			$infoEntry = $parser->create("dPath", $rbData['dPath']);
			$parser->append($rbEntry, $infoEntry);
			
		
		// Save File
		$parser->update();
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
		return "pgVisits_".date("Y-m-d").".xml";
	}
}
//#section_end#
?>