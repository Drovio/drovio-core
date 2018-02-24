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

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Profile", "company");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;

/**
 * Retail Price Manager
 * 
 * {description}
 * 
 * @version	0.1-2
 * @created	December 2, 2014, 13:04 (EET)
 * @revised	December 10, 2014, 11:06 (EET)
 */
class prices
{
	/**
	 * Get all company price types, including global types.
	 * 
	 * @return	array
	 * 		An array of all price types.
	 */
	public static function getPriceTypes()
	{
		// Get all tax rates in the given company
		$dbc = new dbConnection();
		$q = new dbQuery("26594258916143", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>