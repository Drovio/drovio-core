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

importer::import("ESS", "Protocol", "server::ModuleProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\ModuleProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Redback's toolbar ribbon object.
 * 
 * It's a singleton pattern implementation for Redback Core Toolbar Ribbon.
 * Builds the system's ribbon for extra content, navigation and other tricks.
 * 
 * @version	{empty}
 * @created	June 11, 2014, 9:36 (EEST)
 * @revised	July 2, 2014, 19:43 (EEST)
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
	 */
	public function insertCollection($id, $moduleID = "", $viewName = "", $startup = FALSE)
	{
		// Ribbon Collection
		$collection = $this->getCollection($id, $moduleID , $viewName, $startup);
		DOM::append($this->colGroup, $collection);
		
		return $collection;
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
	 */
	public static function getCollection($id, $moduleID = "", $viewName = "", $startup = FALSE)
	{
		// Ribbon Collection
		$collection = DOM::create("div", "", $id, "collection noDisplay");
		if (!empty($moduleID))
			ModuleProtocol::addAsync($collection, $moduleID, $viewName, "", $startup);
		
		return $collection;
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
	 * @param	boolean	$pinnable
	 * 		Defines whether the ribbon will be pinnable for this action.
	 * 
	 * @return	void
	 */
	public static function addNavigation($item, $refId, $ribbonType = "float", $type = "obedient", $pinnable = FALSE)
	{
		// Set Data Navigation
		$navigation = array();
		$navigation['ref'] = $refId;
		$navigation['ribbon'] = $ribbonType;
		$navigation['type'] = $type;
		$navigation['pinnable'] = ($pinnable ? "1" : "0");
		DOM::data($item, 'rbn-nav', $navigation);
	}
}
//#section_end#
?>