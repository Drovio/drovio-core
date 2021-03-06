<?php
//#section#[header]
// Namespace
namespace API\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "session");
importer::import("ESS", "Environment", "url");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "person");
importer::import("API", "Platform", "engine");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\cookies;
use \ESS\Environment\session;
use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\person;
use \API\Platform\engine;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Resources\paths;

/**
 * Account Manager Class
 * 
 * Manages the active account.
 * 
 * @version	6.1-1
 * @created	July 5, 2013, 12:38 (EEST)
 * @updated	August 21, 2015, 15:58 (EEST)
 */
class account
{
	/**
	 * Indicates whether the account is logged in for this run.
	 * 
	 * @type	boolean
	 */
	private static $loggedIn = FALSE;
	
	/**
	 * The current account id.
	 * 
	 * @type	integer
	 */
	private static $accountID = NULL;
	
	/**
	 * The current person id (if any).
	 * 
	 * @type	integer
	 */
	private static $personID = NULL;
	
	/**
	 * The current session id.
	 * 
	 * @type	integer
	 */
	private static $sessionID = NULL;
	
	/**
	 * The current mx id.
	 * 
	 * @type	string
	 */
	private static $mxID = NULL;
	
	/**
	 * All the stored account data.
	 * 
	 * @type	array
	 */
	private static $accountData = array();
	
	/**
	 * The session salt.
	 * 
	 * @type	string
	 */
	private static $salt = "";
	
	/**
	 * Authenticates an account with the given username and password.
	 * 
	 * @param	string	$username
	 * 		The person's username.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @param	string	$accountID
	 * 		The account id to authenticate. If empty, get the current account id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function authenticate($username, $password, $accountID = "")
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("2064670715", "profile.account");
		
		// Authenticate account
		$attr['username'] = $username;
		$attr['password'] = hash("SHA256", $password);
		$result = $dbc->execute($q, $attr);

		// If account not found, return FALSE
		if ($dbc->get_num_rows($result) == 0)
			return FALSE;
		
		// Get account ID
		$accountID = (empty($accountID) ? self::getAccountID() : $accountID);
		
		// If account is found, return account ID
		$accountData = $dbc->fetch($result);
		return ($accountData['accountID'] == $accountID);
	}
	
	/**
	 * Authenticates an account and sets it as logged in.
	 * 
	 * @param	string	$username
	 * 		The person's username.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @param	boolean	$rememberme
	 * 		Whether to remember the user in the database or not.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function login($username, $password, $rememberme = FALSE)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("2064670715", "profile.account");
		
		// Authenticate account
		$attr['username'] = $username;
		$attr['password'] = hash("SHA256", $password);
		$result = $dbc->execute($q, $attr);

		// If user not found, return FALSE
		if ($dbc->get_num_rows($result) == 0)
			return FALSE;
		
		// Fetch Account
		$accountData = $dbc->fetch($result);
		
		// Get Account Info
		$locked = $accountData['locked'];
		$accountID = $accountData['accountID'];
		self::$accountID = $accountData['accountID'];
		self::$personID = NULL;
		if (!$locked)
			self::$personID = $accountData['personID'];
			
		// Create salt
		$salt = hash("md5", time());
		
		// Create Account Session
		self::createSession($accountID, $salt, self::$personID, $rememberme);
		
		// Update Session Account Data
		self::updateSessionData($accountData);
		
		return TRUE;
	}
	
	/**
	 * Validates if the user is logged in.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validate()
	{
		// Check loggedIn
		if (self::$loggedIn)
			return self::$loggedIn;

		// Get cookies
		self::$accountID = self::getAccountID();
		self::$mxID = self::getMX();
		self::$personID = self::getPersonID();

		// Check if the cookies exist
		if (empty(self::$accountID) || empty(self::$mxID))
		{
			self::logout();
			return FALSE;
		}

		// Get salt from account
		$salt = self::getSalt();

		// If salt is empty, return FALSE
		if (empty($salt))
		{
			self::logout();
			return FALSE;
		}
		
		// Check mix
		$genMix = self::getSaltedMix(self::getSessionID(), self::$accountID, $salt, self::$personID);
		if (!($genMix === self::$mxID))
		{
			self::logout();
			return FALSE;
		}
		
		// Update session date modified and reset cookies
		self::updateSessionData();
		
		// Return valid status
		self::$loggedIn = TRUE;
		return TRUE;
	}
	
	/**
	 * Creates a new account session.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$salt
	 * 		The logged in generated salt.
	 * 
	 * @param	integer	$personID
	 * 		The person id (if any).
	 * 
	 * @param	boolean	$rememberme
	 * 		Whether to remember the user in the database or not.
	 * 
	 * @return	void
	 */
	private static function createSession($accountID, $salt, $personID = "", $rememberme = FALSE)
	{
		// Create new Account Session
		$dbc = new dbConnection();
		$q = new dbQuery("1557484874", "profile.account");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['salt'] = $salt;
		$attr['ip'] = $_SERVER['REMOTE_ADDR'];
		$attr['date'] = time();
		$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		$attr['rememberme'] = ($rememberme ? 1 : 0);
		$result = $dbc->execute($q, $attr);
		
		// Database error
		if (!$result)
			return FALSE;
			
		// Get session id
		$accountSession = $dbc->fetch($result);
		self::$sessionID = $accountSession['id'];
		
		// Get Salted mix
		$mx = self::getSaltedMix(self::$sessionID, $accountID, $salt, $personID);
		self::$mxID = $mx;
		
		// Set Cookies (and duration for one month if rememberme)
		$duration = ($rememberme ? 30 * 24 * 60 * 60 : 0);
		cookies::set("acc", $accountID, $duration, TRUE);
		cookies::set("person", $personID, $duration, TRUE);
		cookies::set("mx", $mx, $duration, TRUE);
		
		// Set static logged in
		self::$loggedIn = TRUE;
	}
	
