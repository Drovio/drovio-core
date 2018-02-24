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

importer::import("ESS", "Environment", "cookies");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("DEV", "Projects", "projectLibrary");

use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \DEV\Projects\projectLibrary;

/**
 * Application Session Manager
 * 
 * Manages the application session, in both client side by set / get the respective cookies and in server side by handling the session entries stored in database.
 * 
 * @version	3.0-1
 * @created	March 2, 2015, 21:20 (EET)
 * @updated	May 8, 2015, 10:32 (EEST)
 */
class appSessionManager
{
	/**
	 * The database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The current user's unique session id.
	 * 
	 * @type	string
	 */
	private $userSessionID;
	
	/**
	 * The current user's application session.
	 * 
	 * @type	array
	 */
	private static $userSession;
	
	/**
	 * Constructs the object and sets the respective appSession Cookie.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize properties
		$this->dbc = new dbConnection();
		
		// Get guest's cookie value
		$guestUserSessionID = cookies::get('__apcguid');
		
		// Check if there is a logged in account
		if (account::validate())
		{
			// Create the uid according to user account id
			$accountID = account::getAccountID();
			$this->userSessionID = hash('md5', "app_session_".$accountID);
			
			// Set / renew account session cookie
			cookies::set("__apcuid", $this->userSessionID, 7 * 24 * 60 * 60, TRUE);
			
			// check for app_guest_cookie presence
			if (!empty($guestUserSessionID))
			{
				// Delete possible quest
				cookies::remove('__apcguid');
				
				// Delete possible db entry for quest
				$this->removeDbEntry($guestUserSessionID);
			}
		}
		else
		{
			// Get or Create guest session id
			if (empty($guestUserSessionID))
				$this->userSessionID = hash('md5', "app_session_".microtime()."_".mt_rand());
			else
				$this->userSessionID = $guestUserSessionID;
			
			// Set / renew guest session cookie
			cookies::set("__apcguid", $this->userSessionID, 7 * 24 * 60 * 60, TRUE);
		}
	}
	
	/**
	 * Sets the application version for the current user session.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The application version.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setVersion($applicationID, $version)
	{
		// Get query
		$dbq = new dbQuery("18925875172694", "apps.session");
		
		// Get current account id
		$accountID = account::getAccountID();
		$accountID = (empty($accountID) ? "NULL" : $accountID);
	
		$attr = array();
		$attr['uid'] = $this->userSessionID;
		$attr['application_id'] = $applicationID; 
		$attr['version'] = $version; 
		$attr['account_id'] = $accountID;
		$attr['time_created'] = time();
		$attr['time_updated'] = time();
		return $this->dbc->execute($dbq, $attr);
		
	}
	
	/**
	 * Get application information from the current session.
	 * If there is no session for the requested application, one is created.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	array
	 * 		Application information including version.
	 */
	private function info($applicationID)
	{
		// Check cache
		if (!isset(self::$userSession[$applicationID]))
		{
			// Get query
			$dbq = new dbQuery("2413142951952", "apps.session");
			
			// Gather attributes and execute
			$attr = array();
			$attr['uid'] = $this->userSessionID;
			$result = $this->dbc->execute($dbq, $attr);
			while ($appInfo = $this->dbc->fetch($result))
				self::$userSession[$appInfo['application_id']] = $appInfo;
		}
		
		// Return application information
		return self::$userSession[$applicationID];
	}
	
	/**
	 * Get the current session application version for the given application id.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application version.
	 */
	public function getVersion($applicationID)
	{
		// Get application info
		$info = $this->info($applicationID);
		if (empty($info))
		{
			// Get application last version
			$version = projectLibrary::getLastProjectVersion($applicationID);
			
			// Set session application version
			$this->setVersion($applicationID, $version);
		}
		else
			$version = $info['version'];
		
		// Return version
		return $version;
	}
	
	/**
	 * Get application session statistics including guest and registered users session count.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id to get statistics for.
	 * 
	 * @return	array
	 * 		An array of 'guests' and 'users' session count.
	 */
	public function getApplicationStats($applicationID)
	{
		// Get query
		$dbq = new dbQuery("29596258871675", "apps.session");
		
		// Gather attributes and execute
		$attr = array();
		$attr['app_id'] = $applicationID;
		$result = $this->dbc->execute($dbq, $attr);
		return $this->dbc->fetch($result);
	}

	/**
	 * Removes the database entry for a given application id for the current session.
	 * 
	 * @param	string	$userSessionID
	 * 		The user session id.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function removeDbEntry($userSessionID, $appID = "")
	{	
		// Initialize attributes
		$attr = array();
		
		// Get query
		if (!empty($appID))
		{
			$dbq = new dbQuery("32325815532962", "apps.session");			
			$attr['application_id'] = $appID;
		}
		else
			$dbq = new dbQuery("16373581261513", "apps.session");
		
		// Add user session id and execute
		$attr['uid'] = $userSessionID;
		return $this->dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>