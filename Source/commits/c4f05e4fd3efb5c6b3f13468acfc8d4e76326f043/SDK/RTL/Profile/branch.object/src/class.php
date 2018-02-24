<?php
//#section#[header]
// Namespace
namespace RTL\Profile;

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
 * @package	Profile
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
 * Company Branch Manager
 * 
 * {description}
 * 
 * @version	0.1-2
 * @created	December 3, 2014, 15:01 (EET)
 * @revised	December 10, 2014, 11:09 (EET)
 */
class branch
{
	/**
	 * Create a new company branch.
	 * 
	 * Only company administrators can execute.
	 * 
	 * @param	string	$title
	 * 		The branch title.
	 * 
	 * @param	string	$address
	 * 		The branch address.
	 * 		It could include a full address with postal code, city and country.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($title, $address = "")
	{
		// Check if it is company admin
		
		// Create branch
		$dbc = new dbConnection();
		$q = new dbQuery("25421712376204", "retail.company.branches");
		$attr = array();
		$attr['id'] = company::getCompanyID();
		return $dbc->execute($q, $attr);
	}
}
//#section_end#
?>