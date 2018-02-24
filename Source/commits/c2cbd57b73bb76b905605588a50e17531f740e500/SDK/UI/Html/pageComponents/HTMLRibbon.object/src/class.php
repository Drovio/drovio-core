<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents;

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
 * @namespace	\pageComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::ModuleProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\ModuleProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * HTML Toolbar Ribbon Object
 * 
 * Builds the system's ribbon.
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:13 (EEST)
 * @revised	July 16, 2013, 12:49 (EEST)
 */
class HTMLRibbon extends UIObjectPrototype
{
	/**
	 * The collection group for the ribbon panel groups.
	 * 
	 * @type	DOMElement
	 */
	private $colGroup;
	
	/**
	 * Constructor Method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Put your constructor method code here.
	}
	
	/**
	 * Builds the ribbon.
	 * 
	 * @return	HTMLRibbon
	 * 		{description}
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
		
		// Ribbon tools
		$tools = DOM::create('div', "", "", "tools");
		DOM::append($ribbon, $tools);
		
		// Tools Pin Ico
		$pin = DOM::create('div', "", "", "pin");
		DOM::append($tools, $pin);
		
		
		return $this;
	}
	
	/**
	 * Returns the current collection group.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getCollectionGroup()
	{
		return $this->colGroup;
	}
	
	/**
	 * Inserts a collection into the collection group.
	 * It returns the collection added.
	 * 
	 * @param	string	$id
	 * 		The id of the collection
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load at open.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module.
	 * 
	 * @param	boolean	$startup
	 * 		Sets the collection to load during startup.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function insertCollection($id, $moduleID = "", $action = "", $startup = FALSE)
	{
		// Ribbon Collection
		$collection = $this->getCollection($id, $moduleID , $action, $startup);
		DOM::append($this->colGroup, $collection);
		
		return $collection;
	}
	
	/**
	 * Creates and returns a collection
	 * 
	 * @param	string	$id
	 * 		The id of the collection
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load at open.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module.
	 * 
	 * @param	boolean	$startup
	 * 		Sets the collection to load during startup.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function getCollection($id, $moduleID = "", $action = "", $startup = FALSE)
	{
		// Ribbon Collection
		$collection = DOM::create("div", "", $id, "collection noDisplay");
		if (!empty($moduleID))
			ModuleProtocol::addAsync($collection, $moduleID, $action, "", $startup);
		
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
	 * 		The popup type.
	 * 		See PopupPrototype.
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