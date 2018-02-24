<?php
//#section#[header]
// Namespace
namespace RTL\Invoices;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	RTL
 * @package	Invoices
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("AEL", "Docs", "pdfCreator");
importer::import("AEL", "Resources", "filesystem/fileManager");

use \AEL\Docs\pdfCreator;
use \AEL\Resources\filesystem\fileManager;

/**
 * Invoice Print Engine
 * 
 * Manages to print an invoice to a document.
 * It supports only pdf for the moment.
 * 
 * @version	0.1-1
 * @created	September 24, 2015, 22:33 (EEST)
 * @updated	September 24, 2015, 22:33 (EEST)
 */
class invoicePrint
{
	/**
	 * The invoice id.
	 * 
	 * @type	string
	 */
	private $invoiceID;
	
	/**
	 * Create an invoice print instance.
	 * 
	 * @param	string	$invoiceID
	 * 		The invoice id.
	 * 
	 * @return	void
	 */
	public function __construct($invoiceID = "")
	{
		// Initialize invoice id
		$this->invoiceID = $invoiceID;
	}
	
	/**
	 * Export the pdf to the given filepath.
	 * 
	 * @param	string	$filePath
	 * 		The team file path to save the pdf file.
	 * 
	 * @param	boolean	$shared
	 * 		Whether to use the shared folder or not.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function exportPDF($filePath, $shared = FALSE)
	{
		// Create pdf file
		$pdfParser = new pdfCreator();
		$fileContents = $this->createInvoicePDF($pdfParser);
		
		// Save to file
		$fm = new fileManager($mode = fileManager::TEAM_MODE, $shared);
		return $fm->create($filePath, $fileContents);
	}
	
	/**
	 * Create the invoice pdf using the default schema.
	 * 
	 * @param	pdfCreator	$pdf
	 * 		The pdf creator object.
	 * 
	 * @return	mixed
	 * 		The pdf file.
	 */
	private function createInvoicePDF($pdf)
	{
		// Add page
		$pdf->AddPage();
		
		// Set font
		$pdf->AddFont('DejaVu', $style = '', $file = 'DejaVuSans.ttf', $uni = TRUE);
		$pdf->SetFont('DejaVu', '', 9);
		
		// Add a random cell
		$str = "Γεια σου κόσμε και κοσμάκι!";
		$pdf->Cell(40, 10, $str, 1);
		
		// Return output
		return $pdf->Output();
	}
}
//#section_end#
?>