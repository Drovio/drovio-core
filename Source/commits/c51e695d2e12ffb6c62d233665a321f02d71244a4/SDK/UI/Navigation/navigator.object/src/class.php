<?php
//#section#[header]
// Namespace
namespace UI\Navigation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Navigation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOM");

use \API\Platform\DOM\DOM;

/**
 * Navigator
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	May 6, 2013, 12:47 (EEST)
 * @revised	May 6, 2013, 12:47 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\client\NavigatorProtocol instead.
 */
class navigator
{
	/**
	 * Adds weblink navigation
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$href
	 * 		{description}
	 * 
	 * @param	{type}	$target
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function web($item, $href, $target = "")
	{
		DOM::attr($item, "href", $href);
		DOM::attr($item, "target", $target);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$nav_ref
	 * 		{description}
	 * 
	 * @param	{type}	$nav_targetcontainer
	 * 		{description}
	 * 
	 * @param	{type}	$nav_targetgroup
	 * 		{description}
	 * 
	 * @param	{type}	$nav_navgroup
	 * 		{description}
	 * 
	 * @param	{type}	$nav_display
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function staticNav($item, $nav_ref, $nav_targetcontainer, $nav_targetgroup, $nav_navgroup, $nav_display = "none")
	{
		$staticNav = array();
		$staticNav['ref'] = $nav_ref;
		$staticNav['targetcontainer'] = $nav_targetcontainer;
		$staticNav['targetgroup'] = $nav_targetgroup;
		$staticNav['navgroup'] = $nav_navgroup;
		$staticNav['display'] = $nav_display;
		
		DOM::data($item, "static-nav", $staticNav);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$group
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function selector($item, $group)
	{
		DOM::attr($item, "data-targetgroupid", $group);
	}
	
	
	/**
	 * Adds weblink navigation
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$href
	 * 		{description}
	 * 
	 * @param	{type}	$target
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function add_web_navigation($item, $href, $target = "")
	{
		DOM::attr($item, "href", $href);
		DOM::attr($item, "target", $target);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$nav_ref
	 * 		{description}
	 * 
	 * @param	{type}	$nav_targetcontainer
	 * 		{description}
	 * 
	 * @param	{type}	$nav_targetgroup
	 * 		{description}
	 * 
	 * @param	{type}	$nav_navgroup
	 * 		{description}
	 * 
	 * @param	{type}	$nav_display
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function add_static_navigation($item, $nav_ref, $nav_targetcontainer, $nav_targetgroup, $nav_navgroup, $nav_display = "none")
	{
		$staticNav = array();
		$staticNav['ref'] = $nav_ref;
		$staticNav['targetcontainer'] = $nav_targetcontainer;
		$staticNav['targetgroup'] = $nav_targetgroup;
		$staticNav['navgroup'] = $nav_navgroup;
		$staticNav['display'] = $nav_display;
		
		DOM::data($item, "static-nav", $staticNav);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$group
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function add_static_nav_group_selector($item, $group)
	{
		DOM::attr($item, "data-targetgroupid", $group);
	}
}
//#section_end#
?>