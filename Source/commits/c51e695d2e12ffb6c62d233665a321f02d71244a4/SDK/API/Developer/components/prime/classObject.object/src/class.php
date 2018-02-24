<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime;

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
 * @namespace	\components\prime
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "misc::vcs");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\misc\vcs;

// Compatibility
importer::import("API", "Developer", "components::prime::classObject2");
use \API\Developer\components\prime\classObject2;

/**
 * Prime Class Object
 * 
 * Handles the basics of a class object handler including the capability of documentation.
 * 
 * @version	{empty}
 * @created	May 17, 2013, 14:30 (EEST)
 * @revised	September 20, 2013, 15:50 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\prime\classObject2 instead.
 */
class classObject extends classObject2
{
	/**
	 * The object's library name.
	 * 
	 * @type	string
	 */
	protected $libName;
	/**
	 * The object's package name.
	 * 
	 * @type	string
	 */
	protected $packageName;
	/**
	 * The object's namespace (separated by "::").
	 * 
	 * @type	string
	 */
	protected $nsName;
	
	/**
	 * The object's title.
	 * 
	 * @type	string
	 */
	protected $title;
	
	/**
	 * The root repository folder for the vcs.
	 * 
	 * @type	string
	 */
	protected $repRoot;
	/**
	 * The inner repository folder for the vcs.
	 * 
	 * @type	string
	 */
	protected $rep;
	
	/**
	 * Constructor method. Initializes the object's properties.
	 * 
	 * @param	string	$libName
	 * 		The object's library name.
	 * 
	 * @param	string	$packageName
	 * 		The object's package name.
	 * 
	 * @param	string	$nsName
	 * 		The object's namespace (separated by "_" or "::"). Optional, in case of no namespace.
	 * 
	 * @param	string	$objectName
	 * 		The object's name. Optinal, in case of creating a new object.
	 * 
	 * @return	void
	 */
	public function __construct($libName, $packageName, $nsName = "", $objectName = NULL)
	{
		// Set VCS Properties
		$this->fileType = parent::FILE_TYPE;
		
		// Set Library Properties
		$this->libName = $libName;
		$this->packageName = $packageName;
		$this->nsName = str_replace("_", "::", $nsName);
		
		// Set classObject2 variables
		$this->library = $libName;
		$this->package = $packageName;
		$this->namespace = str_replace("_", "::", $nsName);
		
		// Set Object Properties
		$this->name = $objectName;
	}
	
	/**
	 * Create a new classObject and initialize repository.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @param	string	$title
	 * 		The object's title. If empty, the name is used.
	 * 
	 * @return	classObject
	 * 		{description}
	 */
	public function create($name, $title)
	{
		$this->name = $name;
		$this->title = ($title == "" ? $name : $title);

		// Initialize VCS
		$this->VCS_initialize($this->repRoot, $this->rep, $this->name, parent::FILE_TYPE);

		// Create Object
		$this->VCS_createObject();

		// Update Object
		$this->createStructure();
		$this->updateSourceCode();
		
		// First Initial commit
		$this->commit("VCS Initial Auto-Commit.");
		
		return $this;
	}
	
	/**
	 * Commits this object to repository.
	 * 
	 * @param	string	$description
	 * 		The commit description.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function commit($description)
	{
		return $this->vcsBranch->commit($this->getWorkingBranch(), $description);
	}
	
	/**
	 * Gets the working branch of this object for the current user.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getWorkingBranch()
	{
		$classID = hash("md5", $this->libName.".".$this->packageName.".".$this->nsName.".".$this->name);
		return parent::getWorkingBranch($classID);
	}
	
	/**
	 * Sets the repository parameters for the object's vcs.
	 * 
	 * @param	string	$root
	 * 		The repository root folder.
	 * 
	 * @param	string	$repository
	 * 		The inner repository folder.
	 * 
	 * @return	void
	 */
	protected function setRepository($root, $rep)
	{
		$this->repRoot = $root;
		$this->rep = str_replace("::", "/", $rep);
	}
	
