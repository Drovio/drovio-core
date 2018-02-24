<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter\appComponents;

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
 * @namespace	\appcenter\appComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Developer", "appcenter::appComponents::appSrcPackage");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Developer", "components::prime::classObject2");
importer::import("API", "Developer", "components::prime::indexing::classIndex");
importer::import("API", "Developer", "content::document::parsers::phpParser");

use \API\Developer\appcenter\application;
use \API\Developer\appcenter\appComponents\appSrcPackage;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Developer\components\prime\classObject2;
use \API\Developer\components\prime\indexing\classIndex;
use \API\Developer\content\document\parsers\phpParser;


/**
 * Application Source Object
 * 
 * Application Source Object Manager
 * 
 * @version	{empty}
 * @created	October 29, 2013, 19:58 (EET)
 * @revised	April 6, 2014, 1:48 (EEST)
 * 
 * @deprecated	Use \DEV\Apps\components\source\sourceObject instead.
 */
class appSrcObject extends classObject2
{
	/**
	 * The application developer path.
	 * 
	 * @type	string
	 */
	private $devPath;
	
	/**
	 * The application name. It is used as library to the source object.
	 * 
	 * @type	string
	 */
	private $appName;
	
	/**
	 * Initializes the object with all the variables.
	 * 
	 * @param	string	$appName
	 * 		The application name.
	 * 
	 * @param	string	$devPath
	 * 		The application developer path.
	 * 
	 * @param	string	$package
	 * 		The object package name.
	 * 
	 * @param	string	$namespace
	 * 		The object namespace.
	 * 
	 * @param	string	$name
	 * 		The object name. Leave empty for new object.
	 * 
	 * @return	void
	 */
	public function __construct($appName, $devPath, $package, $namespace = "", $name = NULL)
	{
		// Set Developer's Application Path
		$this->devPath = $devPath;
		$this->appName = $appName;
		
		// Put your constructor method code here.
		parent::__construct($this->devPath, TRUE, $this->appName, $package, $namespace, $name);
	}
	
	/**
	 * Create a new application source object.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @param	string	$title
	 * 		The object title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $title)
	{
		// Create index
		$title = $name;
		$proceed = classIndex::createIndex($this->devPath."/.app/".appSrcPackage::LIB_NAME.".xml", $this->appName, $this->package, $this->namespace, $name, $title);
		
		if (!$proceed)
			return $proceed;
		
		// Create vcs item
		return parent::create($name, FALSE, application::SOURCE_FOLDER);
	}
	
	/**
	 * Update the application source object.
	 * 
	 * @param	string	$code
	 * 		The object's source code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateSourceCode($code = "")
	{
		// Get SDK Object Class Header
		$header = $this->buildHeader();
		
		// Clear code
		$code = phpParser::clearCode($code);
		$code = phpParser::safe($code);
		
		// Update Source Code
		return parent::updateSourceCode($header, $code);
	}
	
	/**
	 * The object's header code.
	 * 
	 * @return	string
	 * 		The object's header code.
	 */
	private function buildHeader()
	{  
		$path = systemRoot.paths::getDevRsrcPath()."/Content/appCenter/Headers/srcObjectPrivate.inc";
		$header = fileManager::get($path);
		return phpParser::unwrap($header);
	}
}
//#section_end#
?>