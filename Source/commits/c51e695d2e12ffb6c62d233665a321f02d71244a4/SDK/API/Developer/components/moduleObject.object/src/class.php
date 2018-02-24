<?php
//#section#[header]
// Namespace
namespace API\Developer\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::modules::module");
importer::import("API", "Developer", "components::modules::auxiliary");

use \API\Developer\components\modules\module;
use \API\Developer\components\modules\auxiliary;

/**
 * Module Object Instance
 * 
 * Manages a module's objects like itself, its auxiliaries and the resources, javascript and css.
 * 
 * @version	{empty}
 * @created	March 15, 2013, 13:47 (EET)
 * @revised	July 18, 2013, 13:15 (EEST)
 */
class moduleObject
{
	/**
	 * The module's id
	 * 
	 * @type	integer
	 */
	private $moduleID;
	
	/**
	 * Constructor method.
	 * Initializes the module's variables.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id
	 * 
	 * @return	void
	 */
	public function __construct($moduleID = "")
	{
		$this->moduleID = $moduleID;
	}
	
	/**
	 * Creates a module and returns an instance of it.
	 * 
	 * @param	integer	$groupID
	 * 		The module's group id.
	 * 
	 * @param	string	$title
	 * 		The module's title.
	 * 
	 * @param	string	$description
	 * 		The module's description
	 * 
	 * @return	module
	 * 		{description}
	 */
	public function createModule($groupID, $title, $description = "")
	{
		$module = new module();
		if ($module->create($groupID, $title, $description))
			return $module;
		else
			return FALSE;
	}
	
	/**
	 * Creates a module auxiliary and returns an instance of it.
	 * 
	 * @param	string	$title
	 * 		The auxiliary's title.
	 * 
	 * @param	string	$description
	 * 		The auxiliary's description
	 * 
	 * @return	auxiliary
	 * 		{description}
	 */
	public function createAuxiliary($title, $description)
	{
		$auxModule = new auxiliary();
		if ($auxModule->create($this->moduleID, $title, $description))
			return $auxModule;
		else
			return FALSE;
	}
	
	/**
	 * Gets an instance of a module or module auxiliary.
	 * 
	 * @param	string	$auxTitle
	 * 		The auxiliary name. Empty by default.
	 * 
	 * @param	string	$auxSeed
	 * 		The auxiliary seed. Empty be default.
	 * 
	 * @return	module
	 * 		{description}
	 */
	public function getModule($auxTitle = "", $auxSeed = "")
	{
		if (empty($auxTitle) && empty($auxSeed))
		{
			$module = new module();
			$module->initialize($this->moduleID)->load();
		}
		else
		{
			$module = new auxiliary();
			$module->initialize($this->moduleID, $auxSeed, $auxTitle)->load();
		}
		
		return $module;
	}
}
//#section_end#
?>