<?php
//#section#[header]
// Namespace
namespace API\Developer\resources\layouts;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\resources\layouts
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "resources::layouts::AbstractLayout");

use \API\Developer\resources\layouts\AbstractLayout;

/**
 * eBuilder Layouts Manager Class
 * 
 * Managew / Edits / Creates layouts for eBuilder system pages using / extending the functionality of AbstractLayout class.
 * 
 * @version	{empty}
 * @created	April 12, 2013, 10:54 (EEST)
 * @revised	April 12, 2013, 10:54 (EEST)
 */
class ebuilderLayout extends AbstractLayout
{
	/**
	 * The name of folder whitch stores the layouts.
	 * 
	 * @type	string
	 */
	private $type = "ebuilder";

	/**
	 * Constructor Method
	 * If paremeter if provided loads the layout object given by the parameter value.
	 * 
	 * @param	string	$layoutName
	 * 		Layout object name.
	 * 
	 * @return	void
	 */
	public function __construct($layoutName = NULL)
	{
		parent::__construct($this->type);
		if(!is_null($layoutName))
		{
			$this->initialize($layoutName);
		}
	}
	
	protected function getWrapper($parser = '', $content = '')
	{
		$wrapper =  $parser->create('div');
		
		$main = $parser->create('div', '', 'content');
		$parser->append($wrapper, $main);
	
		$startComment = $parser->comment('Editable Start - Write layout Code into folloing div');
		$parser->append($main, $startComment);
		
		$parser->innerHTML($main, $parser->innerHTML($main).$content);
		
		$endComment = $parser->comment('Editable End');
		$parser->append($main, $endComment);		
		
		
		
		return $wrapper;	
	}	
}
//#section_end#
?>