	/**
	 * Deletes a given account session.
	 * 
	 * @param	string	$sessionID
	 * 		The account session id to be deleted.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deleteActiveSession($sessionID)
	{
		$accountID = self::getAccountID();
		if (empty($accountID) || empty($sessionID))
			return TRUE;

		// Remove active session
		$dbc = new dbConnection();
		$q = new dbQuery("445420676", "profile.account");
		$attr = array();
		$attr['sid'] = $sessionID;
		$attr['aid'] = $accountID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Returns the salted mix of account, person and salt.
	 * 
	 * @param	string	$sessionID
	 * 		The account session id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$salt
	 * 		The account salt.
	 * 
	 * @param	integer	$personID
	 * 		The person id.
	 * 
	 * @return	string
	 * 		The salted mix to store to cookies.
	 */
	private static function getSaltedMix($sessionID, $accountID, $salt, $personID = "")
	{
		// Create account mix
		$mix = $accountID."_".$salt."_".$personID;
		
		// Create mix aray
		$mixArray = array();
		$mixArray[] = $sessionID;
		$mixArray[] = hash("md5", $mix);
		
		// Return mix
		return implode(":", $mixArray);
	}
	
	/**
	 * Gets the stored salt for the current account session.
	 * 
	 * @return	string
	 * 		The stored salt.
	 */
	private static function getSalt()
	{
		// Get salt from variable
		if (!empty(self::$salt))
			return self::$salt;
		
		// Get salt from account session
		$dbc = new dbConnection();
		$q = new dbQuery("554184391", "profile.account");
		$attr = array();
		$attr['sid'] = self::getSessionID();
		$attr['aid'] = self::getAccountID();
		$result = $dbc->execute($q, $attr);
		
		// Check if the session is valid and exists, otherwise, return empty salt
		if ($dbc->get_num_rows($result) == 0)
			self::$salt = "";
		else
		{
			$row = $dbc->fetch($result);
			self::$salt = $row['salt'];
		}
		
		// Return salt
		return self::$salt;
	}
	
	/**
	 * Logout the account from the system.
	 * 
	 * @return	void
	 */
	public static function logout()
	{
		// Remove Active Session
		$sessionID = self::getSessionID();
		self::deleteActiveSession($sessionID);
		
		// Set class variables to null
		self::$accountID = NULL;
		self::$personID = NULL;
		self::$sessionID = NULL;
		self::$mxID = NULL;
		self::$loggedIn = FALSE;
		
		// Delete all account cookies
		cookies::remove("acc");
		cookies::remove("person");
		cookies::remove("mx");
	}
	
	/**
	 * Switch from one account to another.
	 * 
	 * @param	integer	$accountID
	 * 		The new account id to switch to.
	 * 
	 * @param	string	$password
	 * 		The new account's password.
	 * 
	 * @return	boolean
	 * 		Returns TRUE on success and FALSE if the current account is locked and cannot switch.
	 */
	public static function switchAccount($accountID, $password)
	{
		// Check if it's a valid account
		if (!self::validate())
			return FALSE;
		
		// Check if current account is locked
		if (self::isLocked())
			return FALSE;
		
		// If is valid, and the account is not locked, switch account
		$username = self::getUsername(TRUE);
		if (self::authenticate($username, $password, $accountID))
		{
			self::logout();
			return self::login($username, $password);
		}
		
		return FALSE;
	}
	
