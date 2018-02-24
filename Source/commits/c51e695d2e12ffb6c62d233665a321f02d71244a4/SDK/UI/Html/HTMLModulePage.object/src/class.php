<?php
//#section#[header]
// Namespace
namespace UI\Html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Prototype", "html::ModuleContainerPrototype");
importer::import("ESS", "Prototype", "ActionFactory");
importer::import("ESS", "Templates", "ModulePageTemplate");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTMLPage");
importer::import("UI", "Html", "pageComponents::HTMLRibbon");
importer::import("UI", "Html", "pageComponents::toolbarComponents::toolbarItem");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\html\ModuleContainerPrototype;
use \ESS\Prototype\ActionFactory;
use \ESS\Templates\ModulePageTemplate;
use \UI\Html\DOM;
use \UI\Html\HTMLPage;
use \UI\Html\pageComponents\HTMLRibbon;
use \UI\Html\pageComponents\toolbarComponents\toolbarItem;

/**
 * HTML Module Page Builder
 * 
 * Builds the inner module page with a selected layout.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:42 (EET)
 * @revised	October 15, 2013, 14:37 (EEST)
 */
class HTMLModulePage extends ModulePageTemplate
{
	/**
	 * Inserts a new toolbar navigation item.
	 * 
	 * @param	string	$id
	 * 		The item's id
	 * 
	 * @param	string	$title
	 * 		The item's title
	 * 
	 * @param	string	$class
	 * 		The item's class
	 * 
	 * @param	DOMElement	$collection
	 * 		The ribbon's collection that will appear at item click.
	 * 
	 * @param	string	$ribbonType
	 * 		The ribbon type.
	 * 		See HTMLRibbon for more information.
	 * 
	 * @param	string	$type
	 * 		The ribbon popup type (if float).
	 * 		See HTMLRibbon for more information.
	 * 
	 * @param	boolean	$pinnable
	 * 		Sets the ribbon pinnable.
	 * 		See HTMLRibbon for more information.
	 * 
	 * @param	integer	$index
	 * 		The item's position index.
	 * 
	 * @param	mixed	$ico
	 * 		Defines whether this item will have an ico. It can be used as DOMElement to set the ico.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function addToolbarNavItem($id, $title, $class, $collection = NULL, $ribbonType = "float", $type = "obedient", $pinnable = FALSE, $index = 0, $ico = TRUE)
	{
		// Create holder
		$itemHolder = DOM::create("div", "", "", "nav_item");
		$this->appendToMisc($itemHolder);
		
		// Toolbar Navigation Builder
		$toolbarItem = new toolbarItem();
		$item = $toolbarItem->build($title, $id, $class, $scope = "page", $ico, $index)->get();
		DOM::append($itemHolder, $item);
		
		// Insert collection (if any)
		if (!is_null($collection))
		{
			// Append Collection
			DOM::append($itemHolder, $collection);
			
			// Get reference id from collection
			$refID = DOM::attr($collection, "id");
			
			// Create item Ribbon Navigation
			HTMLRibbon::addNavigation($item, $refID, $ribbonType, $type, $pinnable);
		}
		
		return $item;
	}
	
	/**
	 * Builds and returns a ribbon collection from HTMLRibbon.
	 * 
	 * @param	string	$id
	 * 		The collection's id.
	 * 
	 * @param	string	$moduleID
	 * 		Sets the module id for auto loading.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module for auto loading.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getRibbonCollection($id, $moduleID = "", $action = "")
	{
		return HTMLRibbon::getCollection($id, $moduleID, $action);
	}
	
	/**
	 * Returns the ServerReport of this HTML Module Page.
	 * 
	 * @param	string	$holder
	 * 		The holder for the modulePage content.
	 * 		By default, it follows the general rules of module protocol and has no holder.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getReport($holder = "#pageContainer")
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Add this modulePage as content
		HTMLServerReport::addContent($this->get(), $type = "data", $holder, $method = "replace");
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	/**
	 * Creates a static navigation group.
	 * For more information, see the NavigatorProtocol.
	 * 
	 * @param	string	$id
	 * 		The id of the navigation group.
	 * 
	 * @param	string	$groupSelector
	 * 		The static navigation selector for the group.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function getNavigationGroup($id, $groupSelector)
	{
		// Create Navigation Group
		$navGroup = DOM::create("div", "", $id);
		NavigatorProtocol::selector($navGroup, $groupSelector);
		
		return $navGroup;
	}
	
	/**
	 * Builds a module container and returns the DOMElement.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module (if any)
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module with GET method during loading.
	 * 
	 * @param	boolean	$startup
	 * 		Sets the module to load at startup (next content.modified).
	 * 
	 * @param	string	$containerID
	 * 		The id of the module container as a DOM Object.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function getModuleContainer($moduleID, $action = "", $attr = array(), $startup = TRUE, $containerID = "")
	{
		$moduleContainer = new ModuleContainerPrototype($moduleID, $action);
		return $moduleContainer->build($attr, $startup, $containerID)->get();
	}
	
	/**
	 * Gets the HTMLPage module holder selector.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getPageHolder()
	{
		return HTMLPage::HOLDER;
	}
	
	/**
	 * Creates a new Action Factory.
	 * 
	 * @return	ActionFactory
	 * 		Returns an instance of the Action Factory.
	 */
	public function getActionFactory()
	{
		return new ActionFactory();
	}
}
//#section_end#
?>