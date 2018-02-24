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
importer::import("API", "Model", "sql/dbQuery");
importer::import("ENP", "Comm", "dbConnection");
importer::import("ENP", "Profile", "company");
importer::import("ENP", "Relations", "ePerson");

use \API\Model\sql\dbQuery;
use \ENP\Comm\dbConnection as eDbConnection;
use \ENP\Profile\company;
use \ENP\Relations\ePerson;

class customer extends ePerson
{
	// Constructor Method
	public function __construct($customerID = "")
	{
		// Construct person relation
		parent::__construct($customerID);
	}
	
	public function create($mail_phone, $firstname, $lastname = "")
	{
		// Create person
		$status = parent::create(parent::RELATION_CUSTOMER, $mail_phone, $firstname, $lastname);
		if (!$status)
			return FALSE;
		
		// Add person as customer
		return $this->addCustomer();
	}
	
	public function addCustomer()
	{
		// Get current team id
		$teamID = company::getCompanyID();
		if (empty($teamID))
			return FALSE;
			
		// Add the current person id as customer
		$edbc = new eDbConnection();
		$q = new dbQuery("27062705708512", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		return $edbc->execute($q, $attr);
	}
	
	public function getBalance()
	{
		$customerInfo = $this->getCustomerInfo();
		return $customerInfo['balance'];
	}
	
	public function updateBalance($balance = 0)
	{
		// Get current team id
		$teamID = company::getCompanyID();
		if (empty($teamID))
			return NULL;
			
		// Update customer remainder
		$edbc = new eDbConnection();
		$q = new dbQuery("25490143051449", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		$attr['balance'] = (empty($balance) ? 0 : $balance);
		return $edbc->execute($q, $attr);
	}
	
	private function getCustomerInfo()
	{
		// Get current team id
		$teamID = company::getCompanyID();
		if (empty($teamID))
			return NULL;
			
		// Get customer info
		$edbc = new eDbConnection();
		$q = new dbQuery("27534100085508", "retail.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		$result = $edbc->execute($q, $attr);
		return $edbc->fetch($result);
	}
	
	public function getCustomers()
	{
		return parent::getPersons(parent::RELATION_CUSTOMER);
	}
}
//#section_end#
?>