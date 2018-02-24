<?php
//#section#[header]
// Namespace
namespace UI\Modules;

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
 * @package	Modules
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("ESS", "Protocol", "loaders/ModuleLoader");
importer::import("ESS", "Prototype", "ModuleActionFactory");
importer::import("ESS", "Prototype", "content/ModuleContainerPrototype");
importer::import("API", "Literals", "moduleLiteral");
importer::import("UI", "Content", "HTMLContent");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Modules", "test/moduleTester");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\loaders\ModuleLoader;
use \ESS\Prototype\ModuleActionFactory;
use \ESS\Prototype\content\ModuleContainerPrototype;
use \API\Literals\moduleLiteral;
use \API\Resources\filesystem\fileManager;
use \UI\Content\HTMLContent;
use \UI\Html\DOM;
use \DEV\Modules\modulesProject;
use \DEV\Modules\test\moduleTester;
use \DEV\Resources\paths;

/**
 * Module Content builder
 * 
 * Builds a module content with a specified id and class.
 * It loads module's html and can parse module's literals.
 * 
 * @version	1.3-1
 * @created	June 23, 2014, 12:34 (EEST)
 * @updated	January 12, 2015, 10:26 (EET)
 */
class MContent extends HTMLContent
{
	/**
	 * The module's id that loads this object.
	 * 
	 * @type	integer
	 */
	protected $moduleID;
	
	/**
	 * Initializes the Module Content object.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id for this content (if any).
	 * 		Empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($moduleID = "")
	{
		$this->moduleID = $moduleID;
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
	 * @return	MContent
	 * 		The MContent object.
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
	 * @return	MContent
	 * 		The MContent object.
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
		if (moduleTester::status($this->moduleID))
		{
			$project = new modulesProject();
			$resourcePath = $project->getResourcesFolder()."/media/";
			$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
			$resourceUrl = url::resolve("repo", $resourcePath, "http");
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
	 * Loads module's literals in the designer's html file.
	 * 
	 * @return	MContent
	 * 		The MContent object.
	 */
	protected function loadLiterals()
	{
		// Load core literals
		parent::loadLiterals();
		
		// Load module literals
		$containers = DOM::evaluate("//*[@data-literal]");
		foreach ($containers as $container)
		{
			// Get literal attributes
			$value = DOM::attr($container, "data-literal");
			$attributes = json_decode($value, true);
			
			// Get literal
			if (!isset($attributes['scope']) && isset($attributes['name']))
				$literal = moduleLiteral::get($this->moduleID, $attributes['name']);
			
			// If literal is valid, append to container
			if (!empty($literal))
				DOM::append($container, $literal);
			
			// Remove literal attribute
			DOM::attr($container, "data-literal", null);
		}
		
		return $this;
	}
	
	/**
	 * Creates a new Action Factory instance.
	 * 
	 * @return	ActionFactory
	 * 		An instance of the Action Factory object.
	 */
	public function getActionFactory()
	{
		return new ModuleActionFactory();
	}
	
	/**
	 * Builds an async module container and returns the DOMElement.
	 * 
	 * @param	integer	$moduleID
	 * 		The module ID to load async.
	 * 
	 * @param	string	$viewName
	 * 		The view name for the module action, if any.
	 * 		If empty, get the default module view.
	 * 		It is empty by default.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module with GET method during loading.
	 * 		It is an array (attrName => attrValue)
	 * 
	 * @param	boolean	$startup
	 * 		Sets the module to load at startup (next content.modified).
	 * 
	 * @param	string	$containerID
	 * 		The id attribute of the module container DOM Object.
	 * 
	 * @param	boolean	$loading
	 * 		Set the page loading indicator.
	 * 
	 * @param	boolean	$preload
	 * 		Set the container to preload the content synchronously.
	 * 
	 * @return	DOMElement
	 * 		The outer module receiver container.
	 */
	public static function getModuleContainer($moduleID, $viewName = "", $attr = array(), $startup = TRUE, $containerID = "", $loading = FALSE, $preload = FALSE)
	{
		$moduleContainer = new ModuleContainerPrototype($moduleID, $viewName);
		return $moduleContainer->build($attr, $startup, $containerID, $loading, $preload)->get();
	}
	
	/**
	 * Builds an html weblink and adds a module action to it, if any.
	 * 
	 * @param	string	$href
	 * 		The weblink href attribute.
	 * 
	 * @param	mixed	$content
	 * 		The weblink content.
	 * 		It can be text or DOMElement.
	 * 
	 * @param	string	$target
	 * 		The target attribute. It is "_self" by default.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id for module action.
	 * 		This can be used to load a page module async and redirect.
	 * 		It can be used only for urls of the same subdomain.
	 * 
	 * @param	string	$viewName
	 * 		The view name for the module action, if any.
	 * 		If empty, get the default module view.
	 * 		It is empty by default.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement object.
	 */
	public function getWeblink($href, $content = "", $target = "_self", $moduleID = "", $viewName = "")
	{
		// Get the weblink
		$weblink = parent::getWeblink($href, $content, $target);
		
		// Set module action if any
		if (!empty($moduleID))
		{
			$actionFactory = $this->getActionFactory();
			$actionFactory->setModuleAction($weblink, $moduleID, $viewName);
		}
		
		return $weblink;
	}
	
	/**
	 * Returns the HTML Report for this html content.
	 * 
	 * @param	string	$holder
	 * 		The holder to put the content to.
	 * 		It is a css selector.
	 * 
	 * @param	string	$method
	 * 		The report method.
	 * 		For more information, see HTMLServerReport.
	 * 
	 * @return	mixed
	 * 		The html server report output or the module output if it is an internal call.
	 */
	public function getReport($holder = "", $method = HTMLServerReport::REPLACE_METHOD)
	{
		// Support loading a module inside another module
		// Check the ModuleLoader's depth if it is bigger than 1
		// If more than one, it's an inner loading and return DOMElement
		if (ModuleLoader::getLoadingDepth() > 1)
		{
			ModuleLoader::decLoadingDepth();
			return $this->get();
		}
		
		// Return HTMLContent Report
		return parent::getReport($holder, $method);
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