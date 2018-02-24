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
 * @version	6.0-1
 * @created	March 2, 2015, 21:20 (EET)
 * @updated	June 11, 2015, 11:55 (EEST)
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
	 * The guest's unique session id.
	 * 
	 * @type	string
	 */
	private $guestUserSessionID;
	
	/**
	 * The current user's application session.
	 * 
	 * @type	array
	 */
	private static $userSession;
	
	/**
	 * The singleton instance.
	 * 
	 * @type	appSessionManager
	 */
	private static $instance;
	
	/**
	 * Constructs the object and sets the respective appSession Cookie.
	 * 
	 * @return	void
	 */
	private function __construct()
	{
		// Initialize properties
		$this->dbc = new dbConnection();
		
		// Get guest's session id
		$guestUserSessionID = $this->guestUserSessionID;
		if (empty($guestUserSessionID))
			$guestUserSessionID = cookies::get('__apcguid');
		
		// Check if there is a logged in account
		if (account::validate())
		{
			// Create the uid according to user account id
			$accountID = account::getAccountID();
			$this->userSessionID = hash('md5', "app_session_".$accountID);
			
			// Set / renew account session cookie
			cookies::set("__apcuid", $this->userSessionID, 7 * 24 * 60 * 60, TRUE);
			
			// Remove guest user session id (if any)
			if (!empty($guestUserSessionID))
			{
				// Delete possible quest
				cookies::remove('__apcguid');
				
				// Delete possible db entry for quest
				$this->removeDbEntry($guestUserSessionID);
				
				// Unset variables
				$guestUserSessionID = NULL;
				$this->guestUserSessionID = NULL;
			}
		}
		else
		{
			// Get or Create guest session id
			if (empty($guestUserSessionID))
				$this->guestUserSessionID = hash('md5', "app_session_".microtime()."_".mt_rand());
			else
				$this->guestUserSessionID = $guestUserSessionID;
			
			// Set / renew guest session cookie
			$this->userSessionID = $this->guestUserSessionID;
			cookies::set("__apcguid", $this->guestUserSessionID, 7 * 24 * 60 * 60, TRUE);
		}
	}
	
	/**
	 * Gets the singleton instance of this object.
	 * 
	 * @return	appSessionManager
	 * 		The appSessionManager instance.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new appSessionManager();
		
		return self::$instance;
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
		$attr['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$result = $this->dbc->execute($dbq, $attr);
		if ($result)
		{
			// Force re-fetch application versions
			unset(self::$userSession[$applicationID]);
		}
		
		return $result;
		
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
	 * Get application's last approved version.
	 * This can be used for comparison with the user's version for update.
	 * 
	 * @param	{type}	$applicationID
	 * 		{description}
	 * 
	 * @return	string
	 * 		The last application's approved version.
	 */
	public function getLastVersion($applicationID)
	{
		// Get application last version
		return projectLibrary::getLastProjectVersion($applicationID);
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
		// Set query attributes
		$attr = array();
		$attr['app_id'] = $applicationID;
		$attr['expire_days'] = (7 * 24 * 60 * 60);
		
		// Get generic statistics
		$dbq = new dbQuery("29596258871675", "apps.session");
		$result = $this->dbc->execute($dbq, $attr);
		return $this->dbc->fetch($result);
	}
	
	/**
	 * Get detailed application session statistics.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id to get statistics for.
	 * 
	 * @return	array
	 * 		An array of all application active sessions.
	 */
	public function getApplicationStatsDetails($applicationID)
	{
		// Set query attributes
		$attr = array();
		$attr['app_id'] = $applicationID;
		$attr['expire_days'] = (7 * 24 * 60 * 60);
		
		// Get detailed statistics
		$dbq = new dbQuery("34970244836743", "apps.session");
		$result = $this->dbc->execute($dbq, $attr);
		return $this->dbc->fetch($result, TRUE);
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