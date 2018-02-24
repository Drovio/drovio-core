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

importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Comm", "dbConnection");
importer::import("RTL", "Invoices", "invoice");
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Products", "cProduct");
importer::import("RTL", "Products", "cProductPrice");

use \API\Model\sql\dbQuery;
use \RTL\Comm\dbConnection;
use \RTL\Invoices\invoice;
use \RTL\Profile\company;
use \RTL\Products\cProduct;
use \RTL\Products\cProductPrice;

/**
 * Invoice Product Manager
 * 
 * Manages products that are part of an invoice.
 * 
 * @version	1.0-4
 * @created	September 19, 2015, 14:34 (EEST)
 * @updated	September 28, 2015, 20:24 (EEST)
 */
class invoiceProduct
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
	 * The product id.
	 * 
	 * @type	string
	 */
	private $productID;
	
	/**
	 * Whether the invoice is valid with the current company and it is not closed.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Create a new instance of the invoice product manager.
	 * 
	 * @param	string	$invoiceID
	 * 		The invoice reference id.
	 * 
	 * @param	string	$productID
	 * 		The product id.
	 * 		Leave empty to add new products.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($invoiceID, $productID = "")
	{
		// Initialize database connection and variables
		$this->dbc = new dbConnection();
		$this->invoiceID = $invoiceID;
		$this->productID = $productID;
	}
	
	/**
	 * Add a new product into the invoice.
	 * If you wish to change the product's info, call update().
	 * 
	 * @param	string	$productID
	 * 		The product id to add.
	 * 
	 * @param	float	$price
	 * 		The product's price.
	 * 
	 * @param	float	$amount
	 * 		The product amount.
	 * 		It is 1 by default.
	 * 
	 * @param	float	$discount
	 * 		The discount percentage.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if the product already exists in the invoice.
	 */
	public function create($productID, $price, $amount = 1, $discount = 0)
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Get product information
		$product = new cProduct($productID);
		$productInfo = $product->info();
		if (empty($productInfo))
			return FALSE;
		
		// Normalize amount and discount
		$this->productID = $productID;
		$amount = ($amount < 1 ? 1 : $amount);
		$discount = (empty($discount) ? 0 : $discount);
		$discount = abs($discount);
		$totalPrice = $price * (1 + $productInfo['rate']) * $amount;
		$totalPrice = $totalPrice * (1 - $discount/100);
		$totalPrice = round($totalPrice, 2, PHP_ROUND_HALF_DOWN);
			
		// Add the product to invoice
		$q = new dbQuery("3220877710146", "retail.invoices.products");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['pid'] = $this->productID;
		$attr['title'] = $productInfo['title'];
		$attr['price'] = $price;
		$attr['rate_id'] = $productInfo['tax_rate_id'];
		$attr['rate'] = $productInfo['rate'];
		$attr['amount'] = $amount;
		$attr['discount'] = $discount;
		$attr['total_price'] = $totalPrice;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Remove a product from an invoice.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
			
		// Check product id
		if (empty($this->productID))
			return FALSE;
			
		// Remove product from invoice
		$q = new dbQuery("17649680225828", "retail.invoices.products");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['pid'] = $this->productID;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Update a product into an invoice (amount, discount, price).
	 * 
	 * @param	float	$price
	 * 		The product's price.
	 * 
	 * @param	float	$amount
	 * 		The product amount.
	 * 		It is 1 by default.
	 * 
	 * @param	float	$discount
	 * 		The discount percentage.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($price, $amount = 1, $discount = 0)
	{
		// Validate company's invoice
		if (!$this->validate($editable = TRUE))
			return FALSE;
		
		// Get product information
		$product = new cProduct($this->productID);
		$productInfo = $product->info();
		if (empty($productInfo))
			return FALSE;
			
		// Normalize amount and discount
		$amount = ($amount < 1 ? 1 : $amount);
		$discount = (empty($discount) ? 0 : $discount);
		$discount = abs($discount);
		$totalPrice = $price * (1 + $productInfo['rate']) * $amount;
		$totalPrice = $totalPrice * (1 - $discount/100);
		$totalPrice = round($totalPrice, 2, PHP_ROUND_HALF_DOWN);

		// Update product
		$q = new dbQuery("20043523445391", "retail.invoices.products");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['pid'] = $this->productID;
		$attr['price'] = $price;
		$attr['rate_id'] = $productInfo['tax_rate_id'];
		$attr['rate'] = $productInfo['rate'];
		$attr['amount'] = $amount;
		$attr['discount'] = $discount;
		$attr['total_price'] = $totalPrice;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get the invoice product info.
	 * 
	 * @return	array
	 * 		Invoice product info including title, amount, price, discount etc.
	 */
	public function info()
	{
		// Validate company's invoice
		if (!$this->validate())
			return FALSE;
			
		// Check product id
		if (empty($this->productID))
			return FALSE;
			
		// Get invoice product info
		$q = new dbQuery("33150322242476", "retail.invoices.products");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['pid'] = $this->productID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result);
	}
	
	/**
	 * Get all invoice products.
	 * 
	 * @return	array
	 * 		An array of all invoice product information.
	 */
	public function getAllProducts()
	{
		// Validate company's invoice
		if (!$this->validate())
			return FALSE;
			
		$q = new dbQuery("26699243626124", "retail.invoices.products");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
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