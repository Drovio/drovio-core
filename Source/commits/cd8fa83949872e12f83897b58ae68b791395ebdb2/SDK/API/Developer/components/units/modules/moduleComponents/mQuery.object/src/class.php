<?php
//#section#[header]
// Namespace
namespace API\Developer\components\units\modules\moduleComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\units\modules\moduleComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Module Query Manager
 * 
 * Manages the module's sql queries.
 * 
 * @version	{empty}
 * @created	November 30, 2013, 18:20 (EET)
 * @revised	April 2, 2014, 11:48 (EEST)
 * 
 * @deprecated	Use \DEV\Modules\components\mQuery instead.
 */
class mQuery
{
	/**
	 * The query id.
	 * 
	 * @type	string
	 */
	private $id;
	
	/**
	 * The module's queries directory root.
	 * 
	 * @type	string
	 */
	private $queriesRoot;
	
	/**
	 * The query directory.
	 * 
	 * @type	string
	 */
	private $queryDirectory;
	
	/**
	 * The module vcs object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The module's itemID.
	 * 
	 * @type	string
	 */
	private $itemID;
	
	/**
	 * The query attributes.
	 * 
	 * @type	array
	 */
	private $attributes;
	
	/**
	 * Initializes the module query.
	 * 
	 * @param	vcs	$vcs
	 * 		The module's vcs manager object.
	 * 
	 * @param	string	$itemID
	 * 		The module's itemid.
	 * 
	 * @param	string	$queriesRoot
	 * 		The module's queries root.
	 * 
	 * @param	string	$queryID
	 * 		The query id.
	 * 
	 * @return	void
	 */
	public function __construct($vcs, $itemID, $queriesRoot, $queryID = "")
	{
		// Put your constructor method code here.
		$this->queriesRoot = $viewsRoot;
		$this->vcs = $vcs;
		$this->itemID = $itemID;
		
		if (!empty($queryID))
		{
			$this->id = $queryID;
			$this->queryDirectory = $this->queriesRoot."/".$this->getDirectoryName($this->id);
			$this->loadInfo();
		}
	}
	
	/**
	 * Loads the information (attributes) from the index.
	 * 
	 * @return	void
	 */
	protected function loadInfo()
	{
		$parser = new DOMParser();
		try
		{
			$parser->load($this->queryDirectory."/index.xml", FALSE);
			$root = $parser->find($this->id);
		}
		catch (Exception $ex)
		{
			return FALSE;
		}
		
		// Load Attributes
		$attributes = $parser->evaluate("attributes/attr", $root);
		$this->attributes = array();
		foreach($attributes as $attribute)
			$this->attributes[$attribute->getAttribute("id")] = $attribute->nodeValue;
	}
	
	/**
	 * Creates a new sql query with the given query id.
	 * 
	 * @param	string	$queryID
	 * 		The query id.
	 * 
	 * @return	void
	 */
	public function create($queryID)
	{
		// Update vcs item
		$this->vcs->updateItem($this->itemID);
		
		// Initialize variables
		$this->id = $viewID;
		$this->queryDirectory = $this->queriesRoot."/".$this->getDirectoryName($this->id);
		
		// Create structure
		folderManager::create($this->queryDirectory, TRUE);
		
		$parser = new DOMParser();
		$root = $parser->create("query", "", $this->id);
		$parser->append($root);
		$parser->save($this->queryDirectory."/index.xml");
		
		// Update content
		$this->update();
	}
	
	/**
	 * Updates the query's sql and the attributes.
	 * 
	 * @param	string	$query
	 * 		The sql query.
	 * 		Use attributes in the form of {attr}.
	 * 
	 * @param	array	$attrs
	 * 		The existing attributes' description.
	 * 
	 * @return	void
	 */
	public function update($query = "", $attrs = array())
	{
		// Update vcs item
		$this->vcs->updateItem($this->itemID);
		
		// Get attributes from query
		$expr = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
		preg_match_all($expr, $query, $queryAttributes);
		// Update attributes with given values
		foreach ($queryAttributes[1] as $attr)
			$this->attributes[$attr] = (isset($attrs[$attr]) ? $attrs[$attr] : "");
		
		// Get attributes from query
		$expr = '/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/';
		preg_match_all($expr, $query, $queryAttributes);
		// Update attributes with given values
		foreach ($queryAttributes[1] as $attr)
			$this->attributes[$attr] = (isset($attrs[$attr]) ? $attrs[$attr] : "");
		
		// Update sql code
		fileManager::create($this->queryDirectory."/query.sql", $query, TRUE);
		
		// Update Index Info
		$this->updateIndexInfo();
	}
	
	/**
	 * Updates the query index with the attributes.
	 * 
	 * @return	void
	 */
	private function updateIndexInfo()
	{
		// Update new vcs item index info
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		$parser = new DOMParser();
		try
		{
			$parser->load($itemTrunkPath."/index.xml");
			$root = $parser->evaluate("//query")->item(0);
		}
		catch (Exception $ex)
		{
			$root = $parser->create("query", "", $this->id);
			$parser->append($root);
			$parser->save($itemTrunkPath, "index.xml");
		}
		
		$attributes = $parser->evaluate("attributes", $root)->item(0);
		if (empty($attributes))
		{
			$attributes = $parser->create("attributes");
			$parser->append($root, $attributes);
		}
		$parser->innerHTML($attrs, "");
		foreach ($this->attributes as $key => $value)
		{
			$attr = $parser->create("attr", $value, $key);
			$parser->append($attributes, $attr);
		}		
		
		// Update parser
		$parser->update();
	}
	
	/**
	 * Gets the query for execution, replacing the attributes with the given values.
	 * 
	 * @param	array	$attr
	 * 		The array of attributes, given the key as the attribute name and the value as the attribute value.
	 * 		They will replace the attributes in the query.
	 * 
	 * @return	string
	 * 		The executable query.
	 */
	public function getQuery($attr = array())
	{
		// Get plain query
		$query = $this->getPlainQuery();
		$query = phpParser::clear($query);
		
		// Replace Attributes
		foreach ($attr as $key => $value)
		{
			$query = str_replace("$".$key, $value, $query);
			$query = str_replace("{".$key."}", $value, $query);
		}
		
		return $query;
	}
	
	/**
	 * Gets the sql plain query with no further edit.
	 * 
	 * @return	string
	 * 		The sql query.
	 */
	public function getPlainQuery()
	{
		$query = fileManager::get($this->queryDirectory."/query.sql");
		$query = phpParser::clear($query);
		return trim($query);
	}
	
	/**
	 * Gets the query's directory name.
	 * 
	 * @param	string	$id
	 * 		The query id.
	 * 
	 * @return	string
	 * 		The query directory name.
	 */
	private static function getDirectoryName($id)
	{
		return $id.".sql";
	}
}
//#section_end#
?>