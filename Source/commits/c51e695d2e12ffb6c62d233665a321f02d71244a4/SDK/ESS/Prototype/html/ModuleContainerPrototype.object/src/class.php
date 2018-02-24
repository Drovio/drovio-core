<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\html
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::ModuleProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Security", "privileges");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\server\ModuleProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \API\Security\privileges;
use \UI\Html\DOM;
use \UI\Presentation\notification;

/**
 * Module Container
 * 
 * Builds a module container and the server fills it with the content of the module defined.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:06 (EET)
 * @revised	October 1, 2013, 11:24 (EEST)
 */
class ModuleContainerPrototype extends UIObjectPrototype
{
	/**
	 * The module id
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * The name of the auxiliary of the module
	 * 
	 * @type	string
	 */
	private $action;
	
	/**
	 * Constructor Method. Defines the id (and action) of the module.
	 * 
	 * @param	string	$id
	 * 		The module id
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module (if any)
	 * 
	 * @return	void
	 */
	public function __construct($id, $action = "")
	{
		// Set Module Variables
		$this->id = $id;
		$this->action = $action;
	}
	
	/**
	 * Builds the container
	 * 
	 * @param	array	$attr
	 * 		An array of parameters to be sent to the server by GET method.
	 * 
	 * @param	boolean	$startup
	 * 		Defines whether this container will be filled up at startup (on the next content.modified trigger)
	 * 
	 * @param	string	$containerID
	 * 		The id of the module container as a DOM Object.
	 * 
	 * @return	ModuleContainerPrototype
	 * 		{description}
	 */
	public function build($attr = array(), $startup = TRUE, $containerID = "")
	{
		// Set Container
		$containerID = (empty($containerID) ? "mdl_".$this->id."_".rand() : $containerID);
		$moduleContainer = DOM::create("div", "", $containerID, "moduleContainer");
		$this->set($moduleContainer);
		
		// Check Access
		$access = privileges::moduleAccess($this->id);
		$notification = new notification();
		
		switch ($access)
		{
			case "auth":
			case "onauth":
				// Authenticate User Message
				$ntf = $notification->build("warning", $header = TRUE, $footer = TRUE)->get();
				$message = $notification->getMessage("warning", "wrn.access_auth");
				$notification->append($message);
				
				DOM::append($moduleContainer, $ntf);
				break;
			case "uc":
				// Under Construction Message
				$ntf = $notification->build("warning", $header = TRUE, $footer = TRUE)->get();
				$message = $notification->getMessage("warning", "wrn.content_uc");
				$notification->append($message);
				
				DOM::append($moduleContainer, $ntf);
				break;
			case "off":
				// Page off
				$ntf = $notification->build("error", $header = TRUE, $footer = TRUE)->get();
				$message = $notification->getMessage("debug", "dbg.content_off");
				$notification->append($message);
				
				DOM::append($moduleContainer, $ntf);
				break;
			case "login":
			case "no":
				// Access Denied
				$ntf = $notification->build("error", $header = TRUE, $footer = TRUE)->get();
				$message = $notification->getMessage("error", "err.access_denied");
				$notification->append($message);
				
				DOM::append($moduleContainer, $ntf);
				break;
			default:
				ModuleProtocol::addAsync($moduleContainer, $this->id, $this->action, "", $startup, $attr);
				break;
		}
		
		return $this;
	}
}
//#section_end#
?>