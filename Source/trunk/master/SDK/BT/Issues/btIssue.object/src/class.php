<?php
//#section#[header]
// Namespace
namespace BT\Issues;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
class btIssue
{
	/**
	 * Severity identification : Feature request
	 * 
	 * @type	string
	 */
	const SV_FEATURE = 1;
	/**
	 * Severity identification : Minor bug
	 * 
	 * @type	string
	 */
	const SV_MINOR = 2;
	/**
	 * Severity identification : Major bug
	 * 
	 * @type	string
	 */
	const SV_MAJOR = 3;
	/**
	 * Severity identification : Critical bug
	 * 
	 * @type	string
	 */
	const SV_CRITICAL = 4; 
	
	/**
	 * Priority identification : high
	 * 
	 * @type	string
	 */
	const PR_HIGH = 5;
	/**
	 * Priority identification : normal
	 * 
	 * @type	string
	 */
	const PR_NORMAL = 3;
	/**
	 * Priority identification : low
	 * 
	 * @type	string
	 */
	const PR_LOW = 2;

	/**
	 * The project id
	 * 
	 * @type	string
	 */
	private $projectID;
	
	// Constructor Method
	public function __construct($projectID, $issueID = "")
	{
		// Initialize variables
		$this->projectID = $projectID;
		$this->issueID = $issueID;
	}
	
	/**
	 * Given the priority code returns the priority user friendly name
	 * 
	 * @param	integer	$priority
	 * 		The priority code as it is defined by classes const values
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getPriorityName($priority)
	{
		$array = self::getPriorityOptions();
		return $array[$priority];
	}
	
	/**
	 * Get all available priority options
	 * 
	 * @return	array
	 * 		An associative array code => frindly name
	 */
	public static function getPriorityOptions()
	{
		$priority = array();  
		$priority[self::PR_HIGH] = 'high';
		$priority[self::PR_NORMAL] = 'normal';
		$priority[self::PR_LOW] = 'low';	
		return $priority;
	}
	
	/**
	 * Given the severity code returns the severity user friendly name
	 * 
	 * @param	integer	$severity
	 * 		The severity code as it is defined by classes const values
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getSeverityName($severity)
	{
		$array = self::getSeverityOptions();
		return $array[$severity];
	}
	
	/**
	 * Get all available severityoptions
	 * 
	 * @return	array
	 * 		An associative array code => frindly name
	 */
	public static function getSeverityOptions()
	{
		$severity = array(); 
		$severity[self::SV_FEATURE] = 'feature'; 
		$severity[self::SV_MINOR] = 'minor';
		$severity[self::SV_MAJOR] = 'major';
		$severity[self::SV_CRITICAL] = 'critical'; 	
		return $severity;
	}
}
//#section_end#
?>