	/**
	 * Update current account password.
	 * 
	 * @param	string	$currentPassword
	 * 		The current account password.
	 * 
	 * @param	string	$newPassword
	 * 		The new account password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updatePassword($currentPassword, $newPassword)
	{
		// Check if it's a valid account
		if (!self::validate())
			return FALSE;
		
		// Check if current account is locked
		if (self::isLocked())
			return FALSE;
			
		// If is valid, and the account is not locked, switch account
		$username = self::getUsername(TRUE);
		if (self::authenticate($username, $currentPassword))
		{
			// Update password in database
			$dbc = new dbConnection();
			$q = new dbQuery("3334000094646", "profile.account");
			$attr = array();
			$attr['aid'] = self::getAccountID();
			$attr['password'] = hash("SHA256", $newPassword);
			return $dbc->execute($q, $attr);
		}
		
		return FALSE;
	}
	
	/**
	 * Reset the account password using the reset id created by the reset form.
	 * 
	 * @param	string	$resetID
	 * 		The account reset password id.
	 * 
	 * @param	string	$newPassword
	 * 		The new account password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function resetPassword($resetID, $newPassword)
	{
		// Get account by reset password id
		$dbc = new dbConnection();
		$q = new dbQuery("24174176136971", "profile.account");
		$attr = array();
		$attr['reset'] = $resetID;
		$result = $dbc->execute($q, $attr);
		$accountInfo = $dbc->fetch($result);
			
		// If account is valid, update password
		if ($result && is_array($accountInfo))
		{
			// Update password in database
			$dbc = new dbConnection();
			$q = new dbQuery("3334000094646", "profile.account");
			$attr = array();
			$attr['aid'] = $accountInfo['id'];
			$attr['password'] = hash("SHA256", $newPassword);
			return $dbc->execute($q, $attr);
		}
		
		return FALSE;
	}
	
	/**
	 * Updates the account's data to the session.
	 * 
	 * @param	mixed	$accountData
	 * 		The account data.
	 * 		If not given, it is loaded from the database.
	 * 
	 * @return	void
	 */
	private static function updateSessionData($accountData = NULL)
	{
		// Get session data and check if cookies must be renewed.
		$sessionData = self::getSessionData();
		
		// Get session data
		$lastAccess = $sessionData['lastAccess'];
		$rememberme = $sessionData['rememberme'];
		
		// Get current time
		$currentTime = time();
		
		// Check if session needs to be renewed
		if ($rememberme && $currentTime - $lastAccess > 7 * 24 * 60 * 60)
		{
			// Update Cookies
			$accountID = engine::getVar("acc");
			$personID = engine::getVar("person");
			$mx = engine::getVar("mx");
			
			// Set Cookies again
			$duration = 30 * 24 * 60 * 60;
			cookies::set("acc", $accountID, $duration, TRUE);
			cookies::set("person", $personID, $duration, TRUE);
			cookies::set("mx", $mx, $duration, TRUE);
			
			// Update session in database
			$dbc = new dbConnection();
			$q = new dbQuery("786733301", "profile.account");
			$attr = array();
			$attr['aid'] = self::getAccountID();
			$attr['sid'] = self::getSessionID();
			$attr['ip'] = $_SERVER['REMOTE_ADDR'];
			$attr['date'] = $currentTime;
			$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			$attr['rememberme'] = 1;
			$dbc->execute($q, $attr);
		}
		
	}
	
	/**
	 * Gets whether the system remembers the logged in account.
	 * 
	 * @return	boolean
	 * 		True if the system remembers the account, false otherwise.
	 */
	public static function rememberme()
	{
		// Get session data
		$sessionData = self::getSessionData();
		
		// Return remember me value
		return $sessionData['rememberme'];
	}
	
