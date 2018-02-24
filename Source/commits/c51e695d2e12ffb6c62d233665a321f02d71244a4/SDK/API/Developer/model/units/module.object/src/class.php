<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units;

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
 * @namespace	\model\units
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::modules::SmoduleGroup");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "privileges::modules::manage::tester");
importer::import("API", "Security", "privileges::modules::manage::developer");
importer::import("API", "Developer", "model::version::vcs");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "content::document::parsers::phpParser");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\fileManager;
use \API\Content\filesystem\folderManager;
use \API\Developer\content\resources;
use \API\Developer\model\version\vcs;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\content\document\parsers\phpParser;
use \API\Model\units\modules\SmoduleGroup;
use \API\Model\units\modules\Smodule;
use \API\Security\privileges\modules\manage\tester;
use \API\Security\privileges\modules\manage\developer;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

/**
 * Redback Module Structure
 * 
 * Handles the basic form of coding in the system.
 * 
 * @version	{empty}
 * @created	March 20, 2013, 10:26 (EET)
 * @revised	March 20, 2013, 10:26 (EET)
 * 
 * @deprecated	Use \API\Developer\components\moduleObject instead.
 */
abstract class module extends vcs
{
	/**
	 * The system's exported root path for modules
	 * 
	 * @type	string
	 */
	const PATH = "/System/Library/Modules";
	
	/**
	 * The extension file type for vcs.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "php";
	
	/**
	 * The module id
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
	 * The module's group
	 * 
	 * @type	integer
	 */
	protected $group_id;
	
	/**
	 * The directory path of the module (including group trail)
	 * 
	 * @type	string
	 */
	protected $vcsDirectory;
	/**
	 * The exported directory from DOCUMENT_ROOT
	 * 
	 * @type	string
	 */
	protected $directory;
		
	/**
	 * The list of API Objects imported
	 * 
	 * @type	array
	 */
	protected $api_imports = array();
	/**
	 * The list of SDK (UI) Objects imported
	 * 
	 * @type	array
	 */
	protected $sdk_imports = array();
	/**
	 * The list of Shell Objects imported
	 * 
	 * @type	array
	 */
	protected $shell_imports = array();
	/**
	 * The list of modules being used by this module
	 * 
	 * @type	array
	 */
	protected $inner = array();
	/**
	 * The module's source code
	 * 
	 * @type	string
	 */
	protected $source_code;
	
	/**
	 * The database connection manager
	 * 
	 * @type	interDbConnection
	 */
	protected $dbc;
	
	/**
	 * Abstract function for initializing the module or auxiliary
	 * 
	 * @param	integer	$id
	 * 		The module's id
	 * 
	 * @return	mixed
	 */
	abstract public function initialize($id);
	
	/**
	 * Creates a module
	 * 
	 * @param	integer	$parent_id
	 * 		This is the parent's id.
	 * 		For modules, it's the group id.
	 * 		For auxiliaries, it's the module id
	 * 
	 * @param	string	$title
	 * 		The title of the module.
	 * 
	 * @param	string	$description
	 * 		The description of the module
	 * 
	 * @return	void
	 */
	abstract protected function create($parent_id, $title, $description);
	
	/**
	 * Deletes a module
	 * 
	 * @return	void
	 */
	abstract protected function delete();
	
	/**
	 * Update the module's index information
	 * 
	 * @return	void
	 */
	abstract protected function update_indexInfo();
	
	/**
	 * Constructor Method.
	 * Initializes the interDbConnection.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->dbc = new interDbConnection();
	}
	
	/**
	 * Initialize module information
	 * 
	 * @return	void
	 */
	protected function initialize_info()
	{
		$this->api_imports = array();
		$this->sdk_imports = array();
		$this->shell_imports = array();
		$this->inner = array();
		
		$this->source_code = "";
	}
	
	/**
	 * Returns the module's id
	 * 
	 * @return	integer
	 */
	public function get_id()
	{
		return $this->id;
	}
	
	/**
	 * Returns the module's title.
	 * 
	 * @return	string
	 */
	public function get_title()
	{
		return $this->title;
	}

	/**
	 * Returns the module's description
	 * 
	 * @return	string
	 */
	public function get_description()
	{
		return $this->description;
	}
	
	/**
	 * Returns the API objects that this module uses.
	 * 
	 * @return	array
	 */
	public function get_apiImports()
	{
		return $this->api_imports;
	}
	
