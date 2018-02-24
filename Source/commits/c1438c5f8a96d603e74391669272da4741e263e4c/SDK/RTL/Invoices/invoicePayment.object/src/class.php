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

importer::import("API", "Literals", "literal");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Comm", "dbConnection");
importer::import("RTL", "Profile", "company");

use \API\Literals\literal;
use \API\Model\sql\dbQuery;
use \RTL\Comm\dbConnection;
use \RTL\Profile\company;

/**
 * Invoice Payment Manager
 * 
 * Manages invoice payments.
 * 
 * @version	0.2-2
 * @created	September 19, 2015, 15:25 (EEST)
 * @updated	September 23, 2015, 20:14 (EEST)
 */
class invoicePayment
{
	/**
	 * The retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The invoice id.
	 * 
	 * @type	string
	 */
	private $invoiceID;
	
	/**
	 * The payment id.
	 * 
	 * @type	string
	 */
	private $paymentID;
	
	/**
	 * Whether the invoice is valid with the current company and it is not closed.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Create a new instance of the payment manager.
	 * 
	 * @param	string	$invoiceID
	 * 		The invoice reference id.
	 * 
	 * @param	string	$paymentID
	 * 		The payment id.
	 * 		Leave empty for new payment.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($invoiceID, $paymentID = "")
	{
		// Initialize database connection and variables
		$this->dbc = new dbConnection();
		$this->invoiceID = $invoiceID;
		$this->paymentID = $paymentID;
	}
	
	/**
	 * Add a new invoice payment.
	 * 
	 * @param	integer	$paymentType
	 * 		The payment type id.
	 * 		Use getAllPaymentTypes() to get the types.
	 * 
	 * @param	float	$payment
	 * 		The payment value.
	 * 
	 * @param	string	$notes
	 * 		Any notes about the payment.
	 * 
	 * @param	string	$referenceID
	 * 		A reference id for this payment.
	 * 		This could be another invoice to be used as credit or anything.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function add($paymentType, $payment, $notes = "", $referenceID = "")
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Add an invoice payment
		$q = new dbQuery("28348777353962", "retail.invoices.payments");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['type'] = $paymentType;
		$attr['payment'] = $payment;
		$attr['notes'] = $notes;
		$attr['ref'] = (empty($referenceID) ? "NULL" : $referenceID);
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			$info = $this->dbc->fetch($result);
			$this->paymentID = $info['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update a payment value.
	 * 
	 * @param	float	$payment
	 * 		The payment value.
	 * 
	 * @param	string	$notes
	 * 		Any notes about the payment.
	 * 
	 * @param	string	$referenceID
	 * 		A reference id for this payment.
	 * 		This could be another invoice to be used as credit or anything.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($payment, $notes = "", $referenceID = "")
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Check payment limits
		if ($payment <= 0)
			return FALSE;
			
		// Update the invoice payment
		$q = new dbQuery("20042355255155", "retail.invoices.payments");
		$attr = array();
		$attr['id'] = $this->paymentID;
		$attr['iid'] = $this->invoiceID;
		$attr['payment'] = $payment;
		$attr['notes'] = $notes;
		$attr['ref'] = (empty($referenceID) ? "NULL" : $referenceID);
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Remove a payment from the invoice.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Check payment id
		if (empty($this->paymentID))
			return FALSE;
			
		// Remove payment from invoice
		$q = new dbQuery("34147002435993", "retail.invoices.payments");
		$attr = array();
		$attr['id'] = $this->paymentID;
		$attr['iid'] = $this->invoiceID;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all invoice payments.
	 * 
	 * @return	array
	 * 		An array of all current invoice payments.
	 */
	public function getAllPayments()
	{
		// Validate company's invoice
		if (!$this->validate())
			return FALSE;
		
		// Get all payments
		$q = new dbQuery("27532075211883", "retail.invoices.payments");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$result = $this->dbc->execute($q, $attr);
		$invoicePayments = $this->dbc->fetch($result, TRUE);
		
		// Set payment type titles
		$paymentTypes = $this->getAllPaymentTypes();
		foreach ($invoicePayments as $id => $paymentInfo)
			$invoicePayments[$id]['type'] = $paymentTypes[$paymentInfo['payment_type_id']];
		
		return $invoicePayments;
	}
	
	/**
	 * Get all invoice payment types.
	 * 
	 * @return	array
	 * 		An array of all invoice payment types by id and title (in current locale).
	 */
	public static function getAllPaymentTypes()
	{
		// Get all types from database
		$dbc = new dbConnection();
		$q = new dbQuery("19278302293593", "retail.invoices.payments");
		$result = $dbc->execute($q);
		
		// Iterate and get literals
		$types = array();
		$tliterals = literal::get($scope = "retail.invoices.payments", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $dbc->fetch($result))
			$types[$row['id']] = $tliterals["type_".$row['title']];
		
		return $types;
	}
	
	/**
	 * Validate whether the current invoice is owned by the current team.
	 * 
	 * @param	boolean	$editable
	 * 		Extra check whether the invoice is editable (not completed) or not.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	private function validate($editable = FALSE)
	{
		// Check class cache
		if ($this->valid)
			return $this->valid;
			
		// Get current team/company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
		
		// Get invoice's company owner id
		$inv = new invoice($this->invoiceID);
		$invoiceInfo = $inv->info();
		
		// Check if it is the same team/company
		$this->valid = ($invoiceInfo['owner_company_id'] == $companyID);
		$this->valid = ($editable ? $this->valid && !$invoiceInfo['completed'] : $this->valid);
		return $this->valid;
	}
}
//#section_end#
?>