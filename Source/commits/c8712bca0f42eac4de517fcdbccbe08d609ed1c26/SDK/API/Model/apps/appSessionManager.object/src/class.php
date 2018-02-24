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
 * @version	1.2-2
 * @created	March 2, 2015, 21:20 (EET)
 * @updated	March 12, 2015, 12:48 (EET)
 */
class appSessionManager
{
	/**
	 * The current user's unique session id.
	 * 
	 * @type	string
	 */
	private $userSessionID;
	
	/**
	 * Constructs the object and sets the respective appSession Cookie.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Get guest's cookie value
		$asug = cookies::get('asug');
		
		// Check if there is a logged in account
		if (account::validate())
		{
			// Create the uid according to user account id
			$accountID = account::getAccountID();
			$this->userSessionID = hash('md5', "app_session_".$accountID);
			
			// Set or renew the cookie
			cookies::set("asuu", $this->userSessionID, 7 * 24 * 60 * 60, TRUE);
			
			// check for app_guest_cookie presence
			if (!empty($asug))
			{
				// Delete possible quest
				cookies::remove('asug');
				
				// Delete possible db entry for quest
				$this->removeDbEntry($asug);
			}
		}
		else
		{
			//check for app_guest_cookie presence
			if (empty($asug))
				$this->userSessionID = hash('md5', microtime()."_".mt_rand());
			else
				$this->userSessionID = $asug;
			
			// Set / Renew guest cookie
			cookies::set("asug", $this->userSessionID, 0, TRUE);
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
		$attr['uid'] = $this->userSessionID;
		$attr['application_id'] = $appID; 
		$attr['version'] = $version; 
		$attr['account_id'] = account::getAccountID();
		$attr['time_created'] = time();
		$attr['time_updated'] = time();
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
		// Initialize database connection and get query
		$dbc = new dbConnection();
		$dbq = new dbQuery("2413142951952", "apps.session");
	
		$attr = array();
		$attr['uid'] = $this->userSessionID;
		$attr['application_id'] = $appID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result);
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
	private function removeDbEntry($uid, $appID = "")
	{	
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Get query
		$attr = array();
		if (!empty($appID))
		{
			$dbq = new dbQuery("32325815532962", "apps.session");			
			$attr['application_id'] = $appID;
		}
		else
			$dbq = new dbQuery("16373581261513", "apps.session");
		
		// Add session id and execute the query
		$attr['uid'] = $this->userSessionID;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>