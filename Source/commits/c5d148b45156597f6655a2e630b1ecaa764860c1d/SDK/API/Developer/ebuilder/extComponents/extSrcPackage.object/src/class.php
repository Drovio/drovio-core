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

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "content::resources");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
//importer::import("API", "Developer", "components::prime::packageObject");
//importer::import("API", "Developer", "components::prime::indexing::packageIndex");
//importer::import("API", "Developer", "components::prime::indexing::libraryIndex");
//importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \ESS\Protocol\client\BootLoader;
use \API\Developer\content\resources;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
//use \API\Developer\components\prime\packageObject;
//use \API\Developer\components\prime\indexing\packageIndex;
//use \API\Developer\components\prime\indexing\libraryIndex;
//use \API\Developer\profiler\tester;

/**
 * extension Source Package
 * 
 * Manager source package object in the extension repository.
 * 
 * @version	{empty}
 * @created	July 1, 2013, 18:44 (EEST)
 * @revised	October 11, 2013, 12:20 (EEST)
 * 
 * @deprecated	Use extension
 */
class extSrcPackage //extends packageObject
{	
	/**
	 * a test.
	 * 
	 * @type	integer
	 */
	const TEST_CONST = 1;

	/**
	 * a property
	 * 
	 * @type	string
	 */
	private $testVar; 

	/**
	 * The contructor
	 * 
	 * @param	{type}	$repositoryRoot
	 * 		{description}
	 * 
	 * @param	{type}	$srcRepository
	 * 		{description}
	 * 
	 * @param	{type}	$releaseFolder
	 * 		{description}
	 * 
	 * @return	{empty}
	 * 		{description}
	 */
	public function __construct($repositoryRoot, $srcRepository, $releaseFolder)
	{
		// Set Repository
		$this->setRepository($repositoryRoot, $srcRepository);
		
		// Set Release Folder
		$this->setReleaseFolder($releaseFolder);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @return	{empty}
	 * 		The create return value
	 */
	public function create($libName, $packageName)
	{	
		// Create package
		return parent::create($libName, $packageName);
	}	
	
	/**
	 * Create namespace..
	 * 
	 * @param	string	$libName
	 * 		Test Desc a string
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @param	string	$parentNs
	 * 		Test Desc
	 * 
	 * @return	{empty}
	 * 		true or false
	 * 
	 * @throws	not, found
	 */
	public function createNS($libName, $packageName, $nsName, $parentNs = "")
	{			
		return parent::createNS($libName, $packageName, $nsName, $parentNs);
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$package
	 * 		{description}
	 * 
	 * @return	{empty}
	 * 		{description}
	 */
	public function release($package)
	{
	}	
}
//#section_end#
?>