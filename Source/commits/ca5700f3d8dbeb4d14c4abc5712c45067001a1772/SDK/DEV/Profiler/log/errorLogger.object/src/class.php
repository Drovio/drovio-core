<?php
//#section#[header]
// Namespace
namespace DEV\Profiler\log;

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
 * @namespace	\log
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("DEV", "Profiler", "log::activityLogger");

use \ESS\Protocol\AsCoProtocol;
use \DEV\Profiler\log\activityLogger;

/**
 * System Error Logger
 * 
 * Logs all errors and exceptions of the system.
 * 
 * @version	0.1-1
 * @created	February 10, 2014, 12:52 (EET)
 * @revised	November 6, 2014, 13:02 (EET)
 */
class errorLogger extends activityLogger
{
	/**
	 * The logger run time.
	 * 
	 * @type	integer
	 */
	private $logTime;
	
	/**
	 * Logger constructor function.
	 * 
	 * @param	integer	$time
	 * 		The logger run time.
	 * 		It can be altered manually for reading older logs.
	 * 
	 * @return	void
	 */
	public function __construct($time = "")
	{
		$this->logTime = (empty($time) ? time() : $time);
	}
	
	/**
	 * Logs a given exception in the system.
	 * 
	 * @param	Exception	$ex
	 * 		The exception to log.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function log($ex)
	{
		// Set exception description
		$description = "Exception (".$ex->errCode.")\n";
		$description .= $ex->getMessage()."\n";
		$description .= $ex->getTraceAsString()."\n\n";
		
		// Get server data
		$description .= "Server Data\n";
		$description .= print_r($_SERVER, TRUE)."\n";
		
		// Set ascop data
		if (AsCoProtocol::exists())
		{
			$ascopData = array();
			$subdomain = AsCoProtocol::getSubdomain();
			$ascopData['subdomain'] = (empty($subdomain) ? "www" : $subdomain);
			$ascopData['path'] = AsCoProtocol::getPath();
			
			$description .= "AsCoP Data\n";
			$description .= print_r($ascopData, TRUE)."\n";
		}

		// Log activity
		return parent::log($description);
	}
	
	/**
	 * Gets the log file.
	 * 
	 * @return	string
	 * 		The log inner file path.
	 */
	protected function getLogFolder()
	{
		return "/errors/err_".date("Y-m-d", $this->logTime).".xml";
	}
}
//#section_end#
?>