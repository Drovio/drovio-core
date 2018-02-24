<?php
//#section#[header]
// Namespace
namespace API\Developer\components\modules;

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
 * @namespace	\components\modules
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Developer", "components::modules::moduleGroup");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Resources", "DOMParser");

use \ESS\Protocol\client\BootLoader;
use \API\Comm\database\connections\interDbConnection;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\versionControl\vcsManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Model\units\sql\dbQuery;
use \API\Developer\components\modules\moduleGroup;
use \API\Model\units\modules\Smodule;
use \API\Resources\DOMParser;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Abstract Module Manager
 * 
 * Manages the base information common for modules and auxiliaries.
 * 
 * @version	{empty}
 * @created	March 15, 2013, 13:14 (EET)
 * @revised	January 15, 2014, 10:11 (EET)
 * 
 * @deprecated	Use units\modules\module instead.
 */
abstract class AbstractModule extends vcsManager
{
	/**
	 * The repository root directory.
	 * 
	 * @type	string
	 */
	const REPOSITORY = "/Repositories/";
	/**
	 * The exported path in the system's root.
	 * 
	 * @type	string
	 */
	const EXPORT_PATH = "/System/Library/Units/Modules";
	/**
	 * The inner repository directory.
	 * 
	 * @type	string
	 */
	const REPOSITORY_PATH = "/Library/Units/Modules";
	
	/**
	 * The module's file type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "php";
	
	/**
	 * The module's id
	 * 
	 * @type	integer
	 */
	protected $id;
	/**
	 * The module's title
	 * 
	 * @type	string
	 */
	protected $title;
	
	/**
	 * The module's description
	 * 
	 * @type	string
	 */
	protected $description;
	
	/**
	 * The parent's module group id.
	 * 
	 * @type	integer
	 */
	protected $groupID;
	/**
	 * The repository's directory where the vcs will be initialized.
	 * 
	 * @type	string
	 */
	protected $repositoryDir;
	/**
	 * The directory where source files are exported.
	 * 
	 * @type	string
	 */
	protected $exportDir;
	
	/**
	 * Keeps all the package imports of the module.
	 * 
	 * @type	array
	 */
	protected $imports = array();
	/**
	 * Keeps all the inner modules used by this module.
	 * 
	 * @type	array
	 */
	protected $innerModules = array();
	/**
	 * The module's source code.
	 * 
	 * @type	string
	 */
	protected $sourceCode;
	
	/**
	 * Whether the module has been initialized.
	 * 
	 * @type	boolean
	 */
	protected $initialized = FALSE;
	
	/**
	 * The database connection manager.
	 * 
	 * @type	interDbConnection
	 */
	protected $dbc;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $withCSS = FALSE;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $withJS = FALSE;
	
	
	/**
	 * Initializes the object.
	 * 
	 * @param	string	$id
	 * 		The module's id.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	/**
	 * Initializes the object.
	 * 
	 * @param	string	$id
	 * 		The module's id.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	/**
	 * Initializes the object.
	 * 
	 * @param	string	$id
	 * 		The module's id.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	/**
	 * Initializes the object.
	 * 
	 * @param	string	$id
	 * 		The module's id.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	abstract public function initialize($id);
	
	
	/**
	 * Creates a new object.
	 * 
	 * @param	string	$parentID
	 * 		The parent's id (module or moduleGroup)
	 * 
	 * @param	string	$title
	 * 		The object's title.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	/**
	 * Creates a new object.
	 * 
	 * @param	string	$parentID
	 * 		The parent's id (module or moduleGroup)
	 * 
	 * @param	string	$title
	 * 		The object's title.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	/**
	 * Creates a new object.
	 * 
	 * @param	string	$parentID
	 * 		The parent's id (module or moduleGroup)
	 * 
	 * @param	string	$title
	 * 		The object's title.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	abstract public function create($parentID, $title);
	
	
	/**
	 * Deletes the object.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	/**
	 * Deletes the object.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	/**
	 * Deletes the object.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	/**
	 * Deletes the object.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	abstract public function delete();
	
	/**
	 * Gets the hashed filename of the object.
	 * 
	 * @return	string
	 * 		{description}
	 */
	abstract protected function getFileName();
	
	/**
	 * Constructor Method.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize Database Connection
		$this->dbc = new interDbConnection();
	}
	
	/**
	 * Initializes the object's variables.
	 * 
	 * @return	void
	 */
	protected function initializeInfo()
	{
		// Initialize properties
		$this->name = $this->getFileName();
		
		$this->imports = array();
		$this->innerModules = array();
		
		$this->sourceCode = "";
		$this->jsCode = "";
		$this->cssCode = "";
	}
	