	/**
	 * Returns the SDK (UI) objects that this module uses.
	 * 
	 * @return	array
	 */
	public function get_sdkImports()
	{
		return $this->sdk_imports;
	}
	
	/**
	 * Returns the Shell objects that this module uses.
	 * 
	 * @return	array
	 */
	public function get_shellImports()
	{
		return $this->shell_imports;
	}
	
	/**
	 * Returns the modules that this module uses or dependes on.
	 * 
	 * @return	array
	 */
	public function get_inner()
	{
		return $this->inner;
	}
	
	/**
	 * Set the module's title
	 * 
	 * @param	string	$title
	 * 		The title
	 * 
	 * @return	void
	 */
	public function set_title($title)
	{
		$this->title = $title;
	}

	/**
	 * Set the module's description
	 * 
	 * @param	string	$description
	 * 		The description
	 * 
	 * @return	void
	 */
	public function set_description($description)
	{
		$this->description = $description;
	}
	
	/**
	 * Set the API objects
	 * 
	 * @param	array	$imports
	 * 		The API objects
	 * 
	 * @return	void
	 */
	public function set_apiImports($imports)
	{
		$this->api_imports = $this->_get_imports($imports);
	}
	
	/**
	 * Set the SDK (UI) objects
	 * 
	 * @param	array	$imports
	 * 		The SDK objects
	 * 
	 * @return	void
	 */
	public function set_sdkImports($imports)
	{
		$this->sdk_imports = $this->_get_imports($imports);
	}
	
	/**
	 * Set the Shell objects
	 * 
	 * @param	array	$imports
	 * 		The Shell objects
	 * 
	 * @return	void
	 */
	public function set_shellImports($imports)
	{
		$this->shell_imports = $this->_get_imports($imports);
	}
	
	/**
	 * Creates an array of given imports and builds the structure.
	 * 
	 * @param	array	$imports
	 * 		The array of imports.
	 * 
	 * @return	array
	 */
	private function _get_imports($imports)
	{
		if (is_null($imports))
			return array();
		
		$temp_imports = array();
		$temp_imports['ns'] = array();
		$temp_imports['name'] = array();
		foreach ($imports as $import => $value)
		{
			// Get parts
			$parts = explode("::", $import);
			
			// Set name
			$name = $parts[count($parts)-1];
			$temp_imports['name'][] = $name;
			unset($parts[count($parts)-1]);
			
			// Set namespace
			$ns = implode("::", $parts);
			$temp_imports['ns'][] = $ns;
		}
		return $temp_imports;
	}
	
	/**
	 * Set the inner modules
	 * 
	 * @param	array	$inner
	 * 		The inner modules
	 * 
	 * @return	void
	 */
	public function set_inner($inner)
	{
		// Format inner policies
		//_____ policies that are marked for deletion have a value of "off".
		//_____ the rest are formed from their arrays, respectively, which in turn are then unset.
		foreach ($inner as $key => $i)
		{
			if (gettype($i) == "array")
			{
				if (!empty($i['title']) & gettype($i['title']) == "string" && is_numeric($i['moduleId']))
					$inner[$i['title']] = $i['moduleId'];
				unset($inner[$key]);
			}
		}

		foreach ($inner as $key => $value)
			$this->inner[$key] = $value;
	}
	
	/**
	 * Loads the content of the module and initializes this object
	 * 
	 * @return	void
	 */
	public function load()
	{
		// Load database info
		$this->load_databaseInfo();
		
		// Initialize VCS
		$this->VCS_initialize("/Library/Modules/".$this->vcsDirectory, $this->name, self::FILE_TYPE);
		
		// Initialize Information
		$this->initialize_info();
		
		// Re-Initialize VCS
		$this->VCS_initialize("/Library/Modules/".$this->vcsDirectory, $this->name, self::FILE_TYPE);
		
		try
		{
			// Load Information
			$this->load_indexInfo();
			
			// TEMP
			// Create Release Branch
			$this->vcsBranch->create("release");
		}
		catch (Exception $ex)
		{
		}
	}
	
