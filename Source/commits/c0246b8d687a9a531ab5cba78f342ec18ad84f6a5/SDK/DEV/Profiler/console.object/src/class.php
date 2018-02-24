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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Profile", "tester");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Resources", "paths");

use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\storage\session;
use \API\Resources\DOMParser;
use \DEV\Profile\tester as profileTester;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Resources\paths;

/**
 * Server Console Class
 * 
 * This class allows users of Redback and developers to run free code and get the results of the execution.
 * Simple users can just execute php code, while redback developers can have access to the SDK through the importer.
 * 
 * @version	0.1-4
 * @created	February 17, 2014, 12:05 (EET)
 * @updated	January 14, 2015, 18:40 (EET)
 */
class console
{
	/**
	 * Runs a given php code in redback php server.
	 * The code is safely escaped to prevent malicious functions.
	 * 
	 * @param	string	$code
	 * 		The php code.
	 * 
	 * @param	boolean	$headers
	 * 		This allows to add specific headers to the php code in order to use the system's SDK.
	 * 
	 * @param	array	$dependencies
	 * 		An array of dependencies for the code to run.
	 * 		It works only when headers are specified.
	 * 
	 * @param	boolean	$saveHistory
	 * 		Whether to save this script to history log.
	 * 
	 * @return	string
	 * 		The php code output.
	 */
	public static function php($code, $headers = FALSE, $dependencies = array(), $saveHistory = FALSE)
	{echo "console execution\n";
		// Clear code and remove unsafe functions
		$code = phpParser::clear($code);
		$code = phpCoder::safe($code);
		
		// Save history log
		if ($saveHistory)
			self::saveHistory($code, $dependencies);

		// Add dependencies (if any) and headers
		if ($headers)
		{
			$finalCode = self::addSystemDependencies($code, $dependencies);
			$finalCode = self::addHeaders($finalCode);
		}
		
		// Wrap code into php file
		$finalCode = phpParser::wrap($finalCode);
		
		// Save temporary file
		$file = md5(microtime()."+".rand()."+".session::getID()).".php";
		$tempFilePath = profileTester::getTrunk()."/console/".$file;
		fileManager::create(systemRoot.$tempFilePath, $finalCode, TRUE);
		
		// Check for syntax errors and return the error if found
		$syntax = phpParser::syntax(systemRoot.$tempFilePath);
		if ($syntax !== TRUE)
		{
			$output = "There are syntax errors in your code.\n";
			$output .= "This version of console doesn't support details yet.";
		}
		else
		{
			// Clean buffer
			ob_clean();
			ob_start();
			
			// Run script, get buffer contents and the clear the buffer
			try
			{
				importer::incl($tempFilePath);
				$output = ob_get_clean();
				if (empty($output))
					$output = "The script executed successfully!";
			}
			catch (Exception $ex)
			{
				// Clean buffer
				ob_end_clean();
				
				// Prettify exception message
				$output = "Exception Thrown!\n";
				$output .= "Message: ".$ex->getMessage()."\n";
			}
		}
		
		// Delete file and return output
		fileManager::remove(systemRoot.$tempFilePath);
		return $output;
	}
	
	/**
	 * Gets all user's history log of executed code.
	 * 
	 * @return	array
	 * 		An array of history logs as id and time.
	 */
	public static function getHistoryLog()
	{
		// Clear history first
		self::clearHistoryLog();
		
		$historyEntries = array();
		
		// Get history entries
		$parser = new DOMParser();
		try
		{
			$parser->load(profileTester::getTrunk()."/console/index.xml");
		}
		catch (Exception $ex)
		{
			return $historyEntries;
		}
		
		$entries = $parser->evaluate("//hentry");
		foreach ($entries as $entry)
		{
			$history = array();
			$history['id'] = $parser->attr($entry, "id");
			$history['time'] = $parser->attr($entry, "time");
			
			$historyEntries[] = $history;
		}
		
		return $historyEntries;
	}
	
	/**
	 * Gets a history entry from the log given a history id.
	 * 
	 * @param	string	$id
	 * 		The history id to load.
	 * 
	 * @return	array
	 * 		An array of data:
	 * 		['dependencies'] and
	 * 		['code'].
	 */
	public static function getFromHistory($id)
	{
		// Get history folder
		$historyFolder = self::getHistoryFolder($id);
		
		// Get dependencies
		$dependencies = array();
		$parser = new DOMParser();
		try
		{
			$parser->load($historyFolder."/dependencies.xml");
		}
		catch (Exception $ex)
		{
			return array();
		}
		$deps = $parser->evaluate("//dep");
		foreach ($deps as $dep)
		{
			$library = $parser->attr($dep, "lib");
			$package = $parser->attr($dep, "pkg");
			$dependencies[$library][] = $package;
		}
		
		$code = fileManager::get(systemRoot.$historyFolder."/code.php");
		
		// Return history
		$history = array();
		$history['dependencies'] = $dependencies;
		$history['code'] = $code;
		return $history;
	}
	
