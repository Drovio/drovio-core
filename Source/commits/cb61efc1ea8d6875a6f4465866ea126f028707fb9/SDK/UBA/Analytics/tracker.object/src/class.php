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
importer::import("API", "Model", "sql/dbQuery");
importer::import("UBA", "Comm", "dbConnection");

use \API\Model\sql\dbQuery;
use \UBA\Comm\dbConnection;

class tracker
{
	// Constructor Method
	public function __construct()
	{
		// Put your constructor method code here.
	}
	
		const DB_PREFIX = "drovio.identity";
	
	/**
	 * Setup an identity database for the given team name.
	 * 
	 * @param	string	$teamName
	 * 		The team name to setup the database for.
	 * 
	 * @param	string	$password
	 * 		The password for the demo account.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setup($teamName, $password)
	{
		// Normalize team name
		$teamName = strtolower($teamName);
		
		// Setup database
		$dbc = new dbConnection(NULL);
		$q = new dbQuery("2132704406083", "identity.setup");
		$attr = array();
		$attr['db_name'] = self::DB_PREFIX.".".$teamName;
		$result = $dbc->execute($q, $attr);
		if (!$result)
			return FALSE;
		
		// Create demo account
		return account::getInstance($teamName)->create($email = "demo@identity.drov.io", $firstname = "Demo", $lastname = "Account", $password);
	}
	
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
	
	private static function performQuery($queryId, $attributes = array(), $teamName = "drovio") {
		// Normalize team name ---- Maybe get Team name?
		$teamName = strtolower($teamName); 
		
		// Setup database
		$dbc = new dbConnection($teamName);

		// Prepare query
		$q = new dbQuery($queryId, "uba.analytics.tracker");
		$result = $dbc->execute($q, $attributes);
		if (!$result)
			return FALSE;
		return $result;
	}
	
	public static function createDetailedEvent($event_name, $timestamp, $user_id, $session_id, $attrsParameter) {
		// Normalize team name ---- Maybe get Team name?
		$teamName = "drovio"; 
		$attrs = self::prepareAttributes($event_name, $timestamp, $user_id, $session_id, $attrsParameter, 'detailed');
		return self::performQuery("19750021011368", $attrs, $teamName);
	}
	
	public static function incrementCounter($event_name, $timestamp, $user_id, $session_id, $attrsParameter) {
		// TODO: get team name.
		$teamName = "drovio"; 
		$attrs = self::prepareAttributes($event_name, $timestamp, $user_id, $session_id, $attrsParameter, 'aggregate');
		print_r($attrs);
		return self::performQuery("15956335295961", $attrs, $teamName);
	}
	
	/* 1 Dec:
	1. Generalize the add function -> x, created a general set attributes. but might give it another go.
	2. Create get record function -> x,, no longer necessary, using update instead
	3. Create an increment counter function -> v
	4. retry generalising the add function -> v
	5. check what happens when update is called on non existent row - smbpl
	6. Write documentation
	7. Commit
	*/
}
//#section_end#
?>