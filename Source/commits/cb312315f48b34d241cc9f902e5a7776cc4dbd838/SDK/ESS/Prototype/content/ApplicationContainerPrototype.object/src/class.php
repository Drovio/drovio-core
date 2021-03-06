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
importer::import("API", "Security", "privileges");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\ApplicationProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \API\Security\privileges;
use \UI\Html\DOM;
use \UI\Presentation\notification;

/**
 * Application Container
 * 
 * Builds an application container element.
 * This will be filled asynchronously on content.modified with the module assigned.
 * 
 * @version	0.1-1
 * @created	September 8, 2014, 10:45 (EEST)
 * @revised	September 8, 2014, 10:45 (EEST)
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
	 * @param	integer	$appID
	 * 		The application id to load.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name.
	 * 
	 * @return	void
	 */
	public function __construct($appID, $viewName)
	{
		// Set Application Variables
		$this->appID = $appID;
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
	 * @return	ApplicationContainerPrototype
	 * 		The ApplicationContainerPrototype object.
	 */
	public function build($attr = array(), $startup = TRUE, $containerID = "", $loading = FALSE)
	{
		// Set Container
		$containerID = (empty($containerID) ? "app".$this->appID.mt_rand() : $containerID);
		$appContainer = DOM::create("div", "", $containerID, "moduleContainer");
		$this->set($appContainer);
		
		ApplicationProtocol::addAsync($appContainer, $this->appID, $this->viewName, "", $startup, $attr, $loading);
		
		return $this;
	}
}
//#section_end#
?>