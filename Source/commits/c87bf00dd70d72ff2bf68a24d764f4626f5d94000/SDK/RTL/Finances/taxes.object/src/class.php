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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Literals", "literal");
importer::import("API", "Model", "sql/dbQuery");

use \RTL\Comm\dbConnection;
use \API\Literals\literal;
use \API\Model\sql\dbQuery;

/**
 * Retail Tax Manager
 * 
 * Manages the tax rates and has access to generic tax information.
 * 
 * @version	0.1-3
 * @created	November 20, 2014, 17:33 (EET)
 * @updated	September 24, 2015, 0:20 (EEST)
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
	public static function getTaxRates($countryID = "")
	{
		// Set Greece as default country
		$countryID = (empty($countryID) ? 87 : $countryID);
		
		// Get all tax rates in the given company
		$dbc = new dbConnection();
		$q = new dbQuery("30590685937382", "retail.taxes");
		$attr = array();
		$attr['cid'] = $countryID;
		$result = $dbc->execute($q, $attr);
		
		// Traverse and get literals
		$rates = array();
		$tliterals = literal::get($scope = "retail.prices.taxe_rates", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $dbc->fetch($result))
		{
			$row['title'] = $tliterals["type_".$row['title']];
			$rates[$row['id']] = $row;
		}
		
		return $rates;
	}
}
//#section_end#
?>