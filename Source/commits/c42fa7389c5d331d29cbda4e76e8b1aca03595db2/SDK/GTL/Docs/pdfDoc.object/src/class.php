<?php
//#section#[header]
// Namespace
namespace GTL\Docs;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	GTL
 * @package	Docs
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Model", "core/resource");
importer::import("GTL", "Docs", "pdf/tFPDF");

use \API\Model\core\resource;
use \GTL\Docs\pdf\tFPDF;

/**
 * PDF Document Editor
 * 
 * Creates pdf files using the tFPDF library.
 * For more information see:
 * - http://www.fpdf.org/
 * - http://www.fpdf.org/en/script/script92.php
 * 
 * @version	1.0-1
 * @created	September 27, 2015, 23:42 (EEST)
 * @updated	September 29, 2015, 16:25 (EEST)
 */
class pdfDoc extends tFPDF
{
	/**
	 * The bold tag identifier.
	 * 
	 * @type	integer
	 */
	private $B;
	/**
	 * The italic tag identifier.
	 * 
	 * @type	integer
	 */
	private $I;
	/**
	 * The underline tag identifier.
	 * 
	 * @type	integer
	 */
	private $U;
	/**
	 * The href tag identifier.
	 * 
	 * @type	string
	 */
	private $HREF;

	/**
	 * Create a new pdf document instance.
	 * 
	 * @param	string	$orientation
	 * 		The pdf orientation.
	 * 		Select 'p', 'protrait' or 'l', 'landscape'.
	 * 		It is on portrait mode by default.
	 * 
	 * @param	string	$unit
	 * 		The page units.
	 * 		It is 'mm' by default.
	 * 
	 * @param	string	$size
	 * 		The page size.
	 * 		It is A4 by default.
	 * 
	 * @return	void
	 */
	public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
	{
		// Initialization
		$this->B = 0;
		$this->I = 0;
		$this->U = 0;
		$this->HREF = '';
		
		// Constructor
		parent::__construct();
		
		// Set default fonts
		$this->AddFont('DejaVu', $style = '', $file = 'DejaVuSansCondensed.ttf', $uni = TRUE);
		$this->SetFont('DejaVu', '', 12);
	}
	
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
	
	/**
	 * Puts an image to the pdf given the image contents.
	 * 
	 * @param	mixed	$fileContents
	 * 		The image contents.
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
	 * 		Image format.
	 * 		Possible values are (case insensitive): JPG, JPEG, PNG and GIF.
	 * 		If not specified, the type is inferred from the file extension.
	 * 
	 * @param	string	$link
	 * 		URL or identifier returned by AddLink().
	 * 
	 * @return	void
	 */
	public function ImageFromContents($fileContents, $x=null, $y=null, $w=0, $h=0, $type='jpg', $link='')
	{
		// Create temp file
		$imageFilePath = tempnam(sys_get_temp_dir(), 'PDFImage');
		fileManager::create($imageFilePath, $fileContents);
		
		// Add image from temp file
		return parent::Image($imageFilePath, $x, $y, $w, $h, $type, $link);
	}
}
//#section_end#
?>