	/**
	 * Loads the module's index information
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	void
	 */
	protected function load_indexInfo($branch = "")
	{
		$info_parser = new DOMParser();
		$base = $this->vcsTrunk->get_base($info_parser, $branch);
		
		if (is_null($base))
			return FALSE;
		
		// Load imports
		//_____ API
		$this->api_imports = $this->_load_imports($info_parser, $base, "api");
		//_____ SDK
		$this->sdk_imports = $this->_load_imports($info_parser, $base, "ui");
		//_____ Shell
		$this->shell_imports = $this->_load_imports($info_parser, $base, "shell");
		
		// Load inner modules
		$this->inner = array();
		$inner = $info_parser->evaluate("inner", $base)->item(0);
		$inner_codes = $info_parser->evaluate("module", $inner);
		foreach ($inner_codes as $policy)
			$this->inner[$policy->getAttribute("name")] = $policy->nodeValue;
	}
	
	/**
	 * Loads the imports by name
	 * 
	 * @param	DOMParser	$info_parser
	 * 		The parser to parse the file
	 * 
	 * @param	DOMElement	$base
	 * 		The base of this object in the index file
	 * 
	 * @param	string	$name
	 * 		The name of the imports
	 * 
	 * @return	array
	 */
	private function _load_imports($info_parser, $base, $name)
	{
		$imports = array();
		$import_base = $info_parser->evaluate($name, $base)->item(0);
		
		if (is_null($import_base))
			return $imports;
			
		$import_objects = $info_parser->evaluate("object", $import_base);
		foreach ($import_objects as $object)
		{
			if ($object->getAttribute('ns') != "")
			{
				$imports['name'][] = $object->nodeValue;
				$imports['ns'][] = $object->getAttribute('ns');
			}
			else
				$imports[] = $object->nodeValue;
		}
			
		return $imports;
	}
	
	/**
	 * Commit to HEAD branch
	 * 
	 * @param	string	$description
	 * 		The commit's description
	 * 
	 * @return	void
	 */
	public function commit($description)
	{
		// VCS Commit
		$this->vcsBranch->commit($description);

		// VCS and Module Checkout
		$this->checkout();
	}
	
	/**
	 * Exports the module source code to latest and makes it available for other users.
	 * 
	 * @return	void
	 */
	public function checkout()
	{
		// VCS Checkout
		$objectPath = $this->vcsBranch->checkout();

		// Create Checkout folder (/System/Library/Modules/)
		if (!file_exists(systemRoot.$this->directory))
		{
			folderManager::create(systemRoot.$this->directory, "", 0777, TRUE);
			$builder = new DOMParser();
			$base = $builder->create("index");
			$builder->append($base);
			$builder->save(systemRoot.$this->directory, "index.xml", TRUE);
		}
		
		// Copy checkout file
		fileManager::copy_file($objectPath, systemRoot.$this->directory.$this->name.".".self::FILE_TYPE);
		
		// Update Checkout Index
		$builder = new DOMParser();
		$builder->load($this->directory."/index.xml", TRUE);
		$base = $builder->evaluate("//index")->item(0);
		
		$oldItem = $builder->find($this->_get_fileName());
		$newItem = $builder->create("item", "", $this->_get_fileName());
		$builder->attr($newItem, "title", $this->get_title());
		$builder->attr($newItem, "seed", $this->seed);
		
		if (!is_null($oldItem))
			$builder->replace($oldItem, $newItem);
		else
			$builder->append($base, $newItem);
		

		$builder->save(systemRoot.$this->directory, "index.xml", TRUE);
	}
	
	/**
	 * Update the module's contents
	 * 
	 * @param	string	$title
	 * 		The module's title
	 * 
	 * @param	string	$description
	 * 		The module's description
	 * 
	 * @param	string	$code
	 * 		The module's source code
	 * 
	 * @param	string	$branch
	 * 		The vcs branch where to perform the update
	 * 
	 * @return	boolean
	 */
	protected function update($title, $description, $code = "", $branch = "")
	{
		$this->title = $title;
		$this->description = $description;
		
		// Update Module Information
		$this->update_indexInfo($branch);
	
		// Update Source Code
		return $this->update_sourceCode($code, $branch);
	}
	
