<?php
//#section#[header]
// Namespace
namespace UI\Html;

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
 * @package	Html
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "layoutManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTMLPage");
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Html", "pageComponents::HTMLNavToolbar");
importer::import("UI", "Html", "pageComponents::HTMLRibbon");
importer::import("UI", "Html", "pageComponents::toolbarComponents::toolbarItem");

use \API\Resources\layoutManager;
use \UI\Html\DOM;
use \UI\Html\HTMLPage;
use \UI\Html\HTMLContent;
use \UI\Html\pageComponents\HTMLNavToolbar;
use \UI\Html\pageComponents\HTMLRibbon;
use \UI\Html\pageComponents\toolbarComponents\toolbarItem;

/**
 * HTML Module Page Builder
 * 
 * Builds the inner module page with a selected layout.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:42 (EET)
 * @revised	March 27, 2014, 23:07 (EET)
 */
class HTMLModulePage extends HTMLContent
{
	/**
	 * The page miscellaneous holder.
	 * 
	 * @type	string
	 */
	const MISC_HOLDER = "#pageMisc";
	
	/**
	 * The page title.
	 * 
	 * @type	string
	 */
	private $title = "";

	/**
	 * A pool for dropping auxiliary elements.
	 * 
	 * @type	DOMElement
	 */
	private $misc;
	
	/**
	 * The page layout id.
	 * 
	 * @type	string
	 */
	private $layoutID;
	
	/**
	 * Initializes the module page.
	 * 
	 * @param	string	$layoutID
	 * 		The page layout id.
	 * 
	 * @return	void
	 */
	public function __construct($layoutID = "")
	{
		$this->layoutID = $layoutID;
	}
	
	/**
	 * Creates a new Action Factory.
	 * 
	 * @param	string	$title
	 * 		The page title.
	 * 
	 * @param	string	$class
	 * 		The content extra class.
	 * 
	 * @param	boolean	$loadHtml
	 * 		Load inner html from external file.
	 * 
	 * @return	ActionFactory
	 * 		Returns an instance of the Action Factory.
	 */
	public function build($title = "", $class = "", $loadHtml = FALSE)
	{
		// Page Container
		parent::build("pageContent", "uiPageContent".($class == "" ? "" : " ".$class), $loadHtml);
		
		// Page Invisibles
		$this->misc = DOM::create("div", "", str_replace("#", "", self::MISC_HOLDER));
		DOM::prepend($this->get(), $this->misc);
		
		// Page Title
		$this->setPageTitle($title);
		
		// Create Layout (TEMP)
		if (!empty($this->layoutID))
			DOM::append($this->get(), layoutManager::load($this->layoutID));
		
		return $this;
	}
	
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
		
		// Set toolbar clear to false
		$this->clearToolbar = FALSE;
		
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
	 * 		The page holder. If empty, it gets the page holder.
	 * 
	 * @param	boolean	$clearToolbar
	 * 		If set to TRUE, clears the toolbar from any navigation items.
	 * 		TRUE by default.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getReport($holder = "", $clearToolbar = TRUE)
	{
		// Get page holder if empty
		if (empty($holder))
			$holder = HTMLPage::HOLDER;
		
		// Add title action
		if (!empty($this->title))
			parent::addReportAction("pageTitle.update", $this->title);
		
		// Add action to clear toolbar
		if ($clearToolbar)
		{
			$action = HTMLNavToolbar::getClearAction();
			parent::addReportAction($action['type'], $action['value']);
		}
		
		// Return report
		return parent::getReport($holder);
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
	 * Appends a DOMElement to the given section of the layout.
	 * 
	 * @param	string	$sectionID
	 * 		The section id.
	 * 
	 * @param	DOMElement	$content
	 * 		The element to be appended.
	 * 
	 * @return	HTMLModulePage
	 * 		The HTMLModulePage object.
	 */
	public function appendToSection($sectionID, $content)
	{
		layoutManager::append($this->layoutID, $sectionID, $content);
		
		return $this;
	}
	
	/**
	 * Appends a DOMElement to the auxiliary pool
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	HTMLModulePage
	 * 		The HTMLModulePage object.
	 */
	public function appendToMisc($element)
	{
		DOM::append($this->misc, $element);
		
		return $this;
	}
	
	/**
	 * Sets the page title.
	 * 
	 * @param	string	$title
	 * 		The new page title.
	 * 
	 * @return	void
	 */
	public function setPageTitle($title)
	{
		// Set module title
		$this->title = $title;
	}
	
	/**
	 * Gets the parent's filename.
	 * 
	 * @return	string
	 * 		The parent script name.
	 */
	protected function getParentFilename()
	{
		$stack = debug_backtrace();
		return $stack[3]['file'];
	}
}
//#section_end#
?>