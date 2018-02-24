<?php
//#section#[header]
// Namespace
namespace UI\Navigation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Navigation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Navigation", "treeView");
importer::import("UI", "Html", "DOM");

use \UI\Navigation\treeView;
use \UI\Html\DOM;

/**
 * File treeView.
 * 
 * Specific treeView with a refresh button for viewing file-like content.
 * 
 * @version	{empty}
 * @created	May 8, 2013, 14:31 (EEST)
 * @revised	September 16, 2013, 14:28 (EEST)
 * 
 * @deprecated	Functionality no longer used. Modules can have such functionality now.
 */
class fileTreeView extends treeView
{
	/**
	 * Builds the treeView along with all other tools.
	 * 
	 * @param	string	$id
	 * 		The id of the treeView.
	 * 
	 * @param	string	$class
	 * 		The extra class of the treeView
	 * 
	 * @param	boolean	$sorting
	 * 		Defines whether the fileTreeView will be sortable.
	 * 
	 * @return	fileTreeView
	 * 		{description}
	 */
	public function build($id = "", $class = "", $sorting = FALSE)
	{
		// Build tree view
		parent::build($id, $class, $sorting);
		
		// Get Holder
		$view = $this->get();
		
		// Build toolbar
		$treeViewTools = DOM::create("div", "", "", "ftvToolbar");
		DOM::prepend($view, $treeViewTools);
		$refreshTool = DOM::create("div", "Refresh", "", "ftvTool refresh");
		DOM::append($treeViewTools, $refreshTool);
		$collapseTool = DOM::create("div", "Collapse", "", "ftvTool collapse");
		DOM::append($treeViewTools, $collapseTool);
		
		return $this;
	}
	
	/**
	 * Creates the root list
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$sorting
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use build()->get() instead.
	 */
	public function get_view($id = "", $class = "", $sorting = FALSE)
	{
		return $this->build($id, $class, $sorting)->get();
	}
}
//#section_end#
?>