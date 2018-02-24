<?php
//#section#[header]
// Namespace
namespace UI\Apps;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Apps
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("ESS", "Prototype", "AppActionFactory");
importer::import("ESS", "Prototype", "content/ApplicationContainerPrototype");
importer::import("AEL", "Literals", "appLiteral");
importer::import("AEL", "Platform", "application");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Content", "HTMLContent");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Prototype\AppActionFactory;
use \ESS\Prototype\content\ApplicationContainerPrototype;
use \AEL\Literals\appLiteral;
use \AEL\Platform\application;
use \API\Resources\filesystem\fileManager;
use \UI\Content\HTMLContent;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \DEV\Apps\application as DEVApplication;
use \DEV\Apps\test\appTester;
use \DEV\Resources\paths;

/**
 * Application Content Builder
 * 
 * Builds an application content with a specified container's id and class.
 * It loads application view's html and can parse application's literals.
 * 
 * @version	3.1-5
 * @created	August 23, 2014, 16:56 (EEST)
 * @updated	February 19, 2015, 12:26 (EET)
 */
class APPContent extends HTMLContent
{
	/**
	 * The default application content holder.
	 * 
	 * @type	string
	 */
	const HOLDER = "#applicationContainer";
	
	/**
	 * The application container class. It is for platform-use only.
	 * 
	 * @type	string
	 */
	const CONTAINER_CLASS = "appContentContainer";
	
	/**
	 * The application's id that loads this object.
	 * 
	 * @type	integer
	 */
	protected $appID;
	
	/**
	 * Initializes the Application Content object.
	 * 
	 * @param	integer	$appID
	 * 		The application's id for this content (if any).
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($appID = "")
	{
		$this->appID = $appID;
	}
	
	/**
	 * Build the outer html content container.
	 * 
	 * @param	string	$id
	 * 		The element's id. Empty by default.
	 * 
	 * @param	string	$class
	 * 		The element's class. Empty by default.
	 * 
	 * @param	boolean	$loadHTML
	 * 		Indicator whether to load html from the designer file.
	 * 
	 * @return	APPContent
	 * 		The APPContent object.
	 */
	public function build($id = "", $class = "", $loadHTML = FALSE)
	{
		// Build HTMLContent
		parent::build($id, $class, $loadHTML);
		
		// Return MContent object
		return $this;
	}
	
	/**
	 * Loads the html content of the view.
	 * It resolves all the resource urls properly.
	 * 
	 * @return	APPContent
	 * 		The APPContent object.
	 */
	protected function loadHTML()
	{
		// Get html file
		$parentFilename = $this->getParentFilename();
		$htmlDirectory = dirname($parentFilename);
		$htmlFileName = str_replace(".php", ".html", basename($parentFilename));
		$htmlFileName = $htmlDirectory."/".$htmlFileName;

		// Get html content
		$htmlContent = fileManager::get($htmlFileName);
		
		// Replace resource variables
		if (!appTester::publisherLock())
		{
			$app = new DEVApplication($this->appID);
			$resourcePath = $app->getResourcesFolder();
			$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
			$resourceUrl = url::resolve("repo", $resourcePath);
			$htmlContent = str_replace("%resources%", $resourceUrl, $htmlContent);
			$htmlContent = str_replace("%{resources}", $resourceUrl, $htmlContent);
			$htmlContent = str_replace("%media%", $resourceUrl, $htmlContent);
			$htmlContent = str_replace("%{media}", $resourceUrl, $htmlContent);
		}
		
		// Append to root element if not empty
		if (!empty($htmlContent))
			DOM::innerHTML($this->get(), $htmlContent);
		
		// Set literals
		$this->loadLiterals();
		
		// Set urls
		$this->resolveUrls();
		
		return $this;
	}
	
