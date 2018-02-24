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
importer::import("AEL", "Resources", "filesystem/fileManager");
importer::import("API", "Literals", "literal");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Comm", "dbConnection");
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Relations", "customer");

use \API\Literals\literal;
use \API\Model\sql\dbQuery;
use \RTL\Comm\dbConnection;
use \RTL\Profile\company;
use \RTL\Relations\customer;

class invoice
{
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The company relative product id.
	 * 
	 * @type	integer
	 */
	private $invoiceID;
	
	// Constructor Method
	public function __construct($invoiceID = "")
	{
		// Initialize database connection and variables
		$this->dbc = new dbConnection();
		$this->invoiceID = $invoiceID;
	}
	
	public function create($typeID)
	{
		// Check empty
		if (empty($typeID))
			return FALSE;
			
		// Create a new invoice
		$q = new dbQuery("16711162163164", "retail.invoices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['type'] = $typeID;
		$attr['time'] = time();
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			$invoiceInfo = $this->dbc->fetch($result);
			$this->invoiceID = $invoiceInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function setCustomer($customerID)
	{
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
	
	public function close()
	{
		// Close invoice for editing
		$q = new dbQuery("18349791812254", "retail.invoices");
		$attr = array();
		$attr['iid'] = $this->invoiceID;
		$attr['cid'] = company::getCompanyID();
		return $this->dbc->execute($q, $attr);
	}
	
	public function saveAs($filePath)
	{
		// Export file to pdf
		
		// Save file
	}
	
	public static function getInvoiceTypes($compact = FALSE)
	{
		// Initialize db connection and get invoice types
		$dbc = new dbConnection();
		$q = new dbQuery("32869251857426", "retail.invoices");
		$result = $rdbc->execute($q);
		
		// Traverse and get literals
		$types = array();
		$tliterals = literal::get($scope = "enterprise.retail.invoices", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $rdbc->fetch($result))
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
}
//#section_end#
?>