	/**
	 * Get the module's title.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Gets the module's description.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * Get the module's packages import.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getImports()
	{
		return $this->imports;
	}
	
	/**
	 * Get the module's inner modules.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getInnerModules()
	{
		return $this->innerModules;
	}
	
	/**
	 * Sets the import objects array.
	 * 
	 * @param	array	$imports
	 * 		The imported packages.
	 * 
	 * @return	void
	 */
	public function setImports($imports)
	{
		$this->imports = array();
		foreach ($imports as $key => $val)
		{
			// Get Parts
			$parts = explode(",", $key);
			$lib = $parts[0];
			$pkg = $parts[1];
			
			// Build array
			$this->imports[$lib][] = $pkg;
		}
	}
	
	/**
	 * Sets the object's title.
	 * 
	 * @param	string	$title
	 * 		The new title.
	 * 
	 * @return	void
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	/**
	 * Sets the inner modules used by this object.
	 * 
	 * @param	array	$inner
	 * 		The inner modules.
	 * 
	 * @return	void
	 */
	public function setInnerModules($inner)
	{
		// Format inner modules
		//_____ policies that are marked for deletion have a value of "off".
		//_____ the rest are formed from their arrays, respectively, which in turn are then unset.
		foreach ($inner as $key => $i)
			if (gettype($i) == "array")
			{
				if (!empty($i['title']) && gettype($i['title']) == "string" && is_numeric($i['moduleId']))
					$inner[$i['title']] = $i['moduleId'];
				unset($inner[$key]);
			}

		foreach ($inner as $key => $value)
			$this->innerModules[$key] = $value;
	}
	
	/**
	 * Builds the object's index base element.
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser used to build the element.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function buildIndexBase($builder)
	{
		// Get Index Base
		$newBase = $this->getIndexBase($builder);
		$code_description = $builder->create("description", $this->description);
		$builder->append($newBase, $code_description);
		
		// Update Imports
		$imports = $this->buildImportsIndex($builder);
		$builder->append($newBase, $imports);
		
		// Update Inner modules
		$inner = $this->buildInnerModulesIndex($builder);
		$builder->append($newBase, $inner);
		
		return $newBase;
	}
	
	/**
	 * Builds the object's import index element.
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser used to build the element.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function buildImportsIndex($builder)
	{
		// Create import div
		$base = $builder->create("imports");
		
		// Set imports
		foreach ($this->imports as $lib => $packages)
		{
			foreach ($packages as $pkg)
			{
				// Build item
				$object = $builder->create("package");
				$builder->attr($object, "lib", $lib);
				$builder->attr($object, "name", $pkg);
				$builder->append($base, $object);
			}
		}
		
		return $base;
	}
	
	/**
	 * Builds the object's inner modules index element.
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser used to build the element.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function buildInnerModulesIndex($builder)
	{
		// Create base
		$base = $builder->create("inner");

		//_____ Set new imports
		foreach ($this->innerModules as $key => $value)
		{
			// Remove existing modules
			$mdls = $builder->evaluate("module[@name='$key']", $base);
			foreach ($mdls as $mdl)
				$base->removeChild($mdl);
			
			// Set inner modules
			if ($value != "off")
			{
				$mdl = $builder->create("module", $value);
				DOMParser::attr($mdl, "name", $key);
				DOMParser::append($base, $mdl);
			}
		}
		
		return $base;
	}
	
	/**
	 * Builds the source's header code.
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the code generated will be wrapped in section or not.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function buildHeader($wrapped = FALSE)
	{
		// Module ID
		$moduleHeader = "";
		$moduleHeader .= phpParser::get_variable("moduleID").' = '.$this->id.";\n\n";
		
		// Inner Modules
		$innerHeader = phpParser::get_variable("innerModules").' = array();'."\n";
		foreach ($this->innerModules as $key => $value)
			$innerHeader .= '$innerModules[\''.$key.'\'] = '.$value.";\n";
		
		// Get Headers
		$path = systemRoot.paths::getDevRsrcPath()."/Content/Modules/Headers/private.inc";
		$privateHeader = fileManager::get_contents($path);
		$privateHeader = phpParser::unwrap($privateHeader);
		
		// Imports
		$importsHeader = $this->buildImports();
		
		// Merge
		$headerContent = "";
		$headerContent .= $moduleHeader;
		$headerContent .= $innerHeader;
		$headerContent .= $privateHeader;
		$headerContent .= $importsHeader;
		
		return ($wrapped ? phpCoder::section($headerContent, "header") : $headerContent);
	}
	
	/**
	 * Builds the import section of the source code.
	 * 
	 * @return	string
	 * 		{description}
	 */
	private function buildImports()
	{
		// Build Imports
		$imports = "\n\n";
		$imports .= phpParser::get_comment("Import Packages", $multi = FALSE)."\n";
		$startups = "\n";
		foreach ($this->imports as $lib => $packages)
			foreach ($packages as $pkg)
				$imports .= 'importer::import("'.$lib.'", "'.$pkg.'");'."\n";
		
		// Merge and Clear
		$imports .= $startups;
		return preg_replace('/\n$/', '', $imports);
	}
	