	/**
	 * Remove an entry from history.
	 * 
	 * @param	string	$id
	 * 		The history id to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function removeFromHistory($id)
	{
		// Remove index entry
		$parser = new DOMParser();
		$parser->load(profileTester::getTrunk()."/console/index.xml");
		
		$entry = $parser->find($id);
		$parser->replace($entry, NULL);
		$parser->update();
		
		// Remove history folder
		return folderManager::remove(systemRoot.$id, "", TRUE);
	}
	
	/**
	 * Save a console entry to history.
	 * 
	 * @param	string	$code
	 * 		The php code.
	 * 
	 * @param	array	$dependencies
	 * 		The array of dependencies as received from the php() function.
	 * 
	 * @return	void
	 */
	private static function saveHistory($code, $dependencies)
	{
		// Create testing trunk console folder
		folderManager::create(systemRoot.profileTester::getTrunk()."/console/");
		
		// Create index entry
		$parser = new DOMParser();
		try
		{
			$parser->load(profileTester::getTrunk()."/console/index.xml");
			$root = $parser->evaluate("/console")->item(0);
		}
		catch (Exception $ex)
		{
			$root = $parser->create("console");
			$parser->append($root);
			$parser->save(systemRoot.profileTester::getTrunk()."/console/index.xml");
		}

		// Create history id
		$history_id = md5(microtime()."+".rand()."+".session::getID());
		
		// Create emtru
		$entry = $parser->create("hentry", "", $history_id);
		$parser->attr($entry, "time", time());
		$parser->prepend($root, $entry);

		// Update file
		$parser->update();
		
		// Create history folder and files
		$historyFolder = self::getHistoryFolder($history_id);
		folderManager::create(systemRoot.$historyFolder);
		
		// Create dependencies xml file
		$parser = new DOMParser();
		$root = $parser->create("dependencies");
		$parser->append($root);
		foreach ($dependencies as $library => $packages)
			foreach ($packages as $package)
			{
				$dep = $parser->create("dep");
				$parser->attr($dep, "lib", $library);
				$parser->attr($dep, "pkg", $package);
				$parser->append($root, $dep);
			}
		
		$parser->save(systemRoot.$historyFolder."/dependencies.xml");
		
		// Save code file
		fileManager::create(systemRoot.$historyFolder."/code.php", $code, TRUE);
		
		
		// Clear old history logs
		self::clearHistoryLog();
	}
	
	/**
	 * Clears the history log from entries that are one day old and more.
	 * 
	 * @return	void
	 */
	private static function clearHistoryLog()
	{
		// Get limit timestamp
		$time = time();
		$limit = $time - 60 * 60 * 24;
		
		// Get histories before limit
		$parser = new DOMParser();
		try
		{
			$parser->load(profileTester::getTrunk()."/console/index.xml");
		}
		catch (Exception $ex)
		{
			return;
		}
		
		$histories = $parser->evaluate("//hentry[@time<".$limit."]");
		foreach ($histories as $history)
		{
			// Get history id
			$history_id = $parser->attr($history, "id");
			
			// Remove entry
			$parser->replace($history, NULL);
			
			// Remove folder
			$historyFolder = self::getHistoryFolder($history_id);
			folderManager::remove(systemRoot.$historyFolder, "", TRUE);
		}
		
		// Update index
		$parser->update();
	}
	
	/**
	 * Get the history folder by id.
	 * 
	 * @param	string	$id
	 * 		The history entry id.
	 * 
	 * @return	string
	 * 		The history folder inside the tester's trunk.
	 */
	private static function getHistoryFolder($id)
	{
		return profileTester::getTrunk()."/console/".$id.".ch/";
	}
	
	/**
	 * Adds the proper headers to the php code to be enabled to run.
	 * 
	 * @param	string	$code
	 * 		The user's php code.
	 * 
	 * @return	string
	 * 		The new code with the headers.
	 */
	private static function addHeaders($code)
	{
		// Get headers
		$headers = "";
		$headers .= 'require_once($_SERVER["DOCUMENT_ROOT"]."/_domainConfig.php");'."\n";
		$headers .= "use \API\Platform\importer;\n";

		return trim($headers."\n".$code);
	}
	
	/**
	 * Adds redback sdk dependencies in the code.
	 * 
	 * @param	string	$code
	 * 		The php code to run.
	 * 
	 * @param	array	$dependencies
	 * 		An array of dependencies.
	 * 		It supports only packages for now.
	 * 
	 * @return	string
	 * 		The new code with the dependencies.
	 */
	private static function addSystemDependencies($code, $dependencies = array())
	{
		// Check empty
		if (empty($dependencies))
			return $code;
		
		// Add dependency lines
		$depCode = "";
		foreach ($dependencies as $library => $packages)
			foreach ($packages as $package)
			$depCode .= "importer::import('$library', '$package');\n";
		
		// Get code together and return
		return trim($depCode."\n".$code);
	}
}
//#section_end#
?>