	/**
	 * Get all current session data from the database for the current account.
	 * 
	 * @return	array
	 * 		An array of all session data.
	 */
	private static function getSessionData()
	{
		$dbc = new dbConnection();
		$q = new dbQuery("31610478464352", "profile.account");
		$attr = array();
		$attr['aid'] = self::getAccountID();
		$attr['sid'] = self::getSessionID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Gets the current logged in account id.
	 * 
	 * @return	integer
	 * 		The account id.
	 */
	public static function getAccountID()
	{
		if (isset(self::$accountID))
			return self::$accountID;

		$accID = engine::getVar("acc");
		if ($accID == 'deleted')
			return NULL;
		
		return $accID;
	}
	
	/**
	 * Gets the current mx id.
	 * 
	 * @return	string
	 * 		The current mx id.
	 */
	public static function getMX()
	{
		if (isset(self::$mxID))
			return self::$mxID;

		$mx = engine::getVar("mx");
		if ($mx == 'deleted')
			return NULL;
		
		return $mx;
	}
	
	/**
	 * Gets the account session id.
	 * 
	 * @return	integer
	 * 		The account session id.
	 */
	public static function getSessionID()
	{
		if (isset(self::$sessionID))
			return self::$sessionID;
			
		$mix = self::getMX();
		$mixArray = explode(":", $mix);
		return $mixArray[0];
	}
	
	/**
	 * Gets the person id of the logged in person.
	 * 
	 * @return	integer
	 * 		The person id.
	 */
	public static function getPersonID()
	{
		if (isset(self::$personID))
			return self::$personID;
		
		$person = engine::getVar("person");
		if ($person == 'deleted')
			return NULL;
		
		return $person;
	}
	
	/**
	 * Checks whether this account is locked.
	 * 
	 * @return	boolean
	 * 		True if locked, false otherwise.
	 */
	public static function isLocked()
	{
		return (self::getAccountValue("locked") == TRUE);
	}
	
	/**
	 * Gets the account's folder. The folder is created if doesn't exist.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the folder for.
	 * 		
	 * 		NOTICE: This doesn't work when in secure mode.
	 * 
	 * @return	mixed
	 * 		The account folder path.
	 * 		If there is no active account, it returns FALSE.
	 */
	public static function getAccountFolder($accountID = "")
	{
		// Get account id
		$accountID = (empty($accountID) || importer::secure() ? self::getAccountID() : $accountID);
		if (empty($accountID))
			return NULL;
		
		return self::getAccountFolderPath($accountID);
	}
	
	/**
	 * Get the account folder path for any account.
	 * 
	 * @param	integer	$accountID
	 * 		The account to get the folder path for.
	 * 
	 * @return	string
	 * 		The folder url path.
	 */
	private static function getAccountFolderPath($accountID)
	{
		if (empty($accountID))
			return NULL;
			
		$accFolderID = self::getFolderID("acc", $accountID, "account");
		$accountFolder = paths::getProfilePath()."/Accounts/".$accFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$accountFolder))
			folderManager::create(systemRoot.$accountFolder);
			
