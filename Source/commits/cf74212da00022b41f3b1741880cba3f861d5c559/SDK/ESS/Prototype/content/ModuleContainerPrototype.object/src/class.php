<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\content;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\content
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("ESS", "Protocol", "loaders/ModuleLoader");
importer::import("API", "Model", "modules/module");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \ESS\Protocol\ModuleProtocol;
use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\loaders\ModuleLoader;
use \API\Model\modules\module;
use \UI\Html\DOM;
use \UI\Prototype\UIObjectPrototype;

/**
 * Module Container
 * 
 * Builds a module container element.
 * This will be filled asynchronously on content.modified with the module assigned.
 * 
 * @version	0.2-5
 * @created	September 8, 2014, 10:34 (EEST)
 * @updated	October 10, 2015, 21:30 (EEST)
 */
class ModuleContainerPrototype extends UIObjectPrototype
{
	/**
	 * The module id.
	 * 
	 * @type	integer
	 */
	private $moduleID;
	/**
	 * The module's view name.
	 * 
	 * @type	string
	 */
	private $viewName;
	
	/**
	 * Defines the module id and the view name.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		Leave empty to get the module's default view.
	 * 		Empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($moduleID, $viewName = "")
	{
		// Set Module Variables
		$this->moduleID = $moduleID;
		$this->viewName = $viewName;
	}
	
	/**
	 * Builds the module container with the given parameters.
	 * 
	 * @param	array	$attr
	 * 		An array of parameters to be sent to the server by GET method.
	 * 
	 * @param	boolean	$startup
	 * 		Defines whether this container will be invoked to be filled up at startup (on the next content.modified trigger)
	 * 
	 * @param	string	$containerID
	 * 		The id of the module container DOMElement.
	 * 
	 * @param	boolean	$loading
	 * 		Set the page loading indicator.
	 * 
	 * @param	boolean	$preload
	 * 		Set the container to preload the content synchronously.
	 * 		This must be used only when loading a module view from another module view (inner module loading).
	 * 
	 * @return	ModuleContainerPrototype
	 * 		The ModuleContainerPrototype object.
	 */
	public function build($attr = array(), $startup = TRUE, $containerID = "", $loading = FALSE, $preload = FALSE)
	{
		// Set Container
		$containerID = (empty($containerID) ? "mdl".$this->moduleID.mt_rand() : $containerID);
		$moduleContainer = DOM::create("div", "", $containerID, "moduleContainer");
		$this->set($moduleContainer);
		
		// If preload, disable startup
		$startup = ($preload ? FALSE : $startup);
			
		// Set async attributes
		ModuleProtocol::addAsync($moduleContainer, $this->moduleID, $this->viewName, "", $startup, $attr, $loading);
		
		// If preload, load view now
		if ($preload)
		{
			// Merge attributes with $_GET
			$_GET = array_merge($_GET, $attr);
			
			// Load view and append to container
			$moduleContent = module::loadView($this->moduleID, $this->viewName);
			DOM::append($moduleContainer, $moduleContent);
			
			// Add module resources to Report Header
			$resources = ModuleLoader::getModuleResources($this->moduleID);
			foreach ($resources as $rsrcID => $resource)
				HTMLServerReport::addHeader($resource);
				
		}
		
		return $this;
	}
}
//#section_end#
?>