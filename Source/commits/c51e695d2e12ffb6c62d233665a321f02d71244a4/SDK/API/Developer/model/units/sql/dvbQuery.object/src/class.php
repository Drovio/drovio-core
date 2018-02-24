<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\sql;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\model\units\sql
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "model::version::vcs");
importer::import("API", "Developer", "model::units::sql::dvbLib");

use \API\Content\filesystem\fileManager;
use \API\Content\filesystem\folderManager;
use \API\Platform\DOM\DOMParser;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\model\version\vcs;
use \API\Developer\model\units\sql\dvbLib;

/**
 * Developer's Database Query
 * 
 * Handles the system's database queries.
 * 
 * @version	{empty}
 * @created	June 19, 2013, 10:37 (EEST)
 * @revised	July 3, 2013, 16:10 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\sql\dvbQuery instead.
 */
class dvbQuery extends vcs
{
	/**
	 * The query's file type
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "sql";
	
	/**
	 * The query's id
	 * 
	 * @type	integer
	 */
	protected $id;
	/**
	 * The production directory (latest)
	 * 
	 * @type	string
	 */
	protected $prdDirectory;
	
	/**
	 * Indicates whether the query is a query transaction.
	 * 
	 * @type	boolean
	 */
	public $isTransaction;
	/**
	 * The query's access level (0...5)
	 * 
	 * @type	integer
	 */
	public $access_level;
	
	/**
	 * The query's title
	 * 
	 * @type	string
	 */
	protected $title;
	/**
	 * The query's description
	 * 
	 * @type	string
	 */
	protected $description;
	/**
	 * The query's attributes
	 * 
	 * @type	array
	 */
	protected $attributes;
	
	/**
	 * The query as the developer writes it.
	 * 
	 * @type	string
	 */
	protected $plain_query;
	/**
	 * The query's domain as a full name (separated by ".")
	 * 
	 * @type	string
	 */
	protected $domain;
	/**
	 * The hash function being used to transform the query's file name.
	 * 
	 * @type	string
	 */
	private $hashFunction = 'md5';
	
	/**
	 * Constructor method.
	 * Initializes the object's properties.
	 * 
	 * @param	string	$domain
	 * 		The query's full domain
	 * 
	 * @param	string	$id
	 * 		The query's id.
	 * 
	 * @return	void
	 */
	public function __construct($domain, $id = NULL)
	{
		//Initalize and set empty dbQuery object variables
		$this->id = (is_null($id) ? rand() : str_replace("q.", "", $id));
		
		$this->domain = $domain;
		
		$this->access_level = 1;
		$this->isTransaction = 1;
		$this->plain_query = "";
		$this->attributes = array();
		
		$nsdomain = str_replace('.', '/', $this->domain);
		$this->prdDirectory = dvbLib::PATH."/".$nsdomain."/";
	}	

	/**
	 * Create a query in the sql library
	 * 
	 * @param	string	$description
	 * 		The query's description
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @throws	Exception
	 */
	public function create($description)
	{
		// Normalize domain path
		$nsdomain = str_replace(".", "/", $this->domain);
		
		// Check if exists
		if (!is_dir(systemRoot.dvbLib::REPOSITORY_PATH."/".$nsdomain))
			throw new Exception("Domain '$domain' not found.");
		
		// Create filename
		$this->name = $this->get_name($this->id);
		$this->description = $description;
		
		// Initialize VCS
		$this->VCS_initialize("/Library/SQL/".$nsdomain, $this->name, self::FILE_TYPE);
		
		// Create Query Object
		$this->VCS_create_object();
		
		return TRUE;
	}
	
	/**
	 * Delete a query from the sql library
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @throws	Exception
	 */
	public static function delete()
	{
		// Normalize domain path
		$nsdomain = str_replace(".", "/", $domain);
			
		if (!is_dir(systemRoot.dvbLib::REPOSITORY_PATH."/".$nsdomain."/q.".$id))
			throw new Exception("Query '$domain' -> '$id' not found.");
			
		// Initialize VCS
		$this->name = $this->get_name($id);
		$this->VCS_initialize("/Library/SQL/".$nsdomain, $this->name, self::FILE_TYPE);
		
		// Remove Repository Path
		$this->VCS_remove();
		
		// Remove Production File
		$fileName = dvbQuery::get_name($id);
		fileManager::remove(systemRoot.self::PATH."/".$nsdomain."/".$fileName.".php");
		
		return TRUE;
	}
	