	/**
	 * Loads application's literals in the designer's html file.
	 * 
	 * @return	APPContent
	 * 		The APPContent object.
	 */
	protected function loadLiterals()
	{
		// Check if application id is not set
		if (empty($this->appID))
			return $this;

		// Load application literals
		$containers = DOM::evaluate("//*[@data-literal]");
		foreach ($containers as $container)
		{
			// Get literal attributes
			$value = DOM::attr($container, "data-literal");
			$attributes = json_decode($value, TRUE);

			// Get literal
			$literal = appLiteral::get($attributes['scope'], $attributes['name']);
			
			// If literal is valid, append to container
			if (!empty($literal))
				DOM::append($container, $literal);
			
			// Remove literal attribute
			DOM::attr($container, "data-literal", null);
		}
		
		return $this;
	}
	
	/**
	 * Creates a new Application Action Factory instance.
	 * 
	 * @return	AppActionFactory
	 * 		An instance of the Application Action Factory object.
	 */
	public function getActionFactory()
	{
		return new AppActionFactory();
	}
	
	/**
	 * Builds an async application view container and returns the DOMElement.
	 * 
	 * @param	integer	$appID
	 * 		The application ID to load async.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name to load.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module with GET method during loading.
	 * 		It is an array (attrName => attrValue)
	 * 
	 * @param	boolean	$startup
	 * 		Sets the module to load at startup (next content.modified).
	 * 
	 * @param	string	$containerID
	 * 		The id attribute of the container DOM Object.
	 * 
	 * @param	boolean	$loading
	 * 		Set the page loading indicator.
	 * 
	 * @param	boolean	$preload
	 * 		Set the container to preload the content synchronously.
	 * 
	 * @return	DOMElement
	 * 		The outer application receiver container.
	 * 
	 * @deprecated	Use getAppViewContainer() instead.
	 */
	public static function getAppContainer($appID, $viewName, $attr = array(), $startup = TRUE, $containerID = "", $loading = FALSE, $preload = FALSE)
	{
		return self::getAppViewContainer($viewName, $attr, $startup, $containerID, $loading, $preload);
	}
	
	/**
	 * Builds an async application view container and returns the DOMElement.
	 * 
	 * It will load a view of the current running application.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name to load.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module with GET method during loading.
	 * 		It is an array (attrName => attrValue)
	 * 
	 * @param	boolean	$startup
	 * 		If this is set to true, the application view will load on next content.modified asynchonously.
	 * 
	 * @param	string	$containerID
	 * 		The id attribute of the container DOM Object.
	 * 
	 * @param	boolean	$loading
	 * 		Set the page loading indicator.
	 * 
	 * @param	boolean	$preload
	 * 		If this set to true, the container will ignore the startup attribute and will load the application view synchronously.
	 * 
	 * @return	DOMElement
	 * 		The outer application receiver container.
	 */
	public static function getAppViewContainer($viewName, $attr = array(), $startup = TRUE, $containerID = "", $loading = FALSE, $preload = FALSE)
	{
		$appContainer = new ApplicationContainerPrototype($viewName);
		return $appContainer->build($attr, $startup, $containerID, $loading, $preload)->get();
	}
	
	/**
	 * Get the ServerReport of this HTML Application Cpontent or the object holder.
	 * 
	 * @param	string	$holder
	 * 		The content holder. If empty, it gets the default application content holder.
	 * 
	 * @return	mixed
	 * 		The server report or the object holder.
	 */
	public function getReport($holder = "")
	{
		// Add container class to holder (in order to be removed later from player)
		HTML::addClass($this->get(), self::CONTAINER_CLASS);
		
		// Support loading an application view inside another view
		// Check the Application's loading depth if it is bigger than 1
		// If more than one, it's an inner loading and return DOMElement
		if (application::getLoadingDepth() > 1)
		{
			application::decLoadingDepth();
			return $this->get();
		}
		
		// Return report
		return parent::getReport($holder);
	}
	
	/**
	 * Gets the parent's filename for loading the html from external file.
	 * 
	 * @return	string
	 * 		The parent script name.
	 */
	protected function getParentFilename()
	{
		$stack = debug_backtrace();
		return $stack[3]['file'];
	}
}
//#section_end#
?>