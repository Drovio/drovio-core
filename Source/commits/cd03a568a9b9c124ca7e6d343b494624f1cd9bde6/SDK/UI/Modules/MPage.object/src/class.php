<?php
//#section#[header]
// Namespace
namespace UI\Modules;

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
 * @package	Modules
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Core", "RCPage");
importer::import("UI", "Core", "components::RNavToolbar");
importer::import("UI", "Core", "components::RTRibbon");
importer::import("UI", "Core", "components::toolbar::toolbarItem");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Modules", "MContent");

use \UI\Core\RCPage;
use \UI\Core\components\RNavToolbar;
use \UI\Core\components\RTRibbon;
use \UI\Core\components\toolbar\toolbarItem;
use \UI\Html\DOM;
use \UI\Modules\MContent;

/**
 * HTML Module Page Builder
 * 
 * Builds the inner module page as an entire page.
 * 
 * @version	{empty}
 * @created	June 11, 2014, 10:23 (EEST)
 * @revised	June 23, 2014, 12:34 (EEST)
 */
class MPage extends MContent
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
	 * Builds the module page.
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
	 * @return	MPage
	 * 		The module page object.
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
	 * 		The ribbon type. See HTMLRibbon for more information.
	 * 
	 * @param	string	$type
	 * 		The ribbon popup type (if float). See HTMLRibbon for more information.
	 * 
	 * @param	boolean	$pinnable
	 * 		Sets the ribbon pinnable. See HTMLRibbon for more information.
	 * 
	 * @param	integer	$index
	 * 		The item's position index.
	 * 
	 * @param	mixed	$ico
	 * 		Defines whether this item will have an ico. It can be used as DOMElement to set the ico.
	 * 
	 * @return	DOMElement
	 * 		The toolbar item.
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
			RTRibbon::addNavigation($item, $refID, $ribbonType, $type, $pinnable);
		}
		
		// Set toolbar clear to false
		$this->clearToolbar = FALSE;
		
		return $item;
	}
	
	/**
	 * Builds and returns a ribbon collection from RTRibbon.
	 * 
	 * @param	string	$id
	 * 		The collection's id.
	 * 
	 * @param	integer	$moduleID
	 * 		Sets the module id for auto loading.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name for auto loading.
	 * 
	 * @return	DOMElement
	 * 		The ribbon collection.
	 */
	public function getRibbonCollection($id, $moduleID = "", $viewName = "")
	{
		return RTRibbon::getCollection($id, $moduleID, $viewName);
	}
	
	/**
	 * Get the ServerReport of this HTML Module Page or the object holder.
	 * It depends on the call.
	 * 
	 * @param	string	$holder
	 * 		The page holder. If empty, it gets the page holder.
	 * 
	 * @param	boolean	$clearToolbar
	 * 		If set to TRUE, clears the toolbar from any navigation items.
	 * 		TRUE by default.
	 * 
	 * @return	mixed
	 * 		The server report or the object holder.
	 */
	public function getReport($holder = "", $clearToolbar = TRUE)
	{
		// Get page holder if empty
		if (empty($holder))
			$holder = RCPage::HOLDER;
		
		// Add title action
		if (!empty($this->title))
			parent::addReportAction("pageTitle.update", $this->title);
		
		// Add action to clear toolbar
		if ($clearToolbar)
		{
			$action = RNavToolbar::getClearAction();
			parent::addReportAction($action['type'], $action['value']);
		}
		
		// Return report
		return parent::getReport($holder);
	}
	
	/**
	 * Gets the RCPage module holder selector.
	 * 
	 * @return	string
	 * 		The RCPage holder
	 */
	public static function getPageHolder()
	{
		return RCPage::HOLDER;
	}
	
	/**
	 * Appends a DOMElement to the auxiliary pool
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	MPage
	 * 		The MPage object.
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
	 * Gets the parent's filename for loading the html from external file.
	 * 
	 * @return	string
	 * 		The parent script name.
	 */
	protected function getParentFilename()
	{
		$stack = debug_backtrace();
		return $stack[4]['file'];
	}
}
//#section_end#
?>