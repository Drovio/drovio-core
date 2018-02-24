<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * Client-sided Navigator Protocol
 * 
 * Defines a protocol for the navigation and presentation of elements on the client's browser.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 9:22
 * @revised	March 7, 2013, 9:22
 */
class NavigatorProtocol
{
	/**
	 * Adds weblink navigation
	 * 
	 * @param	DOMElement	$element
	 * 		The element to receive the navigation handler
	 * 
	 * @param	string	$href
	 * 		The href value
	 * 
	 * @param	string	$target
	 * 		The target value.
	 * 		According to W3C, the possible values are:
	 * 		- "_blank"
	 * 		- "_self"
	 * 		- "_parent"
	 * 		- "_top"
	 * 		- "[framename]"
	 * 
	 * @return	void
	 */
	public static function web($element, $href, $target = "")
	{
		DOM::attr($element, "href", $href);
		DOM::attr($element, "target", $target);
	}
	
	/**
	 * Adds static navigation handler
	 * 
	 * @param	DOMElement	$element
	 * 		The element to receive the navigation handler
	 * 
	 * @param	string	$ref
	 * 		The target's id to perform the action
	 * 
	 * @param	string	$targetcontainer
	 * 		The container's class of the group in which the target resides
	 * 
	 * @param	string	$targetgroup
	 * 		The group of the items to handle together when performing the action to the target.
	 * 		References the data-targetgroupid value
	 * 
	 * @param	string	$navgroup
	 * 		The group of navigation items, among which the handler element will be selected
	 * 
	 * @param	string	$display
	 * 		Defines the type of action for the rest items of the group.
	 * 		Accepted values:
	 * 		- "none" : hides all other items
	 * 		- "all" : shows all other items
	 * 		- "toggle" : toggles the appearance of the handler item
	 * 
	 * @return	void
	 */
	public static function staticNav($element, $ref, $targetcontainer, $targetgroup, $navgroup, $display = "none")
	{
		$staticNav = array();
		$staticNav['ref'] = $ref;
		$staticNav['targetcontainer'] = $targetcontainer;
		$staticNav['targetgroup'] = $targetgroup;
		$staticNav['navgroup'] = $navgroup;
		$staticNav['display'] = $display;
		
		DOM::data($element, "static-nav", $staticNav);
	}
	
	/**
	 * Adds static navigation group selector (staticNav's targetgroup)
	 * 
	 * @param	DOMElement	$element
	 * 		The element to receive the selector
	 * 
	 * @param	string	$group
	 * 		The name of the group
	 * 
	 * @return	void
	 */
	public static function selector($element, $group)
	{
		DOM::attr($element, "data-targetgroupid", $group);
	}
}
//#section_end#
?>