	/**
	 * Updates the query's contents
	 * 
	 * @param	string	$branch
	 * 		The vcs branch where the update will be performed
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function update($branch = "")
	{
		$builder = new DOMParser();
		
		// Update Index Info
		$newBase = $this->get_indexInfo($builder);
		$this->vcsTrunk->update_indexBase($builder, $newBase, $branch);
		
		// Save plain query
		$file = $this->vcsTrunk->get_itemPath($branch);
		fileManager::create($file, $this->plain_query);
		
		// Create and Export executable query
		$this->export();
		
		return TRUE;
	}
	
	/**
	 * Exports the query to latest
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function export()
	{
		// Create files
		$fileName = $this->name;
		$execQuery = $this->buildExecutable();
		
		// Create query file
		fileManager::create(systemRoot.$this->prdDirectory.$this->name.".php", $execQuery);
		
		// Update Index File
		$parser = new DOMParser();
		$parser->load($this->prdDirectory."index.xml", TRUE);
		
		// Get Base
		$base = $parser->evaluate("//queries")->item(0);
		
		// Get query node if exists
		$oldQueryElement = $parser->find("q.".$this->id);
		$newQueryElement = $parser->create("q", "", "q.".$this->id);
		$parser->attr($newQueryElement, "transaction", $this->isTransaction);
		$parser->attr($newQueryElement, "level", $this->access_level);
		
		if (!is_null($oldQueryElement))
			$parser->replace($oldQueryElement, $newQueryElement);
		else
			$parser->append($base, $newQueryElement);
			
		// Save File
		return $parser->save(systemRoot.$this->prdDirectory, "index.xml", TRUE);
	}
	
	/**
	 * Get the query's index base
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser that is used to parse the file
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function get_indexInfo($builder)
	{
		// Get the current base to update
		$newBase = $this->vcsTrunk->get_base($builder);
		
		// Clear base
		DOMParser::innerHTML($newBase, "");
		
		//_____ Seed
		DOMParser::attr($newBase, "seed", $this->id);
		
		//_____ Title
		$title_element = $builder->create("title", $this->title);
		DOMParser::append($newBase, $title_element);
		
		//_____ Description
		$description_element = $builder->create("description", $this->description);
		DOMParser::append($newBase, $description_element);
		
		//_____ Access Level
		$level_element = $builder->create("access_level", $this->access_level);
		DOMParser::append($newBase, $level_element);
		
		//_____ Transaction Indicator
		$transaction_element = $builder->create("isTransaction", $this->isTransaction);
		DOMParser::append($newBase, $transaction_element);
		
		//_____ Attributes
		$attributes_element = $builder->create("attributes");
		DOMParser::append($newBase, $attributes_element);
		
		foreach ($this->attributes as $key => $value)
		{
			$attribute = $builder->create("attr", "", $key);
			DOMParser::attr($attribute, "name", $value);
			DOMParser::append($attributes_element, $attribute);
		}
		
		return $newBase;
	}
	
	/**
	 * Load the query's information and content
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	void
	 */
	public function load($branch = "")
	{
		// Check existance
		if (!is_dir(systemRoot.$this->vcsDirectory))
			throw new Exception("Database Query '$domain -> $id' not found.");
		
		// Normalize domain path
		$nsdomain = str_replace(".", "/", $this->domain);
		
		// Initialize VCS
		$this->name = $this->get_name($this->id);
		$this->VCS_initialize("/Library/SQL/".$nsdomain, $this->name, self::FILE_TYPE);
			
		// Load Query Info
		$this->load_indexInfo($branch);
		
		// TEMP
		// Create Release Branch
		$this->vcsBranch->create("release");

		//_____ Plain Query
		$file = $this->vcsTrunk->get_itemPath($branch);
		$this->plain_query = fileManager::get_contents($file);
	}
	
