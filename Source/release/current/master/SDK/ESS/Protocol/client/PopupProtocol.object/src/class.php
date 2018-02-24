<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

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
 * @package	Protocol
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\ModuleProtocol;
use \UI\Html\DOM;

/**
 * Popup Protocol
 * 
 * This is the protocol responsible for showing content in a popup form.
 * 
 * @version	0.1-1
 * @created	March 8, 2013, 10:52 (EET)
 * @revised	November 6, 2014, 13:33 (EET)
 */
class PopupProtocol
{
	/**
	 * Adds the popup action listener to the given DOMElement.
	 * 
	 * @param	DOMElement	$item
	 * 		The element that will call the popup.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$auxName
	 * 		The auxiliary name of the module (if specified).
	 * 
	 * @param	array	$attr
	 * 		An array of attributes to be passed to the module with the GET method.
	 * 
	 * @return	void
	 */
	public static function addAction($item, $moduleID, $auxName = "", $attr = array())
	{
		// Set Module Protocol Action
		ModuleProtocol::addAction($item, "pp", $moduleID, $auxName, $attr);
	}
}
//#section_end#
?>