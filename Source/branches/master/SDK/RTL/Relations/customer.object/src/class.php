<?php
//#section#[header]
// Namespace
namespace RTL\Relations;

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
 * @package	Relations
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Comm", "dbConnection");
importer::import("RTL", "Profile", "company");
importer::import("ENP", "Relations", "ePerson");

use \API\Model\sql\dbQuery;
use \RTL\Comm\dbConnection;
use \RTL\Profile\company;
use \ENP\Relations\ePerson;

/**
 * Company customer manaager.
 * 
 * Manages company customer information.
 * 
 * @version	4.0-1
 * @created	September 1, 2015, 11:21 (EEST)
 * @updated	October 3, 2015, 21:21 (EEST)
 */
class customer extends ePerson
{
	/**
	 * Create a new customer instance.
	 * 
	 * @param	integer	$personID
	 * 		The person/customer id.
	 * 		Leave empty for creating new customer.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($personID = "")
	{
		// Construct person relation
		parent::__construct($personID);
	}
	
	/**
	 * Create a new customer.
	 * Since the class extends ePerson, a new ePerson will be created and a connection will be made.
	 * 
	 * @param	string	$firstname
	 * 		The person's first name.
	 * 
	 * @param	string	$lastname
	 * 		The person's last name.
	 * 
	 * @param	string	$middlename
	 * 		The person's middle name.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($firstname, $lastname, $middlename = "")
	{
		// Create person
		$status = parent::create($firstname, $lastname, $middlename);
		if (!$status)
			return FALSE;
		
		// Add person as customer
		$status = $this->addCustomer();
		$this->update($firstname, $lastname, $middlename);
		return $status;
	}
	
	/**
	 * Add an existing person as customer.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function addCustomer()
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
			
		// Add the current person id as customer
		$dbc = new dbConnection();
		$q = new dbQuery("27062705708512", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['cid'] = $companyID;
		$status = $dbc->execute($q, $attr);
		if (!$status)
			return FALSE;
		
		// Update basic information
		$personInfo = parent::info();
		return $this->update($personInfo['firstname'], $personInfo['lastname'], $personInfo['middle_name']);
	}
	
	/**
	 * Update customer info.
	 * 
	 * @param	string	$firstname
	 * 		The customer's first name.
	 * 
	 * @param	string	$lastname
	 * 		The customer's last name.
	 * 
	 * @param	string	$middlename
	 * 		The customer's middle name.
	 * 		It is empty by default.
	 * 
	 * @param	string	$occupation
	 * 		The customer occupation.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($firstname, $lastname, $middlename = "", $occupation = "")
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
			
		// Add the current person id as customer
		$dbc = new dbConnection();
		$q = new dbQuery("31951388647708", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['cid'] = $companyID;
		$attr['firstname'] = $firstname;
		$attr['lastname'] = $lastname;
		$attr['middlename'] = $middlename;
		$attr['occupation'] = $occupation;
		$status = $dbc->execute($q, $attr);
		if ($status)
			parent::update($firstname, $lastname, $middlename);
		
		return $status;
	}
	
	/**
	 * Update customer's company information.
	 * In case that a customer represents a company.
	 * 
	 * @param	boolean	$isCompany
	 * 		Whether the current customer is company or not.
	 * 		It is FALSE by default.
	 * 
	 * @param	string	$companyName
	 * 		The company name.
	 * 
	 * @param	string	$taxID
	 * 		The company's tax identification number.
	 * 
	 * @param	string	$irs
	 * 		The company's responsible irs agency.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCompanyInfo($isCompany = FALSE, $companyName = "", $taxID = "", $irs = "")
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
			
		// Update customer company information
		$dbc = new dbConnection();
		$q = new dbQuery("31170535129593", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['cid'] = $companyID;
		$attr['is_company'] = $isCompany;
		$attr['cname'] = $companyName;
		$attr['taxid'] = $taxID;
		$attr['irs'] = $irs;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all information for the current customer.
	 * 
	 * @return	array
	 * 		The customer's stored information.
	 * 		It will merge customer information with the connected person information.
	 */
	public function info()
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
			
		// Get customer info
		$customerInfo = $this->getCustomerInfo();
		$customerInfo = (empty($customerInfo) ? array() : $customerInfo);
		
		// Get person info
		$personInfo = parent::info();
		$personInfo = (empty($personInfo) ? array() : $personInfo);
		
		// Merge information into one common array
		return array_merge($customerInfo, $personInfo);
	}
	
	/**
	 * Remove the customer from the customer list.
	 * The customer must not have any invoices or other connections to the retail database.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
			
		// Add the current person id as customer
		$dbc = new dbConnection();
		$q = new dbQuery("32434480104025", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['cid'] = $companyID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get customer balance.
	 * 
	 * @return	float
	 * 		The balance value.
	 */
	public function getBalance()
	{
		$customerInfo = $this->getCustomerInfo();
		return $customerInfo['balance'];
	}
	
	/**
	 * Update the customer's balance.
	 * 
	 * @param	float	$balance
	 * 		The new customer balance.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateBalance($balance = 0)
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return NULL;
			
		// Update customer remainder
		$dbc = new dbConnection();
		$q = new dbQuery("25490143051449", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['cid'] = $companyID;
		$attr['balance'] = (empty($balance) ? 0 : $balance);
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get customer info.
	 * This is used for internal purposes like getting specific information.
	 * 
	 * @return	array
	 * 		An array of all customer information.
	 */
	public function getCustomerInfo()
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return NULL;
			
		// Get customer info
		$dbc = new dbConnection();
		$q = new dbQuery("27534100085508", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['cid'] = $companyID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Get all customer's completed invoices.
	 * 
	 * @return	array
	 * 		An array of all invoices by invoice id and invoice information.
	 */
	public function getCustomerInvoices()
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return NULL;
			
		// Get all customer invoices
		$dbc = new dbConnection();
		$q = new dbQuery("23684686360911", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['cid'] = $companyID;
		$result = $dbc->execute($q, $attr);

		$invoices = array();
		while ($row = $dbc->fetch($result))
			$invoices[$row['id']] = $row;
		
		return $invoices;
	}
	
	/**
	 * Get all company customers.
	 * 
	 * @return	array
	 * 		An array of all customer info.
	 * 		It includes a 'person' field with all the person information
	 */
	public static function getCustomers()
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return NULL;
			
		// Get all customers
		$dbc = new dbConnection();
		$q = new dbQuery("27103555280059", "retail.relations");
		$attr = array();
		$attr['cid'] = $companyID;
		$result = $dbc->execute($q, $attr);
		
		// Fetch all customers and add person info
		$allCustomers = array();
		while ($custInfo = $dbc->fetch($result))
		{
			// Get person information from relations
			$person = new ePerson($custInfo['person_id']);
			$personInfo = $person->info();
			if (!empty($personInfo))
				$custInfo['person'] = $personInfo;
			
			// Add to customer list
			$allCustomers[] = $custInfo;
		}
		
		return $allCustomers;
	}
}
//#section_end#
?>