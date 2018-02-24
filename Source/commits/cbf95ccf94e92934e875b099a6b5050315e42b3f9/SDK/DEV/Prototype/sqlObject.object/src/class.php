<?php
//#section#[header]
// Namespace
namespace DEV\Prototype;

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
 * @package	Prototype
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Tools", "parsers::phpParser");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Tools\parsers\phpParser;

/**
 * Abstract SQL query object.
 * 
 * Manages a smart sql object.
 * 
 * @version	0.1-1
 * @created	March 31, 2014, 15:08 (EEST)
 * @updated	October 7, 2015, 18:54 (EEST)
 */
abstract class sqlObject
{
	/**
	 * The object id.
	 * 
	 * @type	string
	 */
	protected $id;
	
	/**
	 * The query name.
	 * 
	 * @type	string
	 */
	protected $name;
	
	/**
	 * The query object title.
	 * 
	 * @type	string
	 */
	protected $title;
	
	/**
	 * The query object description.
	 * 
	 * @type	string
	 */
	protected $description;
	
	/**
	 * The query object attributes.
	 * 
	 * @type	array
	 */
	protected $attributes;
	
	/**
	 * Initializes the object's properties
	 * 
	 * @param	string	$id
	 * 		The existing's object id.
	 * 		Leave empty for new objects.
	 * 
	 * @return	void
	 */
	public function __construct($id = NULL)
	{
		$this->title = "";
		$this->description = "";
		$this->attributes = array();
		
		if (!empty($id))
		{
			$this->id = $id;
			$this->name = $this->getName($this->id);
		}
		else
		{
			// Get a random id
			$id = mt_rand() + microtime(true);
			$id = str_replace(".", "", $id);
			$this->id = $id;
		}
	}
	
	/**
	 * Abstract function for getting the object's full path from the inherited class.
	 * 
	 * @return	string
	 * 		The object's full path.
	 */
	abstract protected function getObjectFullPath();

	/**
	 * Creates a new sql query object.
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
		// Create filename
		$this->title = $title;
		$this->description = $description;
		$this->name = $this->getName($this->id);
		$objectFolder = $this->getObjectFullPath();
		folderManager::create($objectFolder);
		
		// Update item
		return $this->update($this->title, "", $this->description);
	}
	
	/**
	 * Updates the query object.
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
	 * 		An array of attributes for the programmer to use the query.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($title, $query = "", $description = "", $attributes = array())
	{
		// Update properties
		$this->title = $title;
		$query = phpParser::clear($query);
		$this->description = $description;
		$this->attributes = array();
		
		// Get attributes from query
		$expr = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
		preg_match_all($expr, $query, $queryAttributes);
		// Update attributes with given values
		foreach ($queryAttributes[1] as $attr)
			$this->attributes[$attr] = (isset($attributes[$attr]) ? $attributes[$attr] : "");
		
		// Get attributes from query
		$expr = '/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/';
		preg_match_all($expr, $query, $queryAttributes);
		// Update attributes with given values
		foreach ($queryAttributes[1] as $attr)
			$this->attributes[$attr] = (isset($attributes[$attr]) ? $attributes[$attr] : "");
		
		// Get file path
		$filePath = $this->getObjectFullPath();
		fileManager::create($filePath."/query.sql", $query, TRUE);
		
		// Update Index Info
		$this->updateIndexInfo();
		
		return TRUE;
	}
	
	/**
	 * Updates the query's index information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	protected function updateIndexInfo()
	{
		// Get file path
		$filePath = $this->getObjectFullPath();
		
		// Update new vcs item index info
		$parser = new DOMParser();
		try
		{
			$parser->load($filePath."/index.xml");
			$root = $parser->evaluate("//query")->item(0);
		}
		catch (Exception $ex)
		{
			$root = $parser->create("query", "", $this->id);
			$parser->append($root);
			$parser->save($filePath, "index.xml");
		}
		
		// Update values
		$title = $parser->evaluate("title", $root)->item(0);
		if (empty($title))
		{
			$title = $parser->create("title");
			$parser->append($root, $title);
		}
		$parser->nodeValue($title, $this->title);
		
		$desc = $parser->evaluate("description", $root)->item(0);
		if (empty($desc))
		{
			$desc = $parser->create("description");
			$parser->append($root, $desc);
		}
		$parser->nodeValue($desc, $this->description);
		
		$access = $parser->evaluate("access", $root)->item(0);
		if (empty($access))
		{
			$access = $parser->create("access");
			$parser->append($root, $access);
		}
		$parser->nodeValue($access, $this->accessLevel);
		
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
		return $parser->update();
	}
	
	/**
	 * Gets the executable query code.
	 * 
	 * @return	string
	 * 		The query sql code.
	 */
	public function getQuery()
	{
		// Get file path
		$filePath = $this->getObjectFullPath();
		
		// Get plain query
		$query = fileManager::get($filePath."/query.sql");
		$query = phpParser::clear($query);
		
		return $query;
	}
	
	/**
	 * Loads all the query's information from the index.
	 * 
	 * @return	void
	 */
	protected function loadIndexInfo()
	{
		// Get file path
		$filePath = $this->getObjectFullPath();
		
		$parser = new DOMParser();
		try
		{
			$parser->load($filePath."/index.xml", FALSE);
			$root = $parser->evaluate("//query")->item(0);
		}
		catch (Exception $ex)
		{
			return FALSE;
		}

		$this->name = $parser->attr($root, "id");
		$this->title = $parser->evaluate("title", $root)->item(0)->nodeValue;
		$this->description = $parser->evaluate("description", $root)->item(0)->nodeValue;

		// Attributes
		$attributes = $parser->evaluate("attributes/attr", $root);
		$this->attributes = array();
		foreach($attributes as $attribute)
			$this->attributes[$attribute->getAttribute("id")] = $attribute->nodeValue;
	}
	
	/**
	 * Gets the query's filename.
	 * 
	 * @param	string	$id
	 * 		The query id.
	 * 
	 * @return	string
	 * 		The query's filename.
	 */
	public static function getName($id)
	{
		return "q.".hash("md5", "_q_".$id, FALSE);
	}
	
	/**
	 * Gets the query id.
	 * 
	 * @return	string
	 * 		The query id.
	 */
	public function getID()
	{
		return $this->id;
	}
	
	/**
	 * Gets the query title.
	 * 
	 * @return	string
	 * 		The query title.
	 */
	public function getTitle()
	{
		$this->loadIndexInfo();
		return $this->title;
	}

	/**
	 * Gets the query description.
	 * 
	 * @return	string
	 * 		The query description.
	 */
	public function getDescription()
	{
		$this->loadIndexInfo();
		return $this->description;
	}
	
	/**
	 * Gets the query attributes.
	 * 
	 * @return	array
	 * 		The query attributes array
	 */
	public function getAttributes()
	{
		$this->loadIndexInfo();
		return $this->attributes;
	}
	
	/**
	 * Gets the plain query without the attributes resolving values.
	 * 
	 * @return	void
	 */
	public function getPlainQuery()
	{
		// Get file path
		$filePath = $this->getObjectFullPath();
		
		// Get query
		$query = fileManager::get($filePath."/query.sql");
		$query = phpParser::clear($query);
		return trim($query);
	}
}
//#section_end#
?>