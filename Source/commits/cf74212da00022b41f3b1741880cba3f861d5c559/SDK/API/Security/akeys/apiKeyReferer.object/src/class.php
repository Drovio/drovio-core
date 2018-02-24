<?php
//#section#[header]
// Namespace
namespace API\Security\akeys;

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
 * @package	Security
 * @namespace	\akeys
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * API key referer manager
 * 
 * Manages API key referers including validation.
 * 
 * @version	0.1-1
 * @created	October 10, 2015, 22:53 (EEST)
 * @updated	October 10, 2015, 22:53 (EEST)
 */
class apiKeyReferer
{
	/**
	 * Create a new API key referer.
	 * 
	 * @param	string	$akey
	 * 		The API key.
	 * 
	 * @param	string	$referer
	 * 		The referer.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($akey, $referer = "*")
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("24221906138373", "security.akeys.referers");
		
		// Create key referer
		$attr = array();
		$attr['akey'] = $akey;
		$attr['referer'] = $referer;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Update an existing API key referer.
	 * 
	 * @param	string	$akey
	 * 		The API key.
	 * 
	 * @param	string	$referer
	 * 		The current referer.
	 * 
	 * @param	string	$newReferer
	 * 		The new referer.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($akey, $referer, $newReferer = "*")
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("35864027826278", "security.akeys.referers");
		
		// Create key referer
		$attr = array();
		$attr['akey'] = $akey;
		$attr['referer'] = $referer;
		$attr['new_referer'] = $newReferer;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all API key referes.
	 * 
	 * @param	string	$akey
	 * 		The API key to get all the referers for.
	 * 
	 * @return	array
	 * 		An array of all referers.
	 */
	public static function getAllReferers($akey)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("1466759674944", "security.akeys.referers");
		
		// Get all referrers
		$attr = array();
		$attr['akey'] = $akey;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, "referer", "referer");
	}
	
	/**
	 * Validate whether the given key is valid for the given referer.
	 * 
	 * @param	string	$akey
	 * 		The key to validate.
	 * 
	 * @param	string	$referer
	 * 		The referer.
	 * 		If empty, get the url referer from the $_SERVER variable.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	public static function validate($akey, $referer = "")
	{
		// Normalize given referrer
		$referer = (empty($referer) ? $_SERVER['HTTP_REFERER'] : $referer);
		
		// Get all key referers
		$refList = self::getAllReferers($akey);
		
		// If there are no referers, it is valid
		if (empty($refList))
			return TRUE;
		
		// Make referer readable for preg match
		$referer = str_replace("https://", "", $referer);
		$referer = str_replace("http://", "", $referer);
		
		// Check all referers
		foreach ($refList as $referer)
		{
			// Create regular expression
			$regEx = $referer;
			$regEx = preg_quote($regEx, '/');
			// Replace wildcard
			$regEx = str_replace('\*', '.*', $regEx);
			$regEx = '/^'.$regEx.'/';
			
			// Match
			if (preg_match($regEx, $referer))
				return TRUE;
		}
		
		// No referer matched
		return FALSE;
	}
}
//#section_end#
?>