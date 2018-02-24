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

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Security", "privileges");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\ModuleProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \API\Security\privileges;
use \UI\Html\DOM;
use \UI\Presentation\notification;

/**
 * Module Container
 * 
 * Builds a module container element.
 * This will be filled asynchronously on content.modified with the module assigned.
 * 
 * @version	0.1-1
 * @created	September 8, 2014, 10:34 (EEST)
 * @revised	September 8, 2014, 10:34 (EEST)
 */
class ModuleContainerPrototype extends UIObjectPrototype
{
	/**
	 * The module id.
	 * 
	 * @type	integer
	 */
	private $moduleID;
	/**
	 * The module's view name.
	 * 
	 * @type	string
	 */
	private $viewName;
	
	/**
	 * Defines the module id and the view name.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		Leave empty to get the module's default view.
	 * 		Empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($moduleID, $viewName = "")
	{
		// Set Module Variables
		$this->moduleID = $moduleID;
		$this->viewName = $viewName;
	}
	
	/**
	 * Builds the module container with the given parameters.
	 * 
	 * @param	array	$attr
	 * 		An array of parameters to be sent to the server by GET method.
	 * 
	 * @param	boolean	$startup
	 * 		Defines whether this container will be invoked to be filled up at startup (on the next content.modified trigger)
	 * 
	 * @param	string	$containerID
	 * 		The id of the module container DOMElement.
	 * 
	 * @param	boolean	$loading
	 * 		Set the page loading indicator.
	 * 
	 * @return	ModuleContainerPrototype
	 * 		The ModuleContainerPrototype object.
	 */
	public function build($attr = array(), $startup = TRUE, $containerID = "", $loading = FALSE)
	{
		// Set Container
		$containerID = (empty($containerID) ? "mdl".$this->moduleID.mt_rand() : $containerID);
		$moduleContainer = DOM::create("div", "", $containerID, "moduleContainer");
		$this->set($moduleContainer);
		
		// Check Access
		$access = privileges::moduleAccess($this->moduleID);
		$notification = new notification();
		
		switch ($access)
		{
			case "uc":
				// Under Construction Message
				$ntf = $notification->build($type = notification::WARNING, $header = TRUE, $timeout = FALSE, $disposable = TRUE)->get();
				$message = $notification->getMessage("warning", "wrn.content_uc");
				$notification->append($message);
				
				DOM::append($moduleContainer, $ntf);
				break;
			case "off":
				// Page off
				$ntf = $notification->build($type = notification::ERROR, $header = TRUE, $timeout = FALSE, $disposable = TRUE)->get();
				$message = $notification->getMessage("debug", "dbg.content_off");
				$notification->append($message);
				
				DOM::append($moduleContainer, $ntf);
				break;
			case "no":
				// Access Denied
				$ntf = $notification->build($type = notification::ERROR, $header = TRUE, $timeout = FALSE, $disposable = TRUE)->get();
				$message = $notification->getMessage("error", "err.access_denied");
				$notification->append($message);
				
				DOM::append($moduleContainer, $ntf);
				break;
			default:
				ModuleProtocol::addAsync($moduleContainer, $this->moduleID, $this->viewName, "", $startup, $attr, $loading);
				break;
		}
		
		return $this;
	}
}
//#section_end#
?>