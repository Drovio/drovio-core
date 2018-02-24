<?php
//#section#[header]
// Namespace
namespace SYS\Resources\settings;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Resources
 * @namespace	\settings
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "settingsManager");

use \API\Resources\DOMParser;
use \API\Resources\settingsManager;

/**
 * Platform account manager.
 * 
 * Manages all external account credentials.
 * 
 * @version	0.1-1
 * @created	December 22, 2014, 16:30 (EET)
 * @revised	December 22, 2014, 16:30 (EET)
 */
class accSettings extends settingsManager
{
	/**
	 * The account type.
	 * 
	 * @type	string
	 */
	private $accountType;
	
	/**
	 * The account name.
	 * 
	 * @type	string
	 */
	private $accountName;
	
	/**
	 * Initialize the account manager.
	 * 
	 * @param	string	$type
	 * 		The account type.
	 * 		Keep it lowercase.
	 * 
	 * @param	string	$name
	 * 		The account name.
	 * 
	 * @return	void
	 */
	public function __construct($type, $name)
	{
		// Construct settingsManager
		$this->accountType = $type;
		$this->accountName = $name;
		parent::__construct(systemConfig."/Settings/Accounts/".$type, $name, TRUE);
	}
	
	/**
	 * Create a new account entry in the list.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create()
	{
		// Check empty
		if (empty($this->accountType) || empty($this->accountName))
			return FALSE;
			
		// Create settings file
		$status = parent::create();
		
		// Add server to list
		if ($status)
			return $this->addAccount($this->accountType, $this->accountName);
		
		return FALSE;
	}
	
	/**
	 * Update the account credentials.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 
	 * @param	string	$password
	 * 		The account password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateAccount($username, $password)
	{
		$this->set("USERNAME", $username);
		$this->set("PASSWORD", $password);
		
		return $this->update();
	}
	
	/**
	 * Add a new account to the list.
	 * 
	 * @param	string	$type
	 * 		The account type.
	 * 		Keep it lowercase.
	 * 
	 * @param	string	$name
	 * 		The account name.
	 * 
	 * @return	void
	 */
	private function addAccount($type, $name)
	{
		// Get server list (initialize and create file if not exist)
		$serverList = $this->getAccounts();
		
		// Add server to list
		$parser = new DOMParser();
		$parser->load(systemConfig."/Settings/Accounts/accounts.xml");
		$root = $parser->evaluate("/accounts")->item(0);
		
		// Check type
		$accountType = $parser->evaluate("//type[@name='".$type."']")->item(0);
		if (empty($accountType))
		{
			$accountType = $parser->create("type");
			$parser->attr($accountType, "name", $type);
			$parser->append($root, $accountType);
		}
		
		// Check if server already exists
		$account = $parser->evaluate("account[@name='".$name."']", $accountType)->item(0);
		if (!empty($account))
			return TRUE;
		
		// Add server
		$account = $parser->create("account");
		$parser->attr($account, "name", $name);
		$parser->append($accountType, $account);
		
		return $parser->update();
	}
	
	/**
	 * Get all stored accounts by type.
	 * 
	 * @return	array
	 * 		An array of all accounts by type.
	 */
	public static function getAccounts()
	{
		$parser = new DOMParser();
		try
		{
			$parser->load(systemConfig."/Settings/Accounts/accounts.xml");
		}
		catch (Exception $ex)
		{
			// Create file
			$root = $parser->create("accounts");
			$parser->append($root);
			$parser->save(systemRoot.systemConfig."/Settings/Accounts/accounts.xml");
		}
		
		// Get list
		$accounts = $parser->evaluate("//account");
		$accountList = array();
		foreach ($accounts as $account)
		{
			// Get account info
			$accountType = $parser->attr($account->parentNode, "name");
			$accountName = $parser->attr($account, "name");
			
			// Add to list
			$accountList[$accountType][] = $accountName;
		}
		
		// Return list
		return $accountList;
	}
}
//#section_end#
?>