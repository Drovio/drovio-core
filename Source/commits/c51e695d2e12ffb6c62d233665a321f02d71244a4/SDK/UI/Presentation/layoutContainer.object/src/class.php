<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * Layout Container Formatted
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	September 16, 2013, 14:25 (EEST)
 * @revised	September 16, 2013, 14:25 (EEST)
 * 
 * @deprecated	This class is deprecated. CSS is available at modules now.
 */
class layoutContainer
{	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function container($id = "", $class = "")
	{
		return DOM::create("div", "", $id, "uiLayoutContainer".($class == "" ? "" : " $class"));
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$orientation
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function border($item, $orientation = "", $size = "s")
	{
		return DOM::appendAttr($item, "class", "brd".$orientation.$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function boxSizing($item, $type = "b")
	{
		return DOM::appendAttr($item, "class", "bxs".$type);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$orientation
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function padding($item, $orientation = "", $size = "s")
	{
		return DOM::appendAttr($item, "class", "pd".$orientation.$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$orientation
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function margin($item, $orientation = "", $size = "s")
	{
		return DOM::appendAttr($item, "class", "mrg".$orientation.$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function fontSize($item, $size = "s")
	{
		return DOM::appendAttr($item, "class", "fs".$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$weight
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function fontWeight($item, $weight = "n")
	{
		return DOM::appendAttr($item, "class", "fw".$weight);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$color
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function fontColor($item, $color = "g")
	{
		return DOM::appendAttr($item, "class", "fc".$color);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$align
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function textAlign($item, $align = "left")
	{
		return DOM::appendAttr($item, "class", "txtA".$align);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$pos
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function position($item, $pos = "")
	{
		return DOM::appendAttr($item, "class", "pos-".$pos);
	}
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$float
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function floatPosition($item, $float = "")
	{
		return DOM::appendAttr($item, "class", "f-".$float);
	}
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$clear
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function clear($item, $clear = "")
	{
		return DOM::appendAttr($item, "class", "clr-".$clear);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$visibility
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function visibility($item, $visibility = "")
	{
		return DOM::appendAttr($item, "class", "vis-".$visibility);
	}
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function noDisplay($item)
	{
		return DOM::appendAttr($item, "class", "noDisplay");
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function get_container($id = "", $class = "")
	{
		return DOM::create("div", "", $id, "uiLayoutContainer".($class == "" ? "" : " $class"));
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$orientation
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_border($builder, $item, $orientation = "", $size = "s")
	{
		return DOM::appendAttr($item, "class", "brd".$orientation.$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_boxSizing($builder, $item, $type = "b")
	{
		return DOM::appendAttr($item, "class", "bxs".$type);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$orientation
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_padding($builder, $item, $orientation = "", $size = "s")
	{
		return DOM::appendAttr($item, "class", "pd".$orientation.$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$orientation
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_margin($builder, $item, $orientation = "", $size = "s")
	{
		return DOM::appendAttr($item, "class", "mrg".$orientation.$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_fontSize($builder, $item, $size = "s")
	{
		return DOM::appendAttr($item, "class", "fs".$size);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$weight
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_fontWeight($builder, $item, $weight = "n")
	{
		return DOM::appendAttr($item, "class", "fw".$weight);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$color
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_fontColor($builder, $item, $color = "g")
	{
		return DOM::appendAttr($item, "class", "fc".$color);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$align
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_textAlign($builder, $item, $align = "left")
	{
		return DOM::appendAttr($item, "class", "txtA".$align);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$pos
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_position($builder, $item, $pos = "")
	{
		return DOM::appendAttr($item, "class", "pos-".$pos);
	}
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$float
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_float($builder, $item, $float = "")
	{
		return DOM::appendAttr($item, "class", "f-".$float);
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$visibility
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_visibility($builder, $item, $visibility = "")
	{
		return DOM::appendAttr($item, "class", "vis-".$visibility);
	}
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function add_noDisplay($builder, $item)
	{
		return DOM::appendAttr($item, "class", "noDisplay");
	}
}
//#section_end#
?>