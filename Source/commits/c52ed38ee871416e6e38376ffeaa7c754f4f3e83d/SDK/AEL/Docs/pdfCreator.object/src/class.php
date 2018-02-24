<?php
//#section#[header]
// Namespace
namespace AEL\Docs;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Docs
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("AEL", "Resources", "filesystem/fileManager");
importer::import("API", "Model", "core/resource");
importer::import("GTL", "Docs", "pdf/tFPDF");

use \AEL\Platform\application;
use \AEL\Resources\filesystem\fileManager;
use \API\Model\core\resource;
use \GTL\Docs\pdf\tFPDF;

/**
 * PDF Creator
 * 
 * Creates pdf files.
 * 
 * @version	1.0-2
 * @created	September 24, 2015, 22:07 (EEST)
 * @updated	September 27, 2015, 23:34 (EEST)
 * 
 * @deprecated	Use \GTL\Docs\pdfDoc instead.
 */
class pdfCreator extends tFPDF
{
	/**
	 * Load a json file from the font repository.
	 * You can extend this function to use application resources.
	 * 
	 * @param	string	$fontName
	 * 		The font name to load.
	 * 
	 * @return	array
	 * 		The font array as defined from FPDF.
	 */
	public function _loadfont($fontName)
	{
		$jsonFont = resource::get("/resources/fonts/".$fontName.".json");
		return json_decode($jsonFont, TRUE);
	}
	
	/**
	 * Get the pdf contents.
	 * 
	 * @return	mixed
	 * 		The pdf file.
	 */
	public function Output()
	{
		// Call parent
		return parent::Output($name = "", $dest = "S");
	}
}
//#section_end#
?>