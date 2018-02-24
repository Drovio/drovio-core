<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\debug;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\debug
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "log::errorLogger");
importer::import("API", "Developer", "profiler::debugger");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTMLPageReport");

use \DEV\Profiler\log\errorLogger;
use \API\Developer\profiler\debugger;
use \UI\Html\DOM;
use \UI\Html\HTMLPageReport;

/**
 * Redback Runtime Exception
 * 
 * Creates a runtime exception and provides the user all the available information (according to debugger level) for reporting the error.
 * 
 * @version	{empty}
 * @created	October 15, 2013, 17:26 (EEST)
 * @revised	October 15, 2013, 17:28 (EEST)
 */
class RuntimeException extends Exception
{
	/**
	 * The exception's error code.
	 * 
	 * @type	string
	 */
	protected $errCode;
	
	/**
	 * Constructor method. Constructs the exception.
	 * 
	 * @param	string	$message
	 * 		The exception message.
	 * 
	 * @param	string	$errCode
	 * 		The exception error code.
	 * 
	 * @return	void
	 */
	public function __construct($message, $errCode = "")
	{
		// Construct Exception
		parent::__construct($message);
		
		// Set Exception debugger code
		$this->errCode = $errCode;
	}
	
	/**
	 * Gets the exception page report.
	 * 
	 * @param	Exception	$ex
	 * 		The exception thrown.
	 * 
	 * @return	string
	 * 		The exception page report html.
	 */
	public static function get($ex)
	{
		// Log the exception
		self::log($ex);
		
		// Clear all elements of DOM
		DOM::clear();
		
		// Create Module Report
		$report = new HTMLPageReport();
		
		// Create Exception Message
		$errorDescription = DOM::create("div");
		
		// Get Error Code
		if (isset($ex->errCode))
			$errCode = $ex->errCode;

		// Create Error Message
		$errorMessage = debugger::getErrorMessage($errCode);
		$errorMessageContainer = DOM::create("small", "", "", "errCode");
		DOM::append($errorDescription, $errorMessageContainer);
		$errorMessageDiv = DOM::create("b", $errorMessage);
		DOM::append($errorMessageContainer, $errorMessageDiv);
		
		// Debugger Message Code
		$dbgMessageCode = "dbg.exception";
		
		// Show exception message
		if (debugger::status())
		{
			// Exception Message
			$messageInner = DOM::create("small", " - ".$ex->getMessage(), "", "errMessage");
			DOM::append($errorDescription, $messageInner);
			
			// Exception Stack Trace
			// (Temp as string, tree view to be)
			$trace = DOM::create("pre", $ex->getTraceAsString());
			DOM::append($errorDescription, $trace);
			
			// Debugger Message Code
			$dbgMessageCode = "dbg.exception_full";
		}

		// Build the Report
		return $report->build("error", "debug", $dbgMessageCode, $errorDescription)->getReport();
	}
	
	/**
	 * Logs the exception to the system log.
	 * 
	 * @param	Exception	$ex
	 * 		The exception thrown.
	 * 
	 * @return	void
	 */
	private static function log($ex)
	{
		// Log error
		$erLogger = new errorLogger();
		$erLogger->log($ex);
	}
}
//#section_end#
?>