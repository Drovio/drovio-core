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

importer::import("AEL", "Resources", "appManager");
importer::import("GTL", "Docs", "pdfDoc");

use \AEL\Resources\appManager;
use \GTL\Docs\pdfDoc;

/**
 * PDF Creator
 * 
 * Creates pdf files.
 * 
 * @version	2.0-2
 * @created	September 24, 2015, 22:07 (EEST)
 * @updated	September 29, 2015, 15:22 (EEST)
 */
class pdfCreator extends pdfDoc
{
	/**
	 * Puts an image to the pdf from the team private directory.
	 * 
	 * @param	string	$file
	 * 		The file path inside the apps's team private directory.
	 * 		For more parameter information, see the tFPDF manual.
	 * 
	 * @param	float	$x
	 * 		The x position.
	 * 
	 * @param	float	$y
	 * 		The y position.
	 * 
	 * @param	float	$w
	 * 		The image width.
	 * 
	 * @param	float	$h
	 * 		The image height.
	 * 
	 * @param	string	$type
	 * 		The image type.
	 * 
	 * @param	string	$link
	 * 		The image link.
	 * 
	 * @return	v
	 * 		{description}
	 */
	public function ImageFromTeam($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
	{
		// Get the file path from the team directory
		$rootFolder = appManager::getRootFolder($mode = appManager::TEAM_MODE, $shared = FALSE);
		$imageFilePath = systemRoot.$rootFolder."/".$file;
		return parent::Image($imageFilePath, $x, $y, $w, $h, $type, $link);
	}
	
	/**
	 * Puts an image to the pdf from the team shared directory.
	 * 
	 * @param	string	$file
	 * 		The file path inside the apps's team shared directory.
	 * 		For more parameter information, see the tFPDF manual.
	 * 
	 * @param	float	$x
	 * 		The x position.
	 * 
	 * @param	float	$y
	 * 		The y position.
	 * 
	 * @param	float	$w
	 * 		The image width.
	 * 
	 * @param	float	$h
	 * 		The image height.
	 * 
	 * @param	string	$type
	 * 		The image type.
	 * 
	 * @param	string	$link
	 * 		The image link.
	 * 
	 * @return	void
	 */
	public function ImageFromTeamShared($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
	{
		// Get the file path from the team directory
		$rootFolder = appManager::getRootFolder($mode = appManager::TEAM_MODE, $shared = TRUE);
		$imageFilePath = systemRoot.$rootFolder."/".$file;
		return parent::Image($imageFilePath, $x, $y, $w, $h, $type, $link);
	}
}
//#section_end#
?>