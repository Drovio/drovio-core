<?php
//#section#[header]
// Namespace
namespace API\Login;

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
 * @package	Login
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Login", "account");
importer::import("DRVC", "Profile", "accountSession");

use \API\Login\account;
use \DRVC\Profile\accountSession as IDAccountSession;

/**
 * Login Account Session Handler
 * 
 * It extends the drovio identity account session to manage sessions for the drovio platform.
 * 
 * @version	1.0-1
 * @created	November 11, 2015, 18:52 (GMT)
 * @updated	November 11, 2015, 20:19 (GMT)
 */
class accountSession extends IDAccountSession
{
	/**
	 * The platform accountSession instance.
	 * 
	 * @type	accountSession
	 */
	private static $instance;
	
	/**
	 * Get the accountSession instance.
	 * 
	 * @param	string	$mxID
	 * 		The account's mx id (if any).
	 * 		It is empty by default.
	 * 
	 * @return	accountSession
	 * 		The accountSession instance.
	 */
	public static function getInstance($mxID = "")
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new accountSession($mxID);
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new accountSession instance for the platform identity.
	 * 
	 * @param	string	$mxID
	 * 		The account's mx id.
	 * 
	 * @return	void
	 */
	protected function __construct($mxID = "")
	{
		parent::__construct(account::ID_TEAM_NAME, $mxID);
	}
	
	/**
	 * Gets the current mx id.
	 * 
	 * @return	string
	 * 		The current mx id.
	 */
	public function getMX()
	{
		// Get parent mx
		$mx = parent::getMX();
		if (!empty($mx))
			return $mx;

		// Get value from cookies
		$mx = engine::getVar("__DRVID_MX");
		$mx = (empty($mx) ? engine::getVar("mx") : $mx);
		if ($mx == 'deleted')
			return NULL;
		
		return $mx;
	}
}
//#section_end#
?>