	/**
	 * Update and save the source code of the module
	 * 
	 * @param	string	$code
	 * 		The source code
	 * 
	 * @param	string	$branch
	 * 		The vcs branch where to perform the update
	 * 
	 * @return	boolean
	 */
	protected function update_sourceCode($code = "", $branch = "")
	{
		$this->source_code = trim($code);
		
		if ($this->source_code == "")
			$this->source_code = $this->_get_defaultSourceCode();
		$sourceCode = phpCoder::section($this->source_code, "code");
		
		$private_code = $this->build_privateCode();
		$private_code = phpCoder::section($private_code, "private");
		
		// Build Imports
		//$imports = $this->_build_imports();
		
		// Merge all source code
		$this->source_code = $private_code.$imports.$sourceCode;
		
		// Build Entire Source Code as PHP Code
		$this->source_code = phpParser::wrap($this->source_code);

		// Update File
		//_____ Get Trunk file
		$file = $this->vcsTrunk->get_itemPath($branch);
		return fileManager::put_contents($file, $this->source_code);
	}
	
	/**
	 * Build the source code imports and returns the array
	 * 
	 * @return	array
	 */
	protected function _build_imports()
	{
		// Build Imports
		$imports = "";
		$uses = "";
		$inits = "";
		
		//_____ Build API Imports
		//_____ Initialize API Objects
		$class_mapping = array_map(function($ns, $name){ return $ns."::".$name; }, $this->api_imports['ns'], $this->api_imports['name']);
		foreach ($class_mapping as $class)
			$imports .= 'importer::importAPI'."('".$class."');\n";
		
		//_____ Build SDK Imports
		$class_mapping = array_map(function($ns, $name){ return $ns."::".$name; }, $this->sdk_imports['ns'], $this->sdk_imports['name']);
		foreach ($class_mapping as $class)
			$imports .= 'importer::importUI'."('".$class."');\n";
				
		//_____ Build Shell Imports
		$class_mapping = array_map(function($ns, $name){ return $ns."::".$name; }, $this->shell_imports['ns'], $this->shell_imports['name']);
		foreach ($class_mapping as $class)
			$imports .= 'importer::importShell'."('".$class."');\n";
		
		// Merge and Clear
		$imports = preg_replace('/\n$/', '', $imports);
		$uses = preg_replace('/\n$/', '', $uses);
		$inits = preg_replace('/\n$/', '', $inits);
		
		// Sections
		$imports = phpCoder::section($imports, "imports");
		
		return $imports;
	}
	
	/**
	 * Returns the module's source code
	 * 
	 * @param	string	$branch
	 * 		The branch of the vcs. If empty, take the default branch.
	 * 
	 * @return	string
	 */
	public function get_sourceCode($branch = "")
	{
		if (!developer::get_moduleStatus($this->id))
			return NULL;
			
		// Load source code from source
		$path = $this->vcsTrunk->get_itemPath($branch);
		
		//_____ Load source
		$source = fileManager::get_contents($path);
		$sections = phpCoder::sections($source);

		// Return source code
		return trim($sections["uses"]."\n\n".$sections["code"]);
	}
	
	/**
	 * Returns the index info base element
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser to search for the index
	 * 
	 * @return	DOMElement
	 */
	protected function get_indexInfo($builder)
	{
		// Get the current base to update
		$newBase = $this->vcsTrunk->get_base($builder);
		
		// Clear base
		DOMParser::innerHTML($newBase, "");
				
		// Update Information
		DOMParser::attr($newBase, "title", $this->get_title());
		$code_description = $builder->create("description", $this->description);
		DOMParser::append($newBase, $code_description);
		
		// Update API
		$imports = $this->build_importIndex($builder, "api", $this->api_imports);
		DOMParser::append($newBase, $imports);
		
		// Update SDK
		$imports = $this->build_importIndex($builder, "ui", $this->sdk_imports);
		DOMParser::append($newBase, $imports);
		
		// Update Shell
		$imports = $this->build_importIndex($builder, "shell", $this->shell_imports);
		DOMParser::append($newBase, $imports);
		
		// Update Inner modules
		$inner = $this->build_innerIndex($builder);
		DOMParser::append($newBase, $inner);
		
		return $newBase;
	}
	
	/**
	 * Build the import wrapper
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser to build the element
	 * 
	 * @param	string	$name
	 * 		{description}
	 * 
	 * @param	array	$name_imports
	 * 		{description}
	 * 
	 * @return	DOMElement
	 */
	protected function build_importIndex($builder, $name, $name_imports)
	{
		// Create import div
		$base = $builder->create($name);
		
		// Set imports
		if (!isset($name_imports['ns']))
		{
			foreach ($name_imports as $key => $value)
			{
				$object = $builder->create("object", $key);
				DOM::append($base, $object);
			}
		}
		else
		{
			foreach ($name_imports['name'] as $key => $value)
			{
				$object = $builder->create("object", $value);
				DOMParser::attr($object, "ns", $name_imports['ns'][$key]);
				DOMParser::append($base, $object);
			}
		}
		
		return $base;
	}
	