	/**
	 * Gets the source code of the object.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getSourceCode()
	{
		// Load source code from source
		$path = $this->vcsTrunk->getPath($this->getWorkingBranch());
		$source = fileManager::get_contents($path);
		$sections = phpCoder::sections($source);

		// Return source code
		return trim($sections["code"]);
	}
	
	/**
	 * Gets the css code of the object.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getCSSCode()
	{
		// Load css code from repositiory
		$filePath = systemRoot.paths::getDevPath().self::REPOSITORY."/".self::REPOSITORY_PATH."/".$this->repositoryDir."/style.css";
		if (file_exists($filePath))
			$code = fileManager::get($filePath);
		
		// Return code
		return trim($code);
	}
	
	/**
	 * Update the css code of the object.
	 * 
	 * @param	string	$code
	 * 		The code value.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function updateCSSCode($code)
	{
		// Clear Code
		$code = phpParser::clearCode($code);
		$this->withCSS = !empty($code);
		
		// Update Index Base
		$this->updateIndexBase();
			
		// Load css code from repositiory
		$filePath = systemRoot.paths::getDevPath().self::REPOSITORY."/".self::REPOSITORY_PATH."/".$this->repositoryDir."/style.css";
		if (!empty($code))
			fileManager::create($filePath, $code);
		else
			fileManager::remove($filePath);
			
		return TRUE;
	}
	
	/**
	 * Gets the javascript code of the object.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getJSCode()
	{
		// Load css code from repositiory
		$filePath = systemRoot.paths::getDevPath().self::REPOSITORY."/".self::REPOSITORY_PATH."/".$this->repositoryDir."/script.js";
		if (file_exists($filePath))
			$code = fileManager::get_contents($filePath);
		
		// Return code
		return trim($code);
	}
	
	/**
	 * Update the javascript code of the object.
	 * 
	 * @param	string	$code
	 * 		The code value.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function updateJSCode($code)
	{
		// Clear Code
		$code = phpParser::clearCode($code);
		$this->withJS = !empty($code);
		
		// Update Index Base
		$this->updateIndexBase();

		// Load css code from repositiory
		$filePath = systemRoot.paths::getDevPath().self::REPOSITORY."/".self::REPOSITORY_PATH."/".$this->repositoryDir."/script.js";
		if (!empty($code))
			fileManager::create($filePath, $code);
		else
			fileManager::remove($filePath);
		
		return TRUE;
	}
	
	/**
	 * Gets the default module's source code.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getDefaultSourceCode()
	{
		// Get Default Module Code
		$sourceCode = fileManager::get(systemRoot.paths::getDevRsrcPath()."/Content/Modules/Code/default.inc");
		
		// Clear Delimiters
		$sourceCode = phpParser::unwrap($sourceCode);
		
		// Wrap to section
		return trim($sourceCode);
	}
	
	/**
	 * Builds the outer index base element.
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser used to build the element.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function getIndexBase($builder)
	{
		$newItem = $builder->create("item", "", $this->getFileName());
		$builder->attr($newItem, "title", $this->getTitle());
		$builder->attr($newItem, "css", $this->withCSS);
		$builder->attr($newItem, "js", $this->withJS);
		
		return $newItem;
	}
	
	/**
	 * Loads the object's info (imports and inner modules)
	 * 
	 * @return	void
	 */
	protected function loadInfo()
	{		
		$parser = new DOMParser();
		$base = $this->vcsTrunk->getBase($parser, $this->getWorkingBranch());
		
		// Load Attributes
		$withCSS = $parser->attr($base, "css");
		$withJS = $parser->attr($base, "js");
		
		$this->withCSS = (empty($withCSS) ? FALSE : $withCSS == "1");
		$this->withJS = (empty($withJS) ? FALSE : $withJS == "1");
		
		// Load imports
		$this->loadImports($parser, $base);
		
		// Load inner modules
		$this->loadInnerModules($parser, $base);
	}
	
