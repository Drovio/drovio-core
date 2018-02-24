<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

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
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */



/**
 * Ribbon Panel
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:00 (EEST)
 * @revised	April 5, 2013, 10:00 (EEST)
 * 
 * @deprecated	Use \UI\Html\pageComponents\ribbonComponents\ribbonPanel instead.
 */
class panel
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $panel;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $group;
	
	/**
	 * _____ Constructor
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		$this->panel = DOM::create('div', "", $id, "panel");
	}
	
	/**
	 * Builds a ribbon panel
	 * 
	 * @return	void
	 */
	public function get_panel()
	{
		return $this->panel;
	}
	
	/**
	 * Inesrts a group into the panel
	 * 
	 * @return	void
	 */
	public function insert_group()
	{
		$this->group = DOM::create('div', "", "", "group");
		DOM::append($this->panel, $this->group);
	}
	
	/**
	 * Creates and inserts a panel item into a container (panel | group)
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$img
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_item($type = "small", $title = "", $img = "", $selected = FALSE)
	{
		if ($type != "small" && $type != "big")
			$type = "small";
		
		// Create item
		$item = DOM::create('a', "", "", "panel_item $type".($selected ? " selected" : ""));
		
		if (isset($this->group))
			DOM::append($this->group, $item);
		else
			DOM::append($this->panel, $item);
		
		// Create item title container
		$item_title = DOM::create('div', "", "", "item_title");
		DOM::append($item, $item_title);
		
		// Create item image
		if ($img != "")
		{
			$img_div = DOM::create('img', "", "", "item_img");
			DOM::attr($img_div, 'src', $img);
			DOM::append($item_title, $img_div);
		}
		
		// Insert item title
		if (gettype($title) == "string")
		{
			$hd_title = DOM::create('span', $title, "", "title");
			DOM::attr($item_title, 'title', $title);
		}
		else
		{
			$hd_title = $title;
			DOM::attr($item_title, 'title', $title->nodeValue);
		}
		DOM::append($item_title, $hd_title);
		
		return $item;
	}
}
//#section_end#
?>