		return $accountFolder;
	}
	
	/**
	 * Get a service's folder inside the account root folder.
	 * 
	 * @param	string	$serviceName
	 * 		The service name.
	 * 
	 * @param	boolean	$systemAppData
	 * 		This indicates the service folder as System App and will be placed in a special folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public static function getServicesFolder($serviceName, $systemAppData = FALSE)
	{
		// Get account folder
		$accountFolder = self::getAccountFolder();
		if (empty($accountFolder))
			return NULL;
			
		// Get service folder
		$oldFolder = $accountFolder."/Services/".$serviceName."/";
		$newFolder = $accountFolder."/".($systemAppData ? "SystemAppData/" : "").$serviceName."/";
		
		// Create folder if not exists
		if ($newFolder && !file_exists(systemRoot.$newFolder))
			folderManager::create(systemRoot.$newFolder);
		
		// COMPATIBILITY - copy old folder to new
		if (file_exists(systemRoot.$oldFolder))
		{
			folderManager::copy(systemRoot.$oldFolder, systemRoot.$newFolder, $contents_only = TRUE);
			folderManager::remove(systemRoot.$oldFolder, $name = "", $recursive = TRUE);
		}
			
		return $newFolder;
	}
	
	/**
	 * Checks whether the account is admin.
	 * 
	 * @return	boolean
	 * 		True if admin, false otherwise (shared).
	 */
	public static function isAdmin()
	{
		return (self::getAccountValue("administrator") == TRUE);
	}
	
	/**
	 * Gets the account title for the logged in account.
	 * 
	 * @return	string
	 * 		The account display title.
	 */
	public static function getAccountTitle()
	{
		return self::getAccountValue("accountTitle");
	}
	
	/**
	 * Get the account's username.
	 * 
	 * @param	boolean	$emailFallback
	 * 		Set TRUE to return the connected person's email if the account's username is empty.
	 * 		It is FALSE by default.
	 * 
	 * @return	mixed
	 * 		Return the account username.
	 * 		If account doesn't have username, return the email of the person connected to this account.
	 * 		If there is no connected account, return NULL.
	 */
	public static function getUsername($emailFallback = FALSE)
	{
		// Get account username
		$username = self::getAccountValue("username");
		
		// If username is empty and email fallback is active,
		// get person's mail
		if (empty($username) && $emailFallback)
			$username = person::getMail();
		
		// Return username
		return $username;
	}
	
	/**
	 * Gets an account value from the session. If the session is not set yet, updates from the database.
	 * 
	 * @param	string	$name
	 * 		The value name.
	 * 
	 * @return	string
	 * 		The account value.
	 */
	private static function getAccountValue($name)
	{
		// Check session existance
		if (!isset(self::$accountData[$name]))
			self::$accountData = self::info();
			
		return self::$accountData[$name];
	}
	
	/**
	 * Gets the account info.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the information for.
	 * 		Leave empty for the current account.
	 * 		It is empty by default.
	 * 		If it's not the current account, only public information will be returned.
	 * 
	 * @return	array
	 * 		Returns an array of the account information.
	 */
	public static function info($accountID = "")
	{
		// Get current account id if empty
		$accountID = (empty($accountID) ? self::getAccountID() : $accountID);
		
		// Get all account info
		$publicInfo = array();
		$accountInfo = self::getInfo($accountID);
		
		// Get public profile page
		if (empty($accountInfo['username']))
		{
			$params = array();
			$params['id'] = $accountID;
			$profileUrl = url::resolve("www", "/profile/index.php", $params);
		}
		else
			$profileUrl = url::resolve("www", "/profile/".$accountInfo['username']);
		$publicInfo['profile_url'] = $profileUrl;
		
		// Get profile image (if any)
		$imagePath = self::getAccountFolderPath($accountID)."/media/profile.png";
		if (file_exists(systemRoot.$imagePath))
		{
			$imageUrl = str_replace(paths::getProfilePath(), "", $imagePath);
			$imageUrl = url::resolve("profile", $imageUrl);
			$publicInfo['profile_image_url'] = $imageUrl;
		}
		
		// Check for public or private information
		if (!self::validate() || $accountID != self::getAccountID())
		{
			$publicInfo['accountID'] = $accountInfo['accountID'];
			$publicInfo['accountTitle'] = $accountInfo['accountTitle'];
			$publicInfo['username'] = $accountInfo['username'];

			return $publicInfo;
		}
		
		// Get all account info
		$accountInfo = array_merge($accountInfo, $publicInfo);
		unset($accountInfo['password']);
		return $accountInfo;
	}
	
	/**
	 * Update the account profile image.
	 * 
	 * @param	data	$image
	 * 		The image data.
	 * 		The image should be in png format.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 		If empty or in secure mode this will be the current account.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateProfileImage($image, $accountID = "")
	{
		// Get team id
		$accountID = (empty($accountID) || importer::secure() ? self::getAccountID() : $accountID);
		
		// Get profile image path
		$imagePath = self::getAccountFolder($accountID)."/media/profile.png";
		
		// Remove image if empty
		if (is_null($image))
			fileManager::remove(systemRoot.$imagePath);
		
		// If image is empty other than null, return false
		if (empty($image))
			return FALSE;
		
		// Update image
		return fileManager::create(systemRoot.$imagePath, $image);
	}
	
	/**
	 * Gets an account's information.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get information for.
	 * 
	 * @return	array
	 * 		Array of account data.
	 */
	private static function getInfo($accountID)
	{
		$dbc = new dbConnection();
		$q = new dbQuery("177361907", "profile.account");
		$attr = array();
		$attr['id'] = $accountID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Gets the unique folder id for the requested use.
	 * 
	 * @param	string	$prefix
	 * 		The prefix of the folder.
	 * 
	 * @param	string	$folderID
	 * 		The id to be hashed.
	 * 
	 * @param	string	$extension
	 * 		The extension of the folder (if any).
	 * 
	 * @return	string
	 * 		The folder name.
	 */
	private static function getFolderID($prefix, $folderID, $extension = "")
	{
		return $prefix.hash("md5", $folderID).(empty($extension) ? "" : ".".$extension);
	}
}
//#section_end#
?>