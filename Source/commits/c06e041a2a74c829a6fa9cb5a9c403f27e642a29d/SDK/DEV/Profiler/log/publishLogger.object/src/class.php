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

importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("DEV", "Profiler", "log::activityLogger");

use \ESS\Protocol\server\AsCoProtocol;
use \DEV\Profiler\log\activityLogger;

/**
 * System Publisher Logger
 * 
 * Manages all logs for Redback deploy and publish.
 * 
 * @version	{empty}
 * @created	February 10, 2014, 13:29 (EET)
 * @revised	February 10, 2014, 13:29 (EET)
 */
class publishLogger extends activityLogger
{
	/**
	 * The publisher indicator.
	 * 
	 * @type	string
	 */
	const PUBLISH = "publish";
	
	/**
	 * The deploy indicator.
	 * 
	 * @type	string
	 */
	const DEPLOY = "deploy";
	
	/**
	 * The logger type.
	 * 
	 * @type	string
	 */
	private $logType;
	
	/**
	 * Logger constructor function.
	 * 
	 * @param	string	$type
	 * 		The logger type.
	 * 		Redback deploy or publish.
	 * 
	 * @return	void
	 */
	public function __construct($type = self::DEPLOY)
	{
		$this->logType = (empty($type) ? self::DEPLOY : $type);
	}
	
	/**
	 * Gets the log file.
	 * 
	 * @return	string
	 * 		The log inner file path.
	 */
	protected function getLogFile()
	{
		return "/".$this->logType.".xml";
	}
}
//#section_end#
?>