	/**
	 * Loads the import objects from the index file.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	DOMElement	$base
	 * 		The object's index base element.
	 * 
	 * @return	void
	 */
	private function loadImports($parser, $base)
	{
		$this->imports = array();
		$importsBase = $parser->evaluate("imports", $base)->item(0);
		
		if (is_null($importsBase))
			return;
			
		$importObject = $parser->evaluate("package", $importsBase);
		foreach ($importObject as $object)
			$this->imports[$parser->attr($object, "lib")][] = $parser->attr($object, "name");
	}
	
	/**
	 * Loads the inner modules from the index file.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	DOMElement	$base
	 * 		The object's index base element.
	 * 
	 * @return	void
	 */
	private function loadInnerModules($parser, $base)
	{
		$this->innerModules = array();
		$inner = $parser->evaluate("inner", $base)->item(0);
		$modules = $parser->evaluate("module", $inner);
		foreach ($modules as $module)
			$this->innerModules[$parser->attr($module, "name")] = $module->nodeValue;
	}
	
	/**
	 * Load the object's info from the database.
	 * 
	 * @return	void
	 */
	protected function loadDatabaseInfo()
	{
		// Get module data from Database
		$dbq = new dbQuery("361601426", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		$result = $this->dbc->execute($dbq, $attr);
		$module = $this->dbc->fetch($result);
	
		// Initialize variables
		$this->title = $module['module_title'];
		$this->description = $module['module_description'];
		$this->groupID = $module['group_id'];
		
		// Get module full directory
		$this->exportDir = self::EXPORT_PATH.moduleGroup::getTrail($this->groupID).Smodule::directoryName($this->id);
		$this->repositoryDir = moduleGroup::getTrail($this->groupID).Smodule::directoryName($this->id);
	}
	
	/**
	 * Updates the index base information.
	 * 
	 * @param	string	$title
	 * 		The new object's title.
	 * 
	 * @param	string	$description
	 * 		The new object's description.
	 * 
	 * @param	array	$imports
	 * 		The object's import packages.
	 * 
	 * @param	array	$inner
	 * 		The object's inner modules.
	 * 
	 * @return	void
	 */
	protected function updateIndexInfo($title, $description, $imports, $inner)
	{
		$this->setTitle($title);
		$this->description = $description;
		$this->setImports($imports);
		$this->setInnerModules($inner);
		
		// Update Index Base
		$this->updateIndexBase();
	}
	
	/**
	 * Updates the index base element in the trunk.
	 * 
	 * @return	void
	 */
	private function updateIndexBase()
	{
		// Get Index Base
		$builder = new DOMParser();
		$newBase = $this->buildIndexBase($builder);
		
		// Update Trunk Base
		$this->vcsTrunk->updateBase($this->getWorkingBranch(), $newBase);
	}
	
	/**
	 * Updates the object's source code.
	 * 
	 * @param	string	$code
	 * 		The new source code. If empty, the default code will be set.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	protected function updateSourceCode($code = "")
	{
		// Set source code (default if not given)
		$this->sourceCode = (trim($code) == "" ? $this->getDefaultSourceCode() : $code);
		
		// Clear code from unwanted characters
		$this->sourceCode = phpParser::clearCode($this->sourceCode);
		// Comment out dangerous commands
		//$this->sourceCode = phpParser::safe($this->sourceCode);
		// Set code section
		$sourceCode = phpCoder::section($this->sourceCode, "code");
		$header = $this->buildHeader(TRUE);
		
		// Merge all source code
		$this->sourceCode = $header.$sourceCode;
		$this->sourceCode = phpParser::wrap($this->sourceCode);
		
		// Get File Path
		$filePath = $this->vcsTrunk->getPath($this->getWorkingBranch());
		
		// Create temp file to check syntax
		$tempFile = $filePath.".temp";
		fileManager::create($tempFile, $this->sourceCode);
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;
			
		// Remove temp file
		fileManager::remove($tempFile);

		// Update File
		return (fileManager::put($filePath, $this->sourceCode) !== FALSE);
	}
	
	/**
	 * Loads all the information available for the object.
	 * 
	 * @return	void
	 */
	public function load()
	{
		// Load database info
		$this->loadDatabaseInfo();
		
		// Initialize Information
		$this->initializeInfo();
				
		// Re-Initialize VCS
		$this->VCS_initialize(paths::getDevPath().self::REPOSITORY, self::REPOSITORY_PATH."/".$this->repositoryDir, $this->name, self::FILE_TYPE);
		
		// Load Info
		$this->loadInfo();
	}
	
	/**
	 * Performs the commit and checkout action to version control.
	 * 
	 * @param	string	$description
	 * 		The commit description
	 * 
	 * @return	void
	 */
	public function commitCheckout($description)
	{
		// VCS Commit
		$this->commit($description);
		
		// VCS Checkout
		$this->checkout();
	}
	
	/**
	 * Performs the commit action to version control.
	 * 
	 * @param	string	$description
	 * 		The commit description.
	 * 
	 * @return	void
	 */
	public function commit($description)
	{
		$this->vcsBranch->commit($this->getWorkingBranch(), $description);
	}
	
	/**
	 * Performs the checkout action to version control.
	 * 
	 * @return	void
	 */
	public function checkout()
	{
		// VCS Checkout
		$objectPath = $this->vcsBranch->checkout($this->getWorkingBranch());
		$this->export();
	}
	
	/**
	 * Exports the module to latest along with its css and javascript resources.
	 * 
	 * @return	void
	 */
	public function export()
	{
		$objectPath = $this->vcsBranch->getHeadPath();

		// Create Checkout module's folder
		if (!file_exists(systemRoot.$this->exportDir))
		{
			// Create Folder
			folderManager::create(systemRoot.$this->exportDir, "", 0777, TRUE);
			// Create Index
			$builder = new DOMParser();
			$base = $builder->create("index");
			$builder->append($base);
			$builder->save(systemRoot.$this->exportDir, "index.xml", TRUE);
		}
		
		// Copy checkout file
		fileManager::copy($objectPath, systemRoot.$this->exportDir.$this->name.".".self::FILE_TYPE);
		
		// Update Checkout Index
		$builder = new DOMParser();
		$builder->load($this->exportDir."/index.xml", TRUE);
		$base = $builder->evaluate("//index")->item(0);
		
		$oldItem = $builder->find($this->getFileName());
		$newItem = $this->getIndexBase($builder);
		
		if (!is_null($oldItem))
			$builder->replace($oldItem, $newItem);
		else
			$builder->append($base, $newItem);
		
		// Save Index file
		$builder->save(systemRoot.$this->exportDir, "index.xml", TRUE);
		
		// Export CSS File
		$cssContent = cssParser::format($this->getCSSCode());
		BootLoader::exportCSS("Modules", "Modules", $this->id, $cssContent);
		
		// Export JS File
		$jsContent = jsParser::format($this->getJSCode());
		BootLoader::exportJS("Modules", "Modules", $this->id, $jsContent);
	}
	
	/**
	 * Executes the object's source code script (from the trunk).
	 * 
	 * @return	void
	 */
	public function run()
	{
		if ($this->initialized)
			return importer::incl($this->vcsTrunk->getPath($this->getWorkingBranch()), FALSE);
		else
			throw new Exception("Module initialization error...");
	}
	
	/**
	 * Returns indicator whether this module has CSS code.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function hasCSS()
	{
		return $this->withCSS;
	}
	
	/**
	 * Returns indicator whether this module has javascript code.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function hasJS()
	{
		return $this->withJS;
	}
	
	/**
	 * Gets the current working branch for the version control.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getWorkingBranch()
	{
		return parent::getWorkingBranch($this->id);
	}
	
	/**
	 * Get all auxiliaries of the module.
	 * 
	 * @return	array
	 * 		An array of auxiliaryes [id] => [title].
	 */
	public function getAuxiliaries()
	{
		// Initialize module index base
		$parser = new DOMParser();
		$this->vcsTrunk->getBase($parser, $this->getWorkingBranch());
		
		// Get auxiliary from index
		$auxList = $parser->evaluate("item[@seed]");
		$moduleAuxs = array();
		
		// Form auxiliary array
		foreach ($auxList as $aux)
		{
			$auxID = $aux->getAttribute("id");
			$auxTitle = $aux->getAttribute("title");
			$moduleAuxs[$auxID] = $auxTitle;
		}
		
		return $moduleAuxs;
	}
}
//#section_end#
?>