<?php
//#section#[header]
// Namespace
namespace API\Model\units\sql;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
// Import

// Usage

class sqlQuery
{
	protected $query;
	
	public function __construct() {}
	
	// Create or get the query
	public function query($query = NULL)
	{
		if (is_null($query))
			return $this->query;
		
		$this->query = $query;
	}
}
//#section_end#
?>