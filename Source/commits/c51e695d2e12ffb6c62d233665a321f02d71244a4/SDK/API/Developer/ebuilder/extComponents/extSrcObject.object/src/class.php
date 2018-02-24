<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder\extComponents;

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
 * @namespace	\ebuilder\extComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "components::prime::classObject");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "components::prime::classObject2");

use \API\Developer\components\prime\classObject2;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\components\prime\classObject;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	July 1, 2013, 18:44 (EEST)
 * @revised	August 12, 2013, 13:51 (EEST)
 */
class extSrcObject extends classObject2
{		
	private $devRootRepo;
 	private $SrcObjRootRepo;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $codeSection;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $imports;
			
	/**
	 * {description}
	 * 
	 * @param	{type}	$repositoryRoot
	 * 		{description}
	 * 
	 * @param	{type}	$srcRepository
	 * 		{description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($repository, $includeRelease, $library, $package, $namespace = "", $name = NULL)
	{
		// Parent Constructor
		parent::__construct($repository, $includeRelease, $library, $package, $namespace = "", $name = NULL);
	}
	
	/**
	 * {description}
	 * 
	 * @param	array	$imports
	 * 		{description}
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
	 * {description}
	 * 
	 * @param	{type}	$repositoryRoot
	 * 		{description}
	 * 
	 * @param	{type}	$srcRepository
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function load()
	{		
		// Init classObject
		parent::init($this->devRootRepo, $this->SrcObjRootRepo."::".$this->libName."::".$this->packageName."::".$this->nsName);
		
		// Load Index Info
		$this->loadInfo();
	}
	
	/**
	 * {description}
	 * 
	 * @param	DOMParser	$parser
	 * 		{description}
	 * 
	 * @param	DOMElement	$base
	 * 		{description}
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
	 * {description}
	 * 
	 * @return	void
	 */
	public function loadInfo()
	{
		$parser = new DOMParser();
		$base = $this->vcsTrunk->getBase($parser, $this->getWorkingBranch());		
		
		// Load imports
		$this->loadImports($parser, $base);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function create($objectName, $title = '')
	{
		$this->name = $objectName;
		$this->title = ($title == "" ? $objectName: $title);
		
		// Initalize variables
		$this->setImports($imports = array());
		
		// Reset repository to obtain correct paths
		$this->setRepository($this->devRootRepo, $this->SrcObjRootRepo."::".$this->libName."::".$this->packageName."::".$this->nsName);
		// Create class Object		
		parent::create($this->name, $this->title);
	
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function update($code = "")
	{
		// Get SDK Object Class Header
		$header = $this->buildHeader();
		
		// Update Index
		$this->updateIndexInfo();
		
		// Update Source Code
		return parent::updateSourceCode($header, $code);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function updateIndexInfo()
	{
		// Get Index Base
		$builder = new DOMParser();
		$newBase = $this->buildIndexBase($builder);
		
		// Update Trunk Base
		$this->vcsTrunk->updateBase($this->getWorkingBranch(), $newBase);
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	DOMParser	$builder
	 * 		{description}
	 * 
	 * @return	void
	 */
	protected function buildIndexBase($builder)
	{
		// Get Index Base
		$newBase = $builder->create("item", "", $this->getFileName());
		
		// Update Description
		//$code_description = $builder->create("description", $this->description);
		//$builder->append($newBase, $code_description);
		
		// Update Imports
		$imports = $this->buildImportsIndex($builder);
		$builder->append($newBase, $imports);
		
		// Update Dimensions
		$dimensions = $this->buildDimensionIndex($builder);
		$builder->append($newBase, $dimensions);
		
		return $newBase;
	}
	
	/**
	 * {description}
	 * 
	 * @param	DOMParser	$builder
	 * 		{description}
	 * 
	 * @return	void
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
	 * {description}
	 * 
	 * @return	void
	 */
	public function getSourceCode()
	{	
		return parent::getSourceCode();		
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getSourceDoc()
	{
		return parent::getSourceDoc();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$man
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateSourceDoc($man = "")
	{
		return parent::updateSourceDoc($man);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function commit($description)
	{
		return $this->vcsBranch->commit($this->getWorkingBranch(), $description);
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$wrapped
	 * 		{description}
	 * 
	 * @param	{type}	$importsArray
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildHeader($importsArray = array())
	{
		$header = '';
		// Imports
		$importsHeader = $this->buildImports($importsArray);
		
		// Merge		
		$header .= $importsHeader;
		
		return $header;
		//return phpParser::unwrap($header);
	}
	
	/**
	 * {description}
	 * 
	 * @param	array	$importsArray
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildImports($importsArray = array())
	{
		// Build Imports
		$imports = "\n\n";
		$imports .= phpParser::get_comment("Import Packages", $multi = FALSE)."\n";
		$startups = "\n";
		foreach ($this->imports as $lib => $packages)
			foreach ($packages as $pkg)
			{
				$imports .= 'echo "'.$lib.'", "'.$pkg.'";'."\n";
				//$imports .= 'importer::import("'.$lib.'", "'.$pkg.'");'."\n";
				//$startups .= 'BootLoader::addStartupPackage("'.$lib.'", "'.$pkg.'");'."\n";
			}
		
		// Merge and Clear
		$imports .= $startups;
		return preg_replace('/\n$/', '', $imports);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function delete()
	{	
		// Remove Repository
		//return $this->VCS_removeRepository(parent::REPOSITORY, "".$this->devPath);
	}
	
	/**
	 * Export object's source code to given export path.
	 * 
	 * @param	string	$exportPath
	 * 		The export path.
	 * 		It must include the systemRoot. The object's map folder directory follows (library\package\namespace\object).
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function export($exportPath)
	{
		// Export Source Code
		parent::export($exportPath);
	}
	
	public function getJSCode(){}
	public function updateJSCode($code = ""){}
	public function loadJSCode(){}
	public function getCSSCode(){}
	public function updateCSSCode($code = ""){}
	public function loadCSSCode(){}
	public function getCSSModel(){}
	public function updateCSSModel($model = ""){}
}
//#section_end#
?>