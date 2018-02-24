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

importer::import("UI", "Html", "DOM");
use \UI\Html\DOM;

use \API\Developer\resources\layouts\AbstractLayout;

/**
 * system Layouts Manager Class
 * 
 * Managew / Edits / Creates layouts for system pages using / extending the functionality of AbstractLayout class.
 * 
 * @version	{empty}
 * @created	April 12, 2013, 10:54 (EEST)
 * @revised	April 12, 2013, 10:54 (EEST)
 */
class systemLayout extends AbstractLayout
{
	/**
	 * The name of folder whitch stores the layouts.
	 * 
	 * @type	string
	 */
	private $type = "system";

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
		$header =  $parser->create('div', '', 'rbHeader');
		$parser->append($wrapper, $header);
		$main =  $parser->create('div', '', 'content');
		$parser->append($wrapper, $main);
		$footer =  $parser->create('div', '', 'rbFooter');
		$parser->append($wrapper, $footer);		
		
		$parser->innerHTML($main, $content);
		
		return $wrapper;
	}	
}
//#section_end#
?>