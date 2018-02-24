<?php
//#section#[header]
// Namespace
namespace DEV\Core\sql;

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
 * @package	Core
 * @namespace	\sql
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Core", "sql/sqlDomain");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Tools", "parsers/phpParser");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Prototype", "sqlObject");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Core\sql\sqlDomain;
use \DEV\Core\coreProject;
use \DEV\Tools\parsers\phpParser;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Prototype\sqlObject;

/**
 * Developer's database sql query manager.
 * 
 * Manages all database queries.
 * 
 * @version	0.1-4
 * @created	April 1, 2014, 11:25 (EEST)
 * @updated	April 7, 2015, 12:01 (EEST)
 */
class sqlQuery extends sqlObject
{
	/**
	 * The query domain.
	 * 
	 * @type	string
	 */
	private $domain;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the object's vcs and calls the parent's constructor.
	 * 
	 * @param	string	$domain
	 * 		The query domain.
	 * 
	 * @param	string	$id
	 * 		The query id.
	 * 		If empty, one is generated by random for creating a new query.
	 * 
	 * @return	void
	 */
	public function __construct($domain, $id = NULL)
	{
		// Init project and vcs
		$this->vcs = new vcs(coreProject::PROJECT_ID);
		
		// Initilize parent
		$this->domain = $domain;
		$id = str_replace("q.", "", $id);
		$id = str_replace("q_", "", $id);
		parent::__construct($id);
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
	 * Creates a new sql query.
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
	public function create($title, $description)
	{
		// Normalize domain path
		$nsdomain = str_replace(".", "/", $this->domain);
		
		// Add index entry
		$proceed = sqlDomain::addQuery($this->domain, "q.".$this->id, $title);
		if (!$proceed)
			return FALSE;
		
		// Create new vcs object and files
		$itemID = $this->getItemID();
		$itemPath = "/SQL/".$nsdomain."/";
		$itemName = $this->getName($this->id).".sql";
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create new sql object
		return parent::create($title, $description);
	}
	
	/**
	 * Remove the sql query from the repository.
	 * Use the sqlDomain function instead to remove the page from the map.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove index entry
		$proceed = sqlDomain::removeQuery($this->domain, "q.".$this->id);
		if (!$proceed)
			return FALSE;
		
		// Create new vcs object and files
		$itemID = $this->getItemID();
		return $this->vcs->deleteItem($itemID);
	}
	
	/**
	 * Gets the item id for the vcs.
	 * 
	 * @return	string
	 * 		The vcs item id.
	 */
	private function getItemID()
	{
		return "q".hash("md5", $this->id, FALSE);
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
		
		// Update info in map xml (if title is different)
		if ($this->getTitle() != $title)
			sqlDomain::updateQuery("q.".$this->id, $title);
		
		// Update object
		return parent::update($title, $query, $description, $attributes);
	}
	
	/**
	 * Gets the executable query code.
	 * 
	 * @param	array	$attr
	 * 		An associative array of attributes for the query.
	 * 
	 * @return	string
	 * 		The query sql code.
	 */
	public function getQuery($attr = array())
	{
		// Get file path and validate with query's domain
		$filePath = $this->getObjectFullPath();
		$nsdomain = str_replace(".", "/", $this->domain);
		if (strpos($filePath, $nsdomain) === FALSE)
			throw new Exception("Database Query '$this->domain -> $this->id' not found.");
		
		// Return query
		return parent::getQuery($attr);
	}
}
//#section_end#
?>