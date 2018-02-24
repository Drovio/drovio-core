<?php
//#section#[header]
// Namespace
namespace UBA\Analytics;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UBA
 * @package	Analytics
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Model", "sql/dbQuery");
importer::import("UBA", "Comm", "dbConnection");

use \API\Model\sql\dbQuery;
use \UBA\Comm\dbConnection;

/**
 * Tracker
 * 
 * Object that handles recording tracked events into the database.
 * 
 * @version	1.0-1
 * @created	December 1, 2015, 18:12 (GMT)
 * @updated	December 1, 2015, 19:25 (GMT)
 */
class tracker
{
	/**
	 * 15 minutes in milliseconds
	 * 
	 * @type	integer
	 */
	const TIME_SPAN = 900000;
	
	/**
	 * The team name
	 * 
	 * @type	string
	 */
	private $teamName = "";
	
	/**
	 * Connection to the tracker database.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	private static $instances = array();
	
	
	/**
	 * Get a tracker instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name, required for the tracker database.
	 * 
	 * @return	tracker
	 * 		The tracker instance
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instances[$teamName]))
			self::$instances[$teamName] = new tracker($teamName);
		
		// Return instance
		return self::$instances[$teamName];
	}
	
	/**
	 * Creates a tracker object.
	 * 
	 * @param	string	$teamName
	 * 		The team name required for the tracker database.
	 * 
	 * @return	void
	 */
	protected function __construct($teamName)
	{
		$this->teamName = $teamName;
		$this->dbc = new dbConnection($this->teamName);
	}
	
	/**
	 * Return all the row in the events_detailed table or False if query fails.
	 * 
	 * @return	mixed
	 * 		All rows in events_detailed on success or False on failure.
	 */
	public function getEverything() {
		
		// Setup database
		$q = new dbQuery("17175415668668", "uba.analytics.tracker");
		$result = $this->dbc->execute($q, array());
		if (!$result)
			return FALSE;
		return $result;
		
	}
	
	/**
	 * Prepares the attributes for recording detailed or aggregated events.
	 * 
	 * @param	string	$event_name
	 * 		The event name.
	 * 
	 * @param	integer	$timestamp
	 * 		The current timestamp.
	 * 
	 * @param	integer	$user_id
	 * 		The user id.
	 * 
	 * @param	integer	$session_id
	 * 		The session id.
	 * 
	 * @param	array	$attrsParameter
	 * 		All parameters apart from the keys should be set in this array. The keys expected here are: {'event_name', 'user_id', 'session_id', 'city', 'region', 'country', 'browser', 'browser_version', 'device', 'current_url', 'initial_referrer', 'initial_referrer_domain', 'operating_system', 'referrer', 'referring_domain', 'screen_height', 'screen_width', 'utm_parameters'}.
	 * 
	 * @param	string	$recordType
	 * 		"detailed" or "aggregated".
	 * 
	 * @return	array
	 * 		array()
	 */
	private static function prepareAttributes_old($event_name, $timestamp, $user_id, $session_id, $attrsParameter, $recordType) {
		$attrs = array();
		$attrs['event_name'] = $event_name;
		$attrs['user_id'] = $user_id;
		$attrs['session_id'] = $session_id;
		$attrs['city'] = 'NULL';
		$attrs['region'] = 'NULL';
		$attrs['country'] = 'NULL';
		$attrs['browser'] = 'NULL';
		$attrs['browser_version'] = 'NULL';
		$attrs['device'] = 'NULL';
		$attrs['current_url'] = 'NULL';
		$attrs['initial_referrer'] = 'NULL';
		$attrs['initial_referrer_domain'] = 'NULL';
		$attrs['operating_system'] = 'NULL';
		$attrs['referrer'] = 'NULL';
		$attrs['referring_domain'] = 'NULL';
		$attrs['screen_height'] = 'NULL';
		$attrs['screen_width'] = 'NULL';
		$attrs['utm_parameters'] = 'NULL';
		if ($recordType == 'detailed') {
			$attrs['timestamp'] = $timestamp;
		} else if ($recordType == "aggregate") {
			$attrs['timespan'] = $timestamp - $timestamp % self::TIME_SPAN; // 900000ms == 15min
		}
		foreach(array_keys($attrsParameter) as $key) {
			$attrs[$key] = $attrsParameter[$key];
		}
		return $attrs;
	}
	
	private static function prepareAttributes($queryType, $givenAttrs) {
		$addUserAttrs = array('joindate', 'intial_platform', 'initial_device_type', 'initial_country', 'initial_region', 'initial_city', 'platform', 'intial_referrer', 'initial_browser', 'intial_landing_page', 'initial_device', 'initial_carrier');
		switch ($queryType) {
			case "addNewUser" :
				$requiredAttrs = $addUSerAttrs;
				break;
			default :
				return FALSE;
		}
		$attrs = array();
		foreach ($requiredAttrs as $key) {
			$attrs[$key] = 'NULL';
		}
		foreach(array_keys($givenAttrs) as $key) {
			$attrs[$key] = $givenAttrs[$key];
		}
		return $attrs;
	}
	
	/**
	 * Performs the query given and return the result or False for failure.
	 * 
	 * @param	string	$queryId
	 * 		The id of the tracker query. Note that all queries must be from \UBA\Analytics\tracker.
	 * 
	 * @param	array	$attrs
	 * 		All the required information the must be added to the query.
	 * 
	 * @return	mixed
	 * 		False on fail and the result otherwise.
	 */
	private static function performQuery($queryId, $attrs = array()) {
		// Prepare query
		$q = new dbQuery($queryId, "uba.analytics.tracker");
		$result = $this->$dbc->execute($q, $attrs);
		if (!$result)
			return FALSE;
		return $result;
	}
	
	// TO REMOVE
	/**
	 * Records an event in the tracker database and updates the aggregated records.
	 * 
	 * @param	string	$event_name
	 * 		The name of the event.
	 * 
	 * @param	integer	$user_id
	 * 		The user id.
	 * 
	 * @param	integer	$session_id
	 * 		The session id.
	 * 
	 * @param	array	$attrsParameter
	 * 		All the other information apart from the primary key that must be added to the query.
	 * 
	 * @param	integer	$timestamp
	 * 		If no value is given the current timestamp is will be set.
	 * 
	 * @return	void
	 */
	public function track($event_name, $user_id, $session_id, $attrsParameter, $timestamp = NULL) {
		if ($timestamp == NULL) {
			$timestamp = time();
		}
		// Add individual event record.
		$attrs = $this->prepareAttributes($event_name, $timestamp, $user_id, $session_id, $attrsParameter, 'detailed');
		$individualResponse  = $this->performQuery("31020172492374", $attrs);
		// Update aggregated record
		$attrs = $this->prepareAttributes($event_name, $timestamp, $user_id, $session_id, $attrsParameter, 'aggregate');
		$aggregateResponse = $this->performQuery("15956335295961", $attrs);
	}
	
	public function addNewUser($info = array(), $joindate = NULL) {
		
	}
}
//#section_end#
?>