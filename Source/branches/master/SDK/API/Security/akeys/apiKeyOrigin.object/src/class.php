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
 * API key allowed origin manager
 * 
 * Manages API key allowed origins including validation.
 * 
 * @version	0.1-1
 * @created	November 17, 2015, 19:30 (GMT)
 * @updated	November 17, 2015, 19:30 (GMT)
 */
class apiKeyOrigin
{
	/**
	 * Create a new API key origin.
	 * 
	 * @param	string	$akey
	 * 		The API key.
	 * 
	 * @param	string	$origin
	 * 		The allowed origin.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($akey, $origin = "*")
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("1612265252973", "security.akeys.origins");
		
		// Create key allowed origin
		$attr = array();
		$attr['akey'] = $akey;
		$attr['origin'] = $origin;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Update an existing API key origin.
	 * 
	 * @param	string	$akey
	 * 		The API key.
	 * 
	 * @param	string	$origin
	 * 		The allowed origin.
	 * 
	 * @param	string	$newOrigin
	 * 		The new allowed origin.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($akey, $origin, $newOrigin = "*")
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("29933424211007", "security.akeys.origins");
		
		// Update key allowed origin
		$attr = array();
		$attr['akey'] = $akey;
		$attr['origin'] = $origin;
		$attr['new_origin'] = $newOrigin;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all API key allowed origins.
	 * 
	 * @param	string	$akey
	 * 		The API key to get all origins for.
	 * 
	 * @return	array
	 * 		An array of all allowed origins.
	 */
	public static function getAllOrigins($akey)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("17251755288449", "security.akeys.origins");
		
		// Get all allowed origins
		$attr = array();
		$attr['akey'] = $akey;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, "referer", "referer");
	}
	
	/**
	 * Validate whether the given key is valid for the given origin.
	 * 
	 * @param	string	$akey
	 * 		The key to validate.
	 * 
	 * @param	string	$origin
	 * 		The origin to check.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	public static function validate($akey, $origin)
	{
		// Get all key origins
		$originList = self::getAllOrigins($akey);
		
		// If there are no allowed origins, it is valid
		if (empty($originList))
			return TRUE;
		
		// Make origin readable for preg match
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