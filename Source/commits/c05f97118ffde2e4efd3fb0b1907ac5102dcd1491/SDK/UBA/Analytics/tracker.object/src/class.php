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
 * @version	0.1-1
 * @created	December 1, 2015, 18:12 (GMT)
 * @updated	December 1, 2015, 18:12 (GMT)
 */
class tracker
{
	/**
	 * Creates a tracker object.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Put your constructor method code here.
	}
	
	/**
	 * Return all the row in the events_detailed table or False if query fails.
	 * 
	 * @return	mixed
	 * 		All rows in events_detailed on success or False on failure.
	 */
	public static function getEverything() {
			
		// Normalize team name
		$teamName = strtolower($teamName);
		
		// Setup database
		$dbc = new dbConnection("drovio");
		$q = new dbQuery("17175415668668", "uba.analytics.tracker");
		$result = $dbc->execute($q, array());
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
	private static function prepareAttributes($event_name, $timestamp, $user_id, $session_id, $attrsParameter, $recordType) {
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
			$attrs['timespan'] = $timestamp - $timestamp % 900000; // 900000ms == 15min
		}
		foreach(array_keys($attrsParameter) as $key) {
			$attrs[$key] = $attrsParameter[$key];
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
	 * @param	string	$teamName
	 * 		The team name.
	 * 
	 * @return	mixed
	 * 		False on fail and the result otherwise.
	 */
	private static function performQuery($queryId, $attrs = array(), $teamName = "drovio") {
		// Normalize team name ---- Maybe get Team name?
		$teamName = strtolower($teamName); 
		
		// Setup database
		$dbc = new dbConnection($teamName);

		// Prepare query
		$q = new dbQuery($queryId, "uba.analytics.tracker");
		$result = $dbc->execute($q, $attrs);
		if (!$result)
			return FALSE;
		return $result;
	}
	
	/**
	 * Adds a new record in the event_detailed table.
	 * 
	 * @param	string	$event_name
	 * 		Name of the event.
	 * 
	 * @param	integer	$timestamp
	 * 		Timestamp for the event.
	 * 
	 * @param	integer	$user_id
	 * 		The user id.
	 * 
	 * @param	integer	$session_id
	 * 		The session id.
	 * 
	 * @param	array	$attrsParameter
	 * 		Array the should include all other information that is not part of the primary key. The keys expected here are: {'event_name', 'user_id', 'session_id', 'city', 'region', 'country', 'browser', 'browser_version', 'device', 'current_url', 'initial_referrer', 'initial_referrer_domain', 'operating_system', 'referrer', 'referring_domain', 'screen_height', 'screen_width', 'utm_parameters'}.
	 * 
	 * @return	boolean
	 * 		True on success and False otherwise.
	 */
	public static function createDetailedEvent($event_name, $timestamp, $user_id, $session_id, $attrsParameter) {
		// Normalize team name ---- Maybe get Team name?
		$teamName = "drovio"; 
		$attrs = self::prepareAttributes($event_name, $timestamp, $user_id, $session_id, $attrsParameter, 'detailed');
		return self::performQuery("31020172492374", $attrs, $teamName);
	}
	
	/**
	 * Increments the counter for the selected event. A record is created if one doesn't already exist.
	 * 
	 * @param	string	$event_name
	 * 		The name of the event.
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
	 * 		The parameters to set with the record apart from the ones part of the primary key.
	 * 
	 * @return	boolean
	 * 		True on success and False on failure.
	 */
	public static function incrementCounterForAggreggatedEvent($event_name, $timestamp, $user_id, $session_id, $attrsParameter) {
		// TODO: get team name.
		$teamName = "drovio"; 
		$attrs = self::prepareAttributes($event_name, $timestamp, $user_id, $session_id, $attrsParameter, 'aggregate');
		print_r($attrs);
		return self::performQuery("15956335295961", $attrs, $teamName);
	}
}
//#section_end#
?>