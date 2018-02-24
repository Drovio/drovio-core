<?php
//#section#[header]
// Namespace
namespace DEV\WebTemplates\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	WebTemplates
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "WebTemplates", "template");


use \DEV\WebTemplates\template;
/**
 * Web Template PAge
 * 
 * Object class to manage web template page
 * 
 * @version	{empty}
 * @created	July 7, 2014, 22:08 (EEST)
 * @revised	July 7, 2014, 22:14 (EEST)
 */
class templPage
{
	/**
	 * Object file extension
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "page";
	
	/**
	 * The parent template object
	 * 
	 * @type	template
	 */
	private $templ;
	
	/**
	 * The page name
	 * 
	 * @type	string
	 */
	private $name;

	/**
	 * The constructor class
	 * 
	 * @param	string	$templID
	 * 		The id of the parent template, the one that the page belongs to
	 * 
	 * @param	string	$name
	 * 		The name of the page
	 * 
	 * @return	void
	 */
	public function __construct($templID, $name = "")
	{
		// Init
		$this->templ= new template($templID);
		
		// Set name
		$this->name = $name;
	}
}
//#section_end#
?>