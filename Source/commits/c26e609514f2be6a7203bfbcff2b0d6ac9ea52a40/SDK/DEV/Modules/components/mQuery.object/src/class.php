<?php
//#section#[header]
// Namespace
namespace DEV\Modules\components;

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
 * @package	Modules
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Prototype", "sqlObject");

use \DEV\Prototype\sqlObject;

/**
 * Module Query Manager
 * 
 * Manages the module's sql queries.
 * 
 * @version	{empty}
 * @created	April 2, 2014, 11:47 (EEST)
 * @revised	June 13, 2014, 10:13 (EEST)
 */
class mQuery extends sqlObject
{
	/**
	 * The module's queries root.
	 * 
	 * @type	string
	 */
	private $queriesRoot;
	
	/**
	 * The module vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The module id.
	 * 
	 * @type	integer
	 */
	private $moduleID;
	
	/**
	 * Initializes the view.
	 * 
	 * @param	vcs	$vcs
	 * 		The module vcs manager object.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$queriesRoot
	 * 		The query's root directory.
	 * 
	 * @param	string	$queryID
	 * 		The query id.
	 * 		(Leave empty for new queries).
	 * 
	 * @return	void
	 */
	public function __construct($vcs, $moduleID, $queriesRoot, $queryID = "")
	{
		// Put your constructor method code here.
		$this->queriesRoot = $queriesRoot;
		$this->vcs = $vcs;
		$this->moduleID = $moduleID;
		
		// Parent constructor
		parent::__construct($queryID);
	}
	
	/**
	 * Implementation of the abstract function from the parent class.
	 * Returns the full path of the object inside the repository.
	 * 
	 * @return	string
	 * 		The object's full path.
	 */
	protected function getObjectFullPath()
	{
		$itemID = $this->getItemID();
		return $this->vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Create a new query.
	 * 
	 * @param	string	$viewID
	 * 		The query id.
	 * 
	 * @param	string	$title
	 * 		The query title.
	 * 
	 * @param	string	$description
	 * 		The query description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($viewID, $title, $description)
	{
		// Initialize variables
		$this->id = $viewID;
		
		// Create vcs item
		$itemID = $this->getItemID();
		$itemPath = "/".$this->queriesRoot."/";
		$itemName = $this->getDirectoryName($this->id);
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create new sql object
		return parent::create($title, $description);
	}
	
	/**
	 * Updates the query's repository item and calls the parent function to update the sql object.
	 * 
	 * @param	string	$title
	 * 		The query title.
	 * 
	 * @param	string	$query
	 * 		The SQL plain query.
	 * 
	 * @param	string	$description
	 * 		The query description.
	 * 
	 * @param	array	$attributes
	 * 		An array of description for each attribute in the query.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($title, $query = "", $description = "", $attributes = array())
	{
		// Update new vcs item info
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Update object
		return parent::update($title, $query, $description, $attributes);
	}
	
	/**
	 * Removes the query from the vcs repository.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove object from vcs
		$itemID = $this->getItemID();
		return $this->vcs->deleteItem($itemID);
	}
	
	/**
	 * Gets the query's vcs item id.
	 * 
	 * @return	string
	 * 		The item id.
	 */
	private function getItemID()
	{
		return "mq".md5("query".$this->moduleID."_".$this->id);
	}
	
	/**
	 * Gets the query's directory name.
	 * 
	 * @param	string	$id
	 * 		The query id.
	 * 
	 * @return	string
	 * 		The directory name.
	 */
	private static function getDirectoryName($id)
	{
		return $id.".query";
	}
}
//#section_end#
?>