	/**
	 * Loads all the index info of the query
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	void
	 */
	protected function load_indexInfo($branch = "")
	{
		$parser = new DOMParser();
		$base = $this->vcsTrunk->get_base($parser, $branch);
		
		if (is_null($base))
			return FALSE;
			
		$this->name = $parser->attr($base, "id");
		$this->title = $parser->evaluate("title", $base)->item(0)->nodeValue;
		$this->description = $parser->evaluate("description", $base)->item(0)->nodeValue;
		$this->access_level = $parser->evaluate("access_level", $base)->item(0)->nodeValue;
		$this->isTransaction = $parser->evaluate("isTransaction", $base)->item(0)->nodeValue;
		
		//_____ Query attributes
		$attributes = $parser->evaluate("attr", $base);
		$this->attributes = array();
		foreach($attributes as $attribute)
			$this->attributes[$attribute->getAttribute("id")] = $attribute->getAttribute("name");
	}
	
	/**
	 * Returns the query's filename
	 * 
	 * @param	string	$id
	 * 		The query's name
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_name($id)
	{
		return "q.".hash($this->hashFunction, "_q_".$id, FALSE);
	}
	
	/**
	 * Builds the executable query from plain
	 * 
	 * @return	string
	 * 		{description}
	 */
	private function buildExecutable()
	{
		//Get plain query
		$query = $this->plain_query;
		
		// Set attributes
		$expr = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
		$replace = '$attr[$1]';
		$exec_query = preg_replace($expr, $replace, $query);
		
		// Form query
		$exec_query = phpParser::get_variable("query").' = "'.$exec_query.'";';
		
		// Build Entire Source Code as PHP Code
		$query = phpParser::wrap($exec_query);
		
		return $query;
	}
	
	
	/**
	 * Set the query's title
	 * 
	 * @param	string	$value
	 * 		The title value
	 * 
	 * @return	void
	 */
	public function set_title($value)
	{
		$this->title = $value;
	}
	
	/**
	 * Set the query's description
	 * 
	 * @param	string	$value
	 * 		The description value
	 * 
	 * @return	void
	 */
	public function set_description($value)
	{
		$this->description = $value;
	}
	
	/**
	 * Set the query's access level
	 * 
	 * @param	integer	$value
	 * 		The access level
	 * 
	 * @return	void
	 */
	public function set_accessLevel($value)
	{
		$this->access_level = $value;
	}
	
	/**
	 * Set the query's transaction indicator
	 * 
	 * @param	boolean	$value
	 * 		The transaction indicator value
	 * 
	 * @return	void
	 */
	public function set_isTransaction($value)
	{
		$this->isTransaction = $value;
	}
	
	/**
	 * Set the query's attributes
	 * 
	 * @param	array	$attr
	 * 		The query's attributes
	 * 
	 * @return	void
	 */
	public function set_attributes($attr = array())
	{
		$this->attributes = $attr;
	}
	
	/**
	 * Set the query's code
	 * 
	 * @param	string	$value
	 * 		The query's code
	 * 
	 * @return	void
	 */
	public function set_query($value)
	{
		$this->plain_query = $value;
	}
	
	/**
	 * Get the query's id
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_id()
	{
		return $this->id;
	}
	/**
	 * Get the query's title
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_title()
	{
		return $this->title;
	}
	/**
	 * Get the query's description
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_description()
	{
		return $this->description;
	}
	/**
	 * Get the query's access level
	 * 
	 * @return	integer
	 * 		{description}
	 */
	public function get_accessLevel()
	{
		return $this->access_level;
	}
	/**
	 * Get the query's transaction indicator
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function get_isTransaction()
	{
		return $this->isTransaction;
	}
	/**
	 * Get the query's attributes
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function get_attributes()
	{
		return $this->attributes;
	}
	/**
	 * Get the query's code
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_query()
	{
		return $this->plain_query;
	}
}
//#section_end#
?>