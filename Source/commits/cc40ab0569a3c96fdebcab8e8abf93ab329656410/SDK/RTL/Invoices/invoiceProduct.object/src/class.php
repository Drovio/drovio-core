<?php
//#section#[header]
// Namespace
namespace RTL\Invoices;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
class invoiceProduct
{
	// Constructor Method
	public function __construct()
	{
		// Put your constructor method code here.
	}
}
//#section_end#
?>