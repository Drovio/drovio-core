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

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "storage::session");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Resources", "paths");

use \API\Resources\filesystem\fileManager;
use \API\Resources\storage\session;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Resources\paths;

/**
 * Server Console Class
 * 
 * This class allows users of Redback and developers to run free code and get the results of the execution.
 * Simple users can just execute php code, while redback developers can have access to the SDK through the importer.
 * 
 * @version	0.1-1
 * @created	February 17, 2014, 12:05 (EET)
 * @revised	July 10, 2014, 17:56 (EEST)
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
	 * @return	string
	 * 		The php code output.
	 */
	public static function php($code, $headers = FALSE, $dependencies = array())
	{
		// Clear code and remove unsafe functions
		$code = phpParser::clear($code);
		$code = phpCoder::safe($code);

		// Add dependencies (if any) and headers
		if ($headers)
		{
			$code = self::addSystemDependencies($code, $dependencies);
			$code = self::addHeaders($code);
		}
		
		// Wrap code into php file
		$code = phpParser::wrap($code);
		
		// Save temporary file
		$file = md5(microtime()."+".rand()."+".session::getID()).".php";
		$tempFilePath = paths::getDevPath()."TestingTrunk/pool/".$file;
		fileManager::create(systemRoot.$tempFilePath, $code, TRUE);
		
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