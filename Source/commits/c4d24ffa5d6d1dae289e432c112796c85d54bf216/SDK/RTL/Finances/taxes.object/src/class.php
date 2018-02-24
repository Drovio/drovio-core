<?php
//#section#[header]
// Namespace
namespace RTL\Finances;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	RTL
 * @package	Finances
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * Retail Tax Manager
 * 
 * Manages the tax rates and has access to generic tax information.
 * 
 * @version	0.1-1
 * @created	November 20, 2014, 17:33 (EET)
 * @revised	November 20, 2014, 17:33 (EET)
 */
class taxes
{
	/**
	 * Get all tax rates for a given country, by id.
	 * 
	 * You can get all countries from the API\Geoloc package.
	 * 
	 * @param	integer	$countryID
	 * 		The country id to get the tax rates for.
	 * 
	 * @return	array
	 * 		An array with all tax rates.
	 */
	public static function getTaxRates($countryID)
	{
		// Get all tax rates in the given company
		$dbc = new dbConnection();
		$q = new dbQuery("30590685937382", "retail.taxes");
		$attr = array();
		$attr['cid'] = $countryID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>