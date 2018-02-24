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
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Profile", "company");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;

class prices
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