<?php
//#section#[header]
// Namespace
namespace API\Model\apps;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Model
 * @namespace	\apps
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("ESS", "Environment", "cookies");
importer::import("SYS", "Comm", "db/dbConnection");

use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;

/**
 * Application Session Manager
 * 
 * Manages the application session, in both client side by set / get the respective cookies and in server side by handling the session entries stored in database.
 * 
 * @version	1.2-1
 * @created	March 2, 2015, 21:20 (EET)
 * @updated	March 11, 2015, 20:36 (EET)
 */
class appSessionManager
{
	/**
	 * The application session unique id
	 * 
	 * @type	string
	 */
	public static $appSessId;
	
	/**
	 * Constructs the object and sets the respective appSession Cookie.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$asug = cookies::get('asug');
		// Check if there is a logged in account
		if(account::validate())
		{
			// Create the uid according to user account id
			$accId = account::getAccountID();
			self::$appSessId = hash('md5', $accId);
			
			// Set or renew the cookie
			cookies::set("asuu", self::$appSessId, 0, TRUE);
			
			//check for app_guest_cookie presence
			if(!is_null($asug))
			{
				// Delete possible quest
				cookies::remove('asug');
				
				// Delete possible db entry for quest
				removeDbEntry($asug);
			}
		}
		else
		{
			//check for app_guest_cookie presence
			if(is_null($asug))
				self::$appSessId = hash('md5', mt_rand());
			else
				self::$appSessId = $asug;
			
			// Set / Renew
			cookies::set("asug", self::$appSessId, 0, TRUE);
		}
	}
	
	/**
	 * Creates or updates the database entry corresponding the created uid.
	 * 
	 * @param	string	$appID
	 * 		The application id
	 * 
	 * @param	string	$version
	 * 		The application version
	 * 
	 * @return	void
	 */
	public function setVersion($appID, $version)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Get query
		$dbq = new dbQuery("18925875172694", "apps.session");
	
		$attr = array();
		$attr['uid'] = self::$appSessId;
		$attr['application_id'] = $appID; 
		$attr['version'] = $version; 
		$attr['account_id'] = account::getAccountID();
		$attr['time_created'] = time();
		$result = $dbc->execute($dbq, $attr);		
		
		return $result;
		
	}
	
	/**
	 * Loads and return the information stored in the database for the particular appSession cookie.
	 * 
	 * @param	integer	$appID
	 * 		The application id
	 * 
	 * @return	array
	 * 		{description}
	 */
	private function info($appID)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Get query
		$dbq = new dbQuery("2413142951952", "apps.session");
	
		$attr = array();
		$attr['uid'] = self::$appSessId;
		$attr['application_id'] = $appID;
		$result = $dbc->execute($dbq, $attr);		
		
		$appInfo = $dbc->fetch($result);
		if ($appInfo)
			return $appInfo;
		
		return NULL;
	}
	
	/**
	 * Gets the version (from the database) matching the given application id
	 * 
	 * @param	string	$appID
	 * 		The application id
	 * 
	 * @return	void
	 */
	public function getVersion($appID)
	{
		$info = $this->info($appID);
		
		return $info['version'];

	}

	/**
	 * Removes the database application session entry for the given application
	 * 
	 * @param	string	$uid
	 * 		The application session unique id
	 * 
	 * @param	integer	$appID
	 * 		The application id
	 * 
	 * @return	bolean
	 * 		True on Success, False elsewher
	 */
	private function removeDbEntry($uid, $appID = '')
	{	
		// Initialize database connection
		$dbc = new dbConnection();
		
		$attr = array();
		// Get query
		if(empty($appID))
		{
			$dbq = new dbQuery("32325815532962", "apps.session");			
			$attr['application_id'] = $appID;
		}
		else
		{
			$dbq = new dbQuery("16373581261513", "apps.session");
		}
		
		$attr['uid'] = self::$appSessId;
		$result = $dbc->execute($dbq, $attr);		
				
		return $result;
	}
}
//#section_end#
?>