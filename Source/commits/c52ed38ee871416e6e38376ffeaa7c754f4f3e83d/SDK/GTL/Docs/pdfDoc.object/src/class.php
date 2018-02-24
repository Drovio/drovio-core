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
 * @version	0.1-1
 * @created	September 27, 2015, 23:42 (EEST)
 * @updated	September 27, 2015, 23:42 (EEST)
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
	 * Write a line on the pdf file.
	 * 
	 * @param	string	$text
	 * 		The text to write.
	 * 
	 * @param	float	$posX
	 * 		The x position for the upper right corner.
	 * 
	 * @param	float	$posY
	 * 		The y position for the upper right corner.
	 * 
	 * @param	float	$lnH
	 * 		The line height.
	 * 
	 * @param	float	$fontSize
	 * 		The font size.
	 * 		It is 11 by default.
	 * 
	 * @param	string	$fontColorHex
	 * 		The font color in hex mode.
	 * 
	 * @return	void
	 */
	public function WriteLine($text, $posX, $posY, $lnH, $fontSize = 11, $fontColorHex = "FFF")
	{
		// Set font size and color
		$this->SetFontSize($fontSize);
		$this->SetTextColor($r = 0);
		
		// Set position and write
		$this->SetXY($posX, $posY);
		return $this->Write($lnH, $text);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$html
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function WriteHTML($html)
	{
		// HTML parser
		$html = str_replace("\n", ' ',$html);
		$a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach($a as $i => $e)
		{
			if ($i % 2 == 0)
			{
				// Text
				if ($this->HREF)
					$this->PutLink($this->HREF, $e);
				else
					$this->Write(5, $e);
			}
			else
			{
				// Tag
				if ($e[0]=='/')
					$this->CloseTag(strtoupper(substr($e, 1)));
				else
				{
					// Extract attributes
					$a2 = explode(' ', $e);
					$tag = strtoupper(array_shift($a2));
					$attr = array();
					foreach ($a2 as $v)
						if(preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
							$attr[strtoupper($a3[1])] = $a3[2];
							
					$this->OpenTag($tag, $attr);
				}
			}
		}
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$tag
	 * 		{description}
	 * 
	 * @param	{type}	$attr
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function OpenTag($tag, $attr)
	{
		// Opening tag
		if ($tag == 'B' || $tag == 'I' || $tag == 'U')
			$this->SetStyle($tag, TRUE);
		if ($tag == 'A')
			$this->HREF = $attr['HREF'];
		if ($tag == 'BR')
			$this->Ln(5);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$tag
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function CloseTag($tag)
	{
		// Closing tag
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag, FALSE);
		if($tag=='A')
			$this->HREF = '';
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$tag
	 * 		{description}
	 * 
	 * @param	{type}	$enable
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function SetStyle($tag, $enable)
	{
		// Modify style and select corresponding font
		$this->$tag += ($enable ? 1 : -1);
		$style = '';
		foreach (array('B', 'I', 'U') as $s)
			if($this->$s > 0)
				$style .= $s;
				
		$this->SetFont('', $style);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$URL
	 * 		{description}
	 * 
	 * @param	{type}	$txt
	 * 		{description}
	 * 
	 * @param	{type}	$r
	 * 		{description}
	 * 
	 * @param	{type}	$g
	 * 		{description}
	 * 
	 * @param	{type}	$b
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function PutLink($URL, $txt, $r, $g = NULL, $b = NULL)
	{
		// Set color
		$this->SetTextColor($r, $g, $b);
		
		// Set style
		$this->SetStyle('U', TRUE);
		
		// Write text
		$this->Write(5, $txt, $URL);
		
		// Reset style and color
		$this->SetStyle('U', FALSE);
		$this->SetTextColor(0);
	}
}
//#section_end#
?>