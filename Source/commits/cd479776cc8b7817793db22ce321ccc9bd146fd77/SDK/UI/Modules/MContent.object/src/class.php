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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("ESS", "Prototype", "ActionFactory");
importer::import("ESS", "Prototype", "html::ModuleContainerPrototype");
importer::import("API", "Literals", "moduleLiteral");
importer::import("UI", "Content", "HTMLContent");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\loaders\ModuleLoader;
use \ESS\Prototype\ActionFactory;
use \ESS\Prototype\html\ModuleContainerPrototype;
use \API\Literals\moduleLiteral;
use \UI\Content\HTMLContent;
use \UI\Html\DOM;

/**
 * Module Content builder
 * 
 * Builds a module content with a specified id and class.
 * It loads module's html and can parse module's literals.
 * 
 * @version	1.0-1
 * @created	June 23, 2014, 12:34 (EEST)
 * @revised	August 23, 2014, 15:42 (EEST)
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
			if (!isset($attributes['scope']))
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
		return new ActionFactory();
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
	 * @return	DOMElement
	 * 		The outer module receiver container.
	 */
	public static function getModuleContainer($moduleID, $viewName = "", $attr = array(), $startup = TRUE, $containerID = "")
	{
		$moduleContainer = new ModuleContainerPrototype($moduleID, $viewName);
		return $moduleContainer->build($attr, $startup, $containerID)->get();
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
			$actionFactory->setModuleAction($element, $moduleID, $viewName);
		}
		
		return $element;
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