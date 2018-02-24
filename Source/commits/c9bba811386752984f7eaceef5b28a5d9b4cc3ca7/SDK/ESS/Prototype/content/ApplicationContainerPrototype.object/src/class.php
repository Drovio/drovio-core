<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\content;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\content
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "ApplicationProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("AEL", "Platform", "application");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\ApplicationProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \AEL\Platform\application;
use \UI\Html\DOM;

/**
 * Application View Container
 * 
 * Builds an application view container element.
 * This will be filled asynchronously on content.modified with the application view assigned, unless it is set for preloading.
 * 
 * The application must be initialized before creating an application container.
 * 
 * @version	1.0-1
 * @created	September 8, 2014, 10:45 (EEST)
 * @revised	December 18, 2014, 17:30 (EET)
 */
class ApplicationContainerPrototype extends UIObjectPrototype
{
	/**
	 * The application id.
	 * 
	 * @type	integer
	 */
	private $appID;
	/**
	 * The application's view name.
	 * 
	 * @type	string
	 */
	private $viewName;
	
	/**
	 * Defines the application id and the view name.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name.
	 * 
	 * @return	void
	 */
	public function __construct($viewName)
	{
		// Normalize arguments (compatibility)
		$argNum = func_num_args();
		$args = func_get_args();
		if ($argNum >= 2 && is_numeric($args[0]))
			$viewName = $args[1];
		else if ($argNum == 1 && is_numeric($args[0]))
			$viewName = "";
				
		// Get application id to request
		$applicationID = application::init();
		if (empty($applicationID))
			return FALSE;
			
		// Set Application Variables
		$this->appID = $applicationID;
		$this->viewName = $viewName;
	}
	
	/**
	 * Builds the application container with the given parameters.
	 * 
	 * @param	array	$attr
	 * 		An array of parameters to be sent to the server by GET method.
	 * 
	 * @param	boolean	$startup
	 * 		Defines whether this container will be invoked to be filled up at startup (on the next content.modified trigger)
	 * 
	 * @param	string	$containerID
	 * 		The id of the container DOMElement.
	 * 
	 * @param	boolean	$loading
	 * 		Set the page loading indicator.
	 * 
	 * @param	boolean	$preload
	 * 		Set the container to preload the content synchronously.
	 * 
	 * @return	ApplicationContainerPrototype
	 * 		The ApplicationContainerPrototype object.
	 */
	public function build($attr = array(), $startup = TRUE, $containerID = "", $loading = FALSE, $preload = FALSE)
	{
		// Set Container
		$containerID = (empty($containerID) ? "appvc".$this->appID.mt_rand() : $containerID);
		$appContainer = DOM::create("div", "", $containerID, "moduleContainer");
		$this->set($appContainer);
		
		// Set async attributes
		$startup = ($preload ? FALSE : $startup);
		ApplicationProtocol::addAsync($appContainer, $this->appID, $this->viewName, "", $startup, $attr, $loading);
		
		// If preload, load view
		if ($preload)
		{
			// Merge attributes with $_GET
			$_GET = array_merge($_GET, $attr);
			
			// Load view and append to container
			$appContent = application::loadView($this->viewName);
			DOM::append($appContainer, $appContent);
		}
		
		return $this;
	}
}
//#section_end#
?>