	/**
	 * Builds the inner module's index
	 * 
	 * @param	DOMParser	$builder
	 * 		The builder parser
	 * 
	 * @return	DOMElement
	 */
	protected function build_innerIndex($builder)
	{
		// Create base
		$base = $builder->create("inner");

		//_____ Set new imports
		foreach ($this->inner as $key => $value)
		{
			// Remove inner policy
			$p = $builder->evaluate("module[@name='$key']", $base);
			foreach ($p as $policy)
				$policies_base->removeChild($policy);
					
			if ($value != "off")
			{
				// append inner policy
				$pol = $builder->create("module", $value);
				DOMParser::attr($pol, "name", $key);
				DOMParser::append($base, $pol);
			}
		}
		
		return $base;
	}
	
	/**
	 * Includes and executes the module's source code.
	 * 
	 * @param	string	$branch
	 * 		The vcs branch from where to execute the code.
	 * 
	 * @return	boolean
	 */
	public function get_content($branch = "")
	{
		$trunk = FALSE;
		if (tester::get_moduleStatus($this->id))
			$trunk = TRUE;

		return $this->get_innerContent($trunk, $branch);
	}
	
	/**
	 * Gets and executes (by inclusion) the module's source code
	 * 
	 * @param	boolean	$trunk
	 * 		Defines whether the module will be executed from the repository or from the latest.
	 * 
	 * @param	string	$branch
	 * 		The vcs branch from where the module will be executed (if trunk)
	 * 
	 * @return	string
	 */
	private function get_innerContent($trunk = FALSE, $branch = "")
	{
		if ($trunk)
			return include($this->vcsTrunk->get_itemPath($branch));
		else
			return include(systemRoot.$this->directory.$this->_get_fileName().".php");
	}
	
	/**
	 * Builds the private section of the module. The headers.
	 * 
	 * @return	string
	 */
	protected function build_privateCode()
	{
		
		$path = systemRoot.resources::PATH."/Content/Modules/Headers/private.inc";
		
		$private = fileManager::get_contents($path);
		$private = phpParser::unwrap($private);

		//_____ Prepend Policy Codes
		$policies = "";
		$policies .= '$policyCode = '.$this->id.";\n\n";
		$policies .= '$moduleID = '.$this->id.";\n\n";
		
		//_____ Set Inner Module Codes
		$policies .= '$innerPolicyCodes = array();'."\n";
		$policies .= '$innerModules = array();'."\n";

		foreach ($this->inner as $key => $value)
		{
			$policies .= '$innerPolicyCodes[\''.$key.'\'] = '.$value.";\n";
			$policies .= '$innerModules[\''.$key.'\'] = '.$value.";\n";
		}
		
		$private = $policies.$private."\n";
		
		return $private;
	}
	
	/**
	 * Get the default source code for an empty module
	 * 
	 * @return	string
	 */
	protected function _get_defaultSourceCode()
	{
		// Get Default Module Code
		$source_code = fileManager::get_contents(systemRoot.resources::PATH."/Content/Modules/Code/default.inc");
		
		// Clear Delimiters
		$source_code = phpParser::unwrap($source_code);
		
		// Wrap to section
		return trim($source_code);
	}
	
	/**
	 * Load Information from database
	 * 
	 * @return	void
	 */
	protected function load_databaseInfo()
	{
		// Get module data from Database
		$dbq = new dbQuery("361601426", "units.modules");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['id'] = $this->id;
		$result = $dbc->execute_query($dbq, $attr);

		$module = $dbc->fetch($result);
	
		// Initialize variables
		$this->title = $module['module_title'];
		$this->description = $module['module_description'];
		$this->group_id = $module['group_id'];
		
		// Get module full directory
		$this->directory = self::PATH.SmoduleGroup::trail($this->group_id).Smodule::directoryName($this->id);
		$this->vcsDirectory = SmoduleGroup::trail($this->group_id).Smodule::directoryName($this->id);
	}
}
//#section_end#
?>