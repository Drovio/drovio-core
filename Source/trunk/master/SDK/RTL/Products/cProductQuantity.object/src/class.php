<?php
//#section#[header]
// Namespace
namespace RTL\Products;

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
 * @package	Products
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("RTL", "Products", "cProductStock");

use \RTL\Products\cProductStock;

/**
 * Company Product Quantity Manager
 * 
 * Manages product quantities in branches and in storages.
 * 
 * @version	1.0-1
 * @created	December 16, 2014, 13:24 (EET)
 * @updated	September 1, 2015, 16:58 (EEST)
 * 
 * @deprecated	Use \RTL\Products\cProductStock instead.
 */
class cProductQuantity extends cProductStock {}
//#section_end#
?>