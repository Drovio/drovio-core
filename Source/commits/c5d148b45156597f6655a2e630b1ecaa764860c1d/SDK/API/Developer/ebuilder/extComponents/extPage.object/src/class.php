<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder\extComponents;

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
 * @namespace	\ebuilder\extComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

//importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

//use \API\Developer\versionControl\vcsManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Extension Page
 * 
 * This class represents / manages an extension page object.
 * 
 * @version	{empty}
 * @created	July 10, 2013, 17:47 (EEST)
 * @revised	July 11, 2013, 13:53 (EEST)
 */
class extPage //extends vcsManager
{	
	/**
	 * The (filetype) extension of object
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "php";
	
	/**
	 * The repository path
	 * 
	 * @type	string
	 */
	private $devPath;
		
	/**
	 * The ebLibrary packages / namespace name that this object imports
	 * 
	 * @type	array
	 */
	private $imports = array();
	
	
	/**
	 * The absent dimension of object
	 * 
	 * @type	array
	 */
	private $dimensions = array(
				'width' => 0,
				'height' => 0);	
	
	/**
	 * {description}
	 * 
	 * @param	string	$devPath
	 * 		{description}
	 * 
	 * @param	string	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($devPath, $name = "")
	{
		// Put your constructor method code here.
		$this->devPath = $devPath;
		if (!empty($name))
		{
			$this->VCS_initialize($this->devPath, "", $name, self::FILE_TYPE);
			$this->name = $name;
		}
	}
	
	/**
	 * {description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getImports()
	{
		return $this->imports;
	}
	
	/**
	 * {description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function  getFileName()
	{
	 	return $this->name;
	}
	
	/**
	 * {description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function  getDimensions()
	{
	 	return $this->dimensions;
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
	 * @param	integer	$width
	 * 		{description}
	 * 
	 * @param	integer	$height
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function setDimensions($width, $height)
	{
	 	$this->dimensions['width'] = $width;
		$this->dimensions['height'] = $height;
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	string	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function create($name)
	{
		$this->name = $name;	
		// Initialize VCS
		$this->VCS_initialize($this->devPath, "", $name, self::FILE_TYPE);
		
		// Initalize variables
		$this->setImports($imports = array());

		// Create script
		$this->VCS_createObject();
		$this->update();
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	string	$code
	 * 		{description}
	 * 
	 * @return	mixed
	 * 		Bool : False
	 * 		Int : File size
	 */
	public function update($code = "")
	{
		// Get Object Folder Path
		$sourceFile = $this->vcsTrunk->getPath($this->getWorkingBranch($this->name));
		
		// Prepare	
		// Set source code (default if not given)
		$code = (trim($code) == "" ? $this->getDefaultSourceCode() : $code);
		$code = phpParser::clearCode($code);
		$sourceCode = phpCoder::section($code, "code"); 
		
		$header = $this->buildHeader(TRUE, $importsArray);		
		// Merge all source code
		$sourceCode = $header.$sourceCode;
		$wrappedCode = phpParser::wrap($sourceCode);
				
		// Create temp file to check syntax
		$tempFile = $filePath.".temp";
		fileManager::create($tempFile, $wrappedCode);
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;
			
		// Remove temp file
		fileManager::remove($tempFile);
		
		// Update Index
		$this->updateIndexInfo();
		
		// Update File
		return (fileManager::put($sourceFile, $wrappedCode) !== FALSE);	
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
	 * @param	DOMParser	$builder
	 * 		{description}
	 * 
	 * @return	void
	 */
	protected function buildDimensionIndex($builder)
	{
		// Create import div
		$base = $builder->create("dimensions");
		
		// Set imports
		foreach ($this->dimensions as $key => $value) 
		{
			// Build item
			$object = $builder->create("dim");
			$builder->attr($base, "title", $key);
			$builder->attr($base, "value", $value);
			$builder->append($base, $object);
		}
		
		return $base;
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
		// Load Dimensions
		$this->loadDimensions($parser, $base);
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
	 * @param	DOMParser	$parser
	 * 		{description}
	 * 
	 * @param	DOMElement	$base
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function loadDimensions($parser, $base)
	{
		$this->dimensions = array();
		$dimensionsBase = $parser->evaluate("dimensions", $base)->item(0);
		
		if (is_null($dimensionsBase))
			return;			
		
		$dimensionObject = $parser->evaluate("dim", $importsBase);
		foreach ($dimensionObject  as $object)
			$this->dimensions[$parser->attr($object, "title")] = $parser->attr($object, "value");
	}
	
	/**
	 * {description}
	 * 
	 * @return	mixed
	 * 		string
	 * 		bool false
	 */
	public function getSourceCode()
	{
		// Get Object Folder Path
		$sourceFile = $this->vcsTrunk->getPath($this->getWorkingBranch($this->name));
						
		// Load source code from source		
		$source = fileManager::get_contents($sourceFile);
		$sections = phpCoder::sections($source);
		// Return source code
		return trim($sections["code"]);
	}
		
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	protected function getDefaultSourceCode()
	{
		// Get Default Module Code
		$sourceCode = fileManager::get_contents(systemRoot.paths::getDevRsrcPath()."/Content/ebuilder/Code/default.inc");
		
		// Clear Delimiters
		$sourceCode = phpParser::unwrap($sourceCode);
		
		// Wrap to section
		return trim($sourceCode);
	}
	
	/**
	 * {description}
	 * 
	 * @param	boolean	$wrapped
	 * 		{description}
	 * 
	 * @param	array	$importsArray
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildHeader($wrapped = FALSE, $importsArray = array())
	{
		// Get Headers
		$path = systemRoot.paths::getDevRsrcPath()."/Content/ebuilder/Headers/page.inc";
		$privateHeader = fileManager::get_contents($path);
		$privateHeader = phpParser::unwrap($privateHeader);
		
		// Imports
		$importsHeader = $this->buildImports($importsArray);
		
		// Merge
		$headerContent = "";
		$headerContent .= $privateHeader;
		$headerContent .= $importsHeader;
		
		return ($wrapped ? phpCoder::section($headerContent, "header") : $headerContent);
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
	 * @param	string	$description
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
	 * @return	boolen
	 * 		{description}
	 */
	public function delete()
	{	
		// Remove Repository
		return $this->VCS_removeObject();
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
		// Get Head Object Path
		$headPath = $this->vcsBranch->getHeadPath();
		if (is_dir($headPath."/"))
			return FALSE;
		
		$sourceFile = $headPath;
		$phpCheck = phpParser::check_syntax($sourceFile);
		if (!$phpCheck)
			return $phpCheck;
		
		// Export Object Path
		$sourceCodeObjectPath = $exportPath."/".$this->name.".".SELF::FILE_TYPE;

		// Export Source Code
		$finalCode = fileManager::get_contents($sourceFile);
		fileManager::create($sourceCodeObjectPath, $finalCode);
		
		return TRUE;
	}
}
//#section_end#
?>