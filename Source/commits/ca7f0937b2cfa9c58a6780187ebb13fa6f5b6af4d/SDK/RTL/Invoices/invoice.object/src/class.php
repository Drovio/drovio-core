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
importer::import("API", "Profile", "account");
importer::import("RTL", "Comm", "dbConnection");
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Relations", "customer");
importer::import("RTL", "Invoices", "invoiceProduct");
importer::import("RTL", "Invoices", "invoicePayment");

use \API\Literals\literal;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \RTL\Comm\dbConnection;
use \RTL\Profile\company;
use \RTL\Relations\customer;
use \RTL\Invoices\invoiceProduct;
use \RTL\Invoices\invoicePayment;

/**
 * Company retail invoice manager
 * 
 * Manages company invoices.
 * This class can create an invoice pending, add products and payments and then close it to print or save.
 * 
 * @version	4.0-2
 * @created	September 19, 2015, 19:49 (EEST)
 * @updated	October 3, 2015, 16:48 (EEST)
 */
class invoice
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
	 * Whether the invoice is valid with the current company.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Create a new invoice manager instance.
	 * 
	 * @param	string	$invoiceID
	 * 		The invoice id to manage.
	 * 		Leave empty for new invoices.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($invoiceID = "")
	{
		// Initialize database connection and variables
		$this->dbc = new dbConnection();
		$this->invoiceID = $invoiceID;
	}
	
	/**
	 * Create a new pending invoice in the database.
	 * 
	 * @param	integer	$typeID
	 * 		The invoice type id.
	 * 		Use the getInvoiceTypes() function.
	 * 
	 * @param	integer	$typeInvoiceID
	 * 		The invoice incremental id regarding the invoice type.
	 * 		Leave empty for auto increment according to previous value.
	 * 		It is empty by default.
	 * 
	 * @param	string	$date
	 * 		The date that the invoice was supposed to be created.
	 * 		Leave empty for current date and time.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($typeID, $typeInvoiceID = "", $date = "")
	{
		// Check empty
		if (empty($typeID))
			return FALSE;
			
		// Create a new invoice
		$attr = array();
		if (empty($typeInvoiceID))
			$q = new dbQuery("16711162163164", "retail.invoices");
		else
		{
			$q = new dbQuery("30342730885428", "retail.invoices");
			$attr['icode'] = intval($typeInvoiceID);
		}
		
		// Add rest of attributes
		$attr['cid'] = company::getCompanyID();
		$attr['aid'] = account::getAccountID();
		$attr['type'] = $typeID;
		$attr['time'] = time();
		$attr['date'] = (empty($date) ? "NULL" : $date);
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			$invoiceInfo = $this->dbc->fetch($result);
			$this->invoiceID = $invoiceInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Get the current invoice id.
	 * Usually used after the creation process.
	 * 
	 * @return	string
	 * 		The current invoice id.
	 */
	public function getInvoiceID()
	{
		return $this->invoiceID;
	}
	
	/**
	 * Set the invoice customer value.
	 * 
	 * @param	string	$customerID
	 * 		The customer person id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setCustomer($customerID)
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Check if it is a valid customer
		$cust = new customer($customerID);
		$customerInfo = $cust->info();
		if ($customerInfo['owner_company_id'] != company::getCompanyID())
			return FALSE;
		
		// Set invoice customer
		$q = new dbQuery("26557701898604", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		$attr['cust_id'] = $customerID;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set invoice seller info.
	 * 
	 * @param	string	$sellerInfo
	 * 		The invoice seller info.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setSellerInfo($sellerInfo)
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Set invoice seller info (name or anything)
		$q = new dbQuery("16730646102087", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		$attr['sinfo'] = $sellerInfo;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set the invoice notes.
	 * 
	 * @param	string	$notes
	 * 		The invoice notes.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setNotes($notes)
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Set invoice notes
		$q = new dbQuery("29727858449532", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		$attr['notes'] = $notes;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Update invoice extra information about shipping, delivery etc.
	 * 
	 * @param	string	$wayOfPayment
	 * 		Way of payment.
	 * 
	 * @param	string	$purposeOfTrafficking
	 * 		Purpose of trafficking.
	 * 
	 * @param	string	$wayOfShipping
	 * 		Way of shipping.
	 * 
	 * @param	string	$shippingLocation
	 * 		Shipping location.
	 * 
	 * @param	string	$deliveryLocation
	 * 		Delivery location.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateExtraInformation($wayOfPayment = "", $purposeOfTrafficking = "", $wayOfShipping = "", $shippingLocation = "", $deliveryLocation = "")
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Set invoice notes
		$q = new dbQuery("3044957363949", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		$attr['wayOfPayment'] = $wayOfPayment;
		$attr['purposeOfTrafficking'] = $purposeOfTrafficking;
		$attr['wayOfShipping'] = $wayOfShipping;
		$attr['shippingLocation'] = $shippingLocation;
		$attr['deliveryLocation'] = $deliveryLocation;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Add a product to the current invoice.
	 * If the product already exists, the new amount will be added and the discount will be replaced.
	 * 
	 * @param	string	$productID
	 * 		The product id.
	 * 
	 * @param	float	$price
	 * 		The product's price for this invoice.
	 * 
	 * @param	float	$amount
	 * 		The product amount for this invoice.
	 * 		The amount will be added if there the product already exists.
	 * 
	 * @param	float	$discount
	 * 		The product discount, if any.
	 * 		This is value and not percentage over the total price (per 1 amount) after taxes.
	 * 		It is 0 by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function addProduct($productID, $price, $amount = 1, $discount = 0)
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Add an invoice product
		$iProduct = new invoiceProduct($this->invoiceID);
		$status = $iProduct->create($productID, $price, $amount, $discount);
		
		// If the product already exists, update amount and discounts
		if (!$status)
		{
			// Get current amount
			$productInfo = $iProduct->info();
			$newAmount = $productInfo['amount'] + $amount;
			return $iProduct->update($price, $newAmount, $discount);
		}
		
		// Return status
		return $status;
	}
	
	/**
	 * Add an invoice payment.
	 * 
	 * @param	integer	$paymentType
	 * 		The payment type id.
	 * 
	 * @param	float	$payment
	 * 		The payment value.
	 * 
	 * @param	string	$notes
	 * 		Payment notes (e.x. card bank owner etc.).
	 * 
	 * @param	string	$referenceID
	 * 		The payment reference id.
	 * 		This can be used to reference other invoices for proof of payment.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function addPayment($paymentType, $payment, $notes = "", $referenceID = "")
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Add an invoice payment
		$iPayment = new invoicePayment($this->invoiceID);
		return $iPayment->create($paymentType, $payment, $notes, $referenceID);
	}
	
	/**
	 * Mark the current invoice as completed and it cannot be further edited.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function close()
	{
		// Validate company's invoice, should be open
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Close invoice for editing
		$q = new dbQuery("18349791812254", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		$status = $this->dbc->execute($q, $attr);
		if (!$status)
			return FALSE;
		
		// Get current invoice info
		$invoiceInfo = $this->info();
		
		// Get invoice type flow
		$invTypes = $this->getInvoiceTypes();
		$flow = $invTypes[$invoiceInfo['type_id']]['transaction_flow'];
		if (empty($flow))
			return $status;
		
		// Set customer balance
		$customerID = $invoiceInfo['customer_id'];
		$cust = new customer($customerID);
		$newBalance = $cust->getBalance() + ($flow * $this->getBalance());
		$cust->updateBalance($newBalance);
		
		// Update each product stock
		
		return $status;
	}
	
	/**
	 * Remove the invoice from the system.
	 * The invoice must be editable.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Close invoice for editing
		$q = new dbQuery("2838833109349", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all invoice information.
	 * 
	 * @param	boolean	$includeProducts
	 * 		Set whether to include the product list into the information.
	 * 		It will be under the 'products' field.
	 * 		It is FALSE by default.
	 * 
	 * @param	boolean	$includePayments
	 * 		Set whether to include the payments into the information.
	 * 		It will be under the 'payments' field.
	 * 		It is FALSE by default.
	 * 
	 * @return	array
	 * 		An array of all product information.
	 */
	public function info($includeProducts = FALSE, $includePayments = FALSE)
	{
		// Validate company's invoice
		if (!$this->validate())
			return FALSE;
			
		// Get invoice info
		$q = new dbQuery("34401623353128", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		$result = $this->dbc->execute($q, $attr);
		$invoiceInfo = $this->dbc->fetch($result);
		
		// Include products
		if ($includeProducts)
		{
			$invp = new invoiceProduct($this->invoiceID);
			$invoiceInfo['products'] = $invp->getAllProducts();
		}
		
		// Include payments
		if ($includePayments)
		{
			$invp = new invoicePayment($this->invoiceID);
			$invoiceInfo['payments'] = $invp->getAllPayments();
		}
		
		// Add invoice type
		$types = $this->getInvoiceTypes($compact = TRUE);
		$invoiceInfo['type'] = $types[$invoiceInfo['type_id']];
		
		return $invoiceInfo;
	}
	
	/**
	 * Get invoice total product price (including taxes).
	 * 
	 * @return	float
	 * 		The invoice total price (including taxes).
	 */
	public function getTotalPrice()
	{
		// Initialize zero balance
		$balance = 0;
		
		// Get all invoice products
		$invp = new invoiceProduct($this->invoiceID);
		$inv_products = $invp->getAllProducts();
		foreach ($inv_products as $productInfo)
			$balance += $productInfo['total_price'];
		
		return $balance;
	}
	
	/**
	 * Get invoice total payments value.
	 * 
	 * @return	float
	 * 		The total payments.
	 */
	public function getTotalPayments()
	{
		// Initialize zero payment
		$payment = 0;
		
		// Get all invoice payments
		$invp = new invoicePayment($this->invoiceID);
		$inv_payments = $invp->getAllPayments();
		foreach ($inv_payments as $payementInfo)
			$payment += $payementInfo['payment'];
		
		return $payment;
	}
	
	/**
	 * Get the current invoice balance (total price - payments).
	 * 
	 * @return	float
	 * 		The current invoice balance.
	 */
	public function getBalance()
	{
		return $this->getTotalPrice() - $this->getTotalPayments();
	}
	
	/**
	 * Get all invoice types.
	 * 
	 * @param	boolean	$compact
	 * 		Whether to return a compact array of only ids and titles or full type information.
	 * 
	 * @return	array
	 * 		An array of all invoice types by id and title.
	 */
	public static function getInvoiceTypes($compact = FALSE)
	{
		// Initialize db connection and get invoice types
		$dbc = new dbConnection();
		$q = new dbQuery("32869251857426", "retail.invoices");
		$result = $dbc->execute($q);
		
		// Traverse and get literals
		$types = array();
		$tliterals = literal::get($scope = "retail.invoices", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $dbc->fetch($result))
		{
			// Get invoice type literal title
			$typeTitle = $tliterals["type_".$row['title']];
			
			// Get compact or not
			if ($compact)
				$types[$row['id']] = $typeTitle;
			else
			{
				$row['title'] = $typeTitle;
				$types[$row['id']] = $row;
			}
		}
		
		return $types;
	}
	
	/**
	 * Get all completed invoices for the given period of time.
	 * 
	 * @param	integer	$fromTime
	 * 		The from time timestamp.
	 * 		Use unix timestamp format.
	 * 		It is 0 by default.
	 * 
	 * @param	integer	$toTime
	 * 		The to time timestamp.
	 * 		Use unix timestamp format.
	 * 		It is empty by default, which means up until now.
	 * 
	 * @param	integer	$accountID
	 * 		The creator/owner account id for the invoices.
	 * 		Leave empty to skip this filter.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all invoices.
	 */
	public static function getAllInvoices($fromTime = "0", $toTime = "", $accountID = "")
	{
		// Set from and to time
		$fromTime = (empty($fromTime) ? 0 : $fromTime);
		$toTime = (empty($toTime) ? time() : $toTime);
		
		// Initialize db connection and get invoice types
		$dbc = new dbConnection();
		$q = new dbQuery("16471472824431", "retail.invoices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['ftime'] = $fromTime;
		$attr['ttime'] = $toTime;
		$attr['aid'] = (empty($accountID) ? "NULL" : $accountID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all pending (not completed) invoices for the given period of time.
	 * 
	 * @param	integer	$fromTime
	 * 		The from time timestamp.
	 * 		Use unix timestamp format.
	 * 		It is 0 by default.
	 * 
	 * @param	integer	$toTime
	 * 		The to time timestamp.
	 * 		Use unix timestamp format.
	 * 		It is empty by default, which means up until now.
	 * 
	 * @param	integer	$accountID
	 * 		The creator/owner account id for the invoices.
	 * 		Leave empty to skip this filter.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all invoices.
	 */
	public static function getAllPendingInvoices($fromTime = "0", $toTime = "", $accountID = "")
	{
		// Set from and to time
		$fromTime = (empty($fromTime) ? 0 : $fromTime);
		$toTime = (empty($toTime) ? time() : $toTime);
		
		// Initialize db connection and get invoice types
		$dbc = new dbConnection();
		$q = new dbQuery("30832773381233", "retail.invoices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['ftime'] = $fromTime;
		$attr['ttime'] = $toTime;
		$attr['aid'] = (empty($accountID) ? "NULL" : $accountID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Validate whether the current invoice is owner by the current company.
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
		$q = new dbQuery("34401623353128", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		$result = $this->dbc->execute($q, $attr);
		$invoiceInfo = $this->dbc->fetch($result);
		
		// Check if it is the same team/company
		$this->valid = ($invoiceInfo['owner_company_id'] == $companyID);
		$this->valid = ($editable ? $this->valid && !$invoiceInfo['completed'] : $this->valid);
		return $this->valid;
	}
}
//#section_end#
?>