	/**
	 * Initialize the object's vcs.
	 * 
	 * @param	string	$repRoot
	 * 		The repository root folder.
	 * 
	 * @param	string	$repository
	 * 		The inner repository folder.
	 * 
	 * @return	void
	 */
	protected function init($repRoot, $repository)
	{
		// Set Repository
		$this->setRepository($repRoot, $repository);
		
		// Initialize VCS
		$this->VCS_initialize($this->repRoot, $this->rep, $this->name, parent::FILE_TYPE);
	}
	
	/**
	 * Creates the structure of the object by creating the necessary folders
	 * 
	 * @return	classObject
	 * 		{description}
	 */
	protected function createStructure()
	{
		$builder = new DOMParser();
		
		// Update Index Info
		$newBase = $this->buildIndexBase($builder);
		$this->vcsTrunk->updateBase($this->getWorkingBranch(), $newBase);

		// Update Structure (if not exists)
		$objectFolder = $this->vcsTrunk->getPath($this->getWorkingBranch());
		
		//_____ Create root folder
		if (!file_exists($objectFolder."/"))
			folderManager::create($objectFolder."/");
		
		//_____ Create src folder
		if (!file_exists($objectFolder.parent::SOURCE_FOLDER."/"))
			folderManager::create($objectFolder."/", parent::SOURCE_FOLDER);
		
		return $this;
	}
	
	/**
	 * Builds the object's index base.
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser to create the base.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function buildIndexBase($builder)
	{
		// Get the current base to update
		$newBase = $this->vcsTrunk->getBase($builder, $this->getWorkingBranch(), TRUE);

		// Clear base
		DOMParser::innerHTML($newBase, "");

		// Set Object Attributes (Name, Title)
		DOMParser::attr($newBase, "name", $this->name);
		DOMParser::attr($newBase, "title", $this->title);

		return $newBase;
	}
	
	/**
	 * Load all the index info
	 * 
	 * @return	void
	 */
	protected function loadInfo()
	{
		$parser = new DOMParser();
		$base = $this->vcsTrunk->getBase($parser, $this->getWorkingBranch());
		
		if (is_null($base))
			return FALSE;
		$this->setTitle($parser->attr($base, "title"));
	}
	
	/**
	 * Gets the object's path to the trunk.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getTrunkPath()
	{
		return $this->vcsTrunk->getPath($this->getWorkingBranch($this->name));
	}
	
	/**
	 * Get the object's path to the head branch.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getHeadPath()
	{
		return $this->vcsBranch->getHeadPath();
	}
	
	/**
	 * Sets the object's title.
	 * 
	 * @param	string	$value
	 * 		The new title.
	 * 
	 * @return	void
	 */
	public function setTitle($value)
	{
		$this->title = $value;
	}
	/**
	 * Gets the object's title.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Gets the object's library name.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getLibrary()
	{
		return $this->libName;
	}
	
	/**
	 * Gets the object's package name.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getPackage()
	{
		return $this->packageName;
	}
	
	/**
	 * Gets the object's namespace name as declared on the top of the class.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getNamespace()
	{
		return "\\".str_replace("::", "\\", $this->nsName);
	}
	
	/**
	 * Its the new constructor for the classObject2.
	 * 
	 * @param	string	$repository
	 * 		The object repository.
	 * 
	 * @param	boolean	$includeRelease
	 * 		Whether it will include the release files in the repository.
	 * 
	 * @return	void
	 */
	public function newConstruct($repository, $includeRelease)
	{
		// Init vcs
		$this->vcs = new vcs($repository, $includeRelease);
		$this->vcs->createStructure();
	}
	
	/**
	 * Updates the object to the new repository.
	 * 
	 * @return	void
	 */
	public function updateFullObject()
	{
		// Create item (if not exist)
		parent::create($this->name, $includeLibraryPath = TRUE, $innerPath = "");
		
		// Copy contents to the new trunk
		$oldTrunk = $this->getTrunkPath();
		$newTrunk = parent::getVCSTrunkPath();
		folderManager::copy($oldTrunk, $newTrunk, TRUE);
	}
}
//#section_end#
?>