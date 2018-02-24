<?php
//#section#[header]
// Namespace
namespace UI\Core\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Core
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Prototype", "content/ModuleContainerPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \ESS\Protocol\ModuleProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\content\ModuleContainerPrototype;
use \UI\Html\DOM;
use \UI\Html\HTML;

/**
 * Redback's toolbar ribbon object.
 * 
 * It's a singleton pattern implementation for Redback Core Toolbar Ribbon.
 * Builds the system's ribbon for extra content, navigation and other tricks.
 * 
 * @version	0.1-4
 * @created	June 11, 2014, 9:36 (EEST)
 * @revised	November 24, 2014, 13:49 (EET)
 */
class RTRibbon extends UIObjectPrototype
{
	/**
	 * The singleton's instance.
	 * 
	 * @type	RTRibbon
	 */
	private static $instance;
	
	/**
	 * The collection group for the ribbon panel groups.
	 * 
	 * @type	DOMElement
	 */
	private $colGroup;
	
	/**
	 * Constructor function for RTRibbon Instance.
	 * 
	 * @return	void
	 */
	protected function __construct(){}
	
	/**
	 * Gets the instance of the RTRibbon.
	 * 
	 * @return	RTRibbon
	 * 		The RTRibbon unique instance.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new RTRibbon();
		
		return self::$instance;
	}
	
	/**
	 * Builds the ribbon object.
	 * 
	 * @return	RTRibbon
	 * 		The ribbon object.
	 */
	public function build()
	{
		$holder = DOM::create('div', "", "", "uiTlbRibbon");
		$this->set($holder);
		
		$ribbon = DOM::create('div', "", "", "ribbon");
		DOM::append($holder, $ribbon);
		
		// Collection's Group
		$this->colGroup = DOM::create('div', "", "", "colGroup");
		DOM::append($ribbon, $this->colGroup);
		
		// Collection's status/title bar
		$statusbar = DOM::create("div", "", "", "statusbar title");
		DOM::append($ribbon, $statusbar);
		
		return $this;
	}
	
	/**
	 * Get the current collection group.
	 * 
	 * @return	DOMelement
	 * 		The current collection group.
	 */
	public function getCollectionGroup()
	{
		return $this->colGroup;
	}
	
	/**
	 * Adds a collection into the collection group.
	 * 
	 * @param	string	$collectionID
	 * 		The collection element id.
	 * 
	 * @param	string	$title
	 * 		The collection's title.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load when collection is open.
	 * 		This is optional when we want to load a module view inside the collection.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 
	 * @param	boolean	$startup
	 * 		Set the collection to load during startup.
	 * 		False by default.
	 * 
	 * @return	DOMElement
	 * 		The collection element.
	 */
	public function addCollection($collectionID, $title = "", $moduleID = "", $viewName = "", $startup = FALSE)
	{
		// Ribbon Collection
		$collection = $this->createCollection($collectionID, $title, $moduleID , $viewName, $startup);
		DOM::append($this->colGroup, $collection);
		
		return $collection;
	}
	
	/**
	 * Creates and ribbon a collection.
	 * 
	 * @param	string	$collectionID
	 * 		The collection element id.
	 * 
	 * @param	string	$title
	 * 		The collection's title.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load when collection is open.
	 * 		This is optional when we want to load a module view inside the collection.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 
	 * @param	boolean	$startup
	 * 		Set the collection to load during startup.
	 * 		False by default.
	 * 
	 * @return	DOMElement
	 * 		The collection element.
	 */
	public static function createCollection($collectionID, $title = "", $moduleID = "", $viewName = "", $startup = FALSE)
	{
		// Ribbon Collection
		if (!empty($moduleID))
		{
			$mdlContainer = new ModuleContainerPrototype($moduleID, $viewName);
			$collection = $mdlContainer->build($attr = array(), $startup, $collectionID, $loading = FALSE)->get();
			
		}
		else
			$collection = DOM::create("div", "", $collectionID);
		
		// Add classes and extra data
		HTML::addClass($collection, "collection");
		HTML::addClass($collection, "noDisplay");
		HTML::attr($collection, "data-title", $title);
		
		// Return collection
		return $collection;
	}
	
	/**
	 * Inserts a collection into the collection group.
	 * 
	 * @param	string	$id
	 * 		The id of the collection.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load at open.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 
	 * @param	boolean	$startup
	 * 		Sets the collection to load during startup.
	 * 
	 * @return	DOMElement
	 * 		The collection element.
	 * 
	 * @deprecated	Use addCollection() instead.
	 */
	public function insertCollection($id, $moduleID = "", $viewName = "", $startup = FALSE)
	{
		return $this->addCollection($id, $title = "", $moduleID, $viewName, $startup);
	}
	
	/**
	 * Creates and ribbon a collection
	 * 
	 * @param	string	$id
	 * 		The id of the collection.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load at open.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 
	 * @param	boolean	$startup
	 * 		Sets the collection to load during startup.
	 * 
	 * @return	DOMEelement
	 * 		The collection element.
	 * 
	 * @deprecated	Use createCollection() instead.
	 */
	public static function getCollection($id, $moduleID = "", $viewName = "", $startup = FALSE)
	{
		return self::createCollection($id, $title = "", $moduleID, $viewName, $startup);
	}
	
	/**
	 * Appends a given item to a given collection.
	 * 
	 * @param	DOMElement	$collection
	 * 		The receptor collection
	 * 
	 * @param	DOMElement	$item
	 * 		The appended item
	 * 
	 * @return	void
	 */
	public static function insertItem($collection, $item)
	{
		DOM::append($collection, $item);
	}
	
	/**
	 * Inserts action to specific toolbar navigation item.
	 * 
	 * @param	DOMElement	$item
	 * 		The item that will receive the action.
	 * 
	 * @param	string	$refId
	 * 		The collection reference id.
	 * 
	 * @param	string	$ribbonType
	 * 		The ribbon appearance type.
	 * 		Accepted values:
	 * 		- "float", this will show the ribbon as popup.
	 * 		- "inline", this will show the ribbon as ribbon.
	 * 
	 * @param	string	$type
	 * 		The popup type. See PopupPrototype for more information.
	 * 
	 * @return	void
	 */
	public static function addNavigation($item, $refId, $ribbonType = "float", $type = "obedient")
	{
		// Set Data Navigation
		$navigation = array();
		$navigation['ref'] = $refId;
		$navigation['ribbon'] = $ribbonType;
		$navigation['type'] = $type;
		DOM::data($item, 'rbn-nav', $navigation);
	}
}
//#section_end#
?>