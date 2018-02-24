<?php
//#section#[header]
// Namespace
namespace DEV\Tools;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Tools
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Comm", "database::connections::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "person");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Comm\database\connections\interDbConnection;
use \API\Comm\database\connections\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Profile\person;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * Database Synchronization Class
 * 
 * Synchronizes development and production databases.
 * 
 * @version	{empty}
 * @created	April 29, 2014, 15:55 (EEST)
 * @revised	April 29, 2014, 22:18 (EEST)
 */
class dbSync
{
	/**
	 * The database tables in categories.
	 * 
	 * @type	array
	 */
	private static $categories = array(
		"geoloc" => array(
			"method" => "upload",
			"tables" => array(
				"update:GLC_region",
				"update:GLC_country",
				"update:GLC_town",
				"update:GLC_timeZone",
				"ignore:GLC_timeZoneCountry",
				"update:GLC_language",
				"update:GLC_locale",
				"update:GLC_currency",
				"ignore:GLC_countryCurrency"
			)
		),
		"modules" => array(
			"method" => "upload",
			"tables" => array(
				"update:UNIT_moduleStatus",
				"update:UNIT_moduleScope",
				"update:UNIT_moduleGroup",
				"update:UNIT_module"
			)
		),
		"pages" => array(
			"method" => "upload",
			"tables" => array(
				"update:UNIT_domain",
				"update:UNIT_pageFolder",
				"update:UNIT_page"
			)
		),
		"literals" => array(
			"method" => "upload",
			"tables" => array(
				"update:TR_literalScope",
				"update:TR_literal"
			)
		),
		"security" => array(
			"method" => "upload",
			"tables" => array(
				"update:PLM_userGroup",
				"ignore:PLM_userGroupCommand",
				"ignore:PLM_accountAtGroup",
				"update:PLM_userGroup",
				"ignore:PLM_userGroupCommand",
				"ignore:PLM_accountAtGroup"
			)
		),
		"projects" => array(
			"method" => "upload",
			"tables" => array(
				"update:DEV_projectType",
				"update:DEV_projectStatus",
				"update:DEV_projectCategory"
			)
		),
		"persons" => array(
			"method" => "download",
			"tables" => array(
				"update:RB_person",
				"update:RB_company",
				"update:PLM_account",
				"ignore:PLM_accountAtGroup",
				"ignore:PLM_personToAccount"
			)
		)
	);
	/**
	 * Checks the two stored schemas for differences.
	 * For now, it checks only the table columns without the keys.
	 * 
	 * @return	array
	 * 		An array of "upload" and "delete" fields for the production server.
	 * 		An empty array if the databases are identical.
	 */
	public static function checkSchemas()
	{
		// load schemas
		$localSchema = self::loadXMLSchema("local.testing");
		$pubSchema = self::loadXMLSchema("db28.grserver.gr");
		
		$result = array();
		$result['upload'] = self::diff($localSchema, $pubSchema);
		$result['delete'] = self::diff($pubSchema, $localSchema);
		
		return $result;
	}
	
	/**
	 * Returns the array difference between two specific arrays containing table information.
	 * 
	 * @param	array	$schema1
	 * 		The first db schema.
	 * 
	 * @param	array	$schema2
	 * 		The second db schema.
	 * 
	 * @return	array
	 * 		An array of all differences.
	 */
	private static function diff($schema1, $schema2)
	{
		$diff = array();
		foreach ($schema1 as $tableName => $columns)
		{
			if (!isset($schema2[$tableName]))
				$diff[$tableName] = "FULL TABLE";
			else
			{
				foreach ($columns as $key => $column)
				{
					$diff_assoc = array_diff_assoc($column, $schema2[$tableName][$key]);
					if (!empty($diff_assoc))
						$diff[$tableName][] = $diff_assoc;
					else if (empty($schema2[$tableName][$key]))
						$diff[$tableName][] = $column;
				}
			}
		}
		
		return $diff;
	}
	
	/**
	 * Loads the db schema from the xml file.
	 * 
	 * @param	string	$name
	 * 		The schema file name.
	 * 
	 * @return	array
	 * 		The db schema.
	 */
	private static function loadXMLSchema($name)
	{
		$parser = new DOMParser();
		try
		{
			$parser->load("/System/Resources/Analytics/database/".$name.".schema.xml");
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		$schema = array();
		$tables = $parser->evaluate("//table");
		foreach ($tables as $table)
		{
			$tableInfo = array();
			$tableName = $parser->attr($table, "name");
			
			$columns = $parser->evaluate("column", $table);
			foreach ($columns as $column)
			{
				$columnInfo = array();
				$attrs = $parser->evaluate("attr", $column);
				foreach ($attrs as $attr)
				{
					$attrName = $parser->attr($attr, "name");
					$attrValue = $parser->attr($attr, "value");
					$columnInfo[$attrName] = $attrValue;
				}
				
				$tableInfo[] = $columnInfo;
			}
			
			$schema[$tableName] = $tableInfo;
		}
		
		return $schema;
	}
	
	/**
	 * Loads and parses the schemas of the development and the production databases.
	 * 
	 * @return	void
	 */
	public static function loadSchemas()
	{
		// Get developer's dbSchema
		$dbc = new interDbConnection();
		$q = new dbQuery("21316677244563", "schema.database");
		
		$result = $dbc->execute($q);
		$dbTables = $dbc->fetch($result, TRUE);
		
		$devTables = array();
		foreach ($dbTables as $tables)
			foreach ($tables as $table)
				$devTables[] = $table;
		
		
		// Get columns for each table
		$q = new dbQuery("14631754301299", "schema.database");
		
		$devColumns = array();
		foreach ($devTables as $dbTable)
		{
			$attr = array();
			$attr['table'] = $dbTable;
			$result = $dbc->execute($q, $attr);
			while ($row = $dbc->fetch($result))
				$devColumns[$dbTable][] = $row;
		}
		
		// Write schema to file
		self::generateXMLSchema($devColumns, "local.testing");
		
		
		
		// Load Published schema
		$dbc = new dbConnection();
		$dbc->options("MySQL", "db28.grserver.gr", "redback", "rbdbman", "qAoc~935");
		$q = new dbQuery("21316677244563", "schema.database");
		
		
		$query = $q->getQuery();
		$result = $dbc->execute($query);
		$dbTables = $dbc->fetch($result, TRUE);
		
		$pubTables = array();
		foreach ($dbTables as $tables)
			foreach ($tables as $table)
				$pubTables[] = $table;
				
		// Get columns for each table
		$q = new dbQuery("14631754301299", "schema.database");
		
		$pubColumns = array();
		foreach ($pubTables as $dbTable)
		{
			$attr = array();
			$attr['table'] = $dbTable;
			$query = $q->getQuery($attr);
			$result = $dbc->execute($query);
			while ($row = $dbc->fetch($result))
				$pubColumns[$dbTable][] = $row;
		}
		
		// Write schema to file
		self::generateXMLSchema($pubColumns, "db28.grserver.gr");
	}
	
	/**
	 * Generates the xml file schema given the table data.
	 * 
	 * @param	array	$schema
	 * 		An array containing all the tables and their columns.
	 * 
	 * @param	string	$name
	 * 		The schema filename.
	 * 
	 * @return	void
	 */
	private static function generateXMLSchema($schema, $name)
	{
		$parser = new DOMParser();
		$root = $parser->create("schema");
		$parser->append($root);
		$tables = $parser->create("tables");
		$parser->append($root, $tables);
		foreach ($schema as $dbTable => $data)
		{
			$tableNode = $parser->create("table");
			$parser->attr($tableNode, "name", $dbTable);
			$parser->append($tables, $tableNode);
			
			// columns
			foreach ($data as $columns)
			{
				$column = $parser->create("column");
				$parser->append($tableNode, $column);
				foreach ($columns as $key => $value)
				{
					$attr = $parser->create("attr");
					$parser->append($column, $attr);
					$parser->attr($attr, "name", $key);
					$parser->attr($attr, "value", $value);
				}
			}
		}
		
		$output = $parser->getXML();
		fileManager::create(systemRoot."/System/Resources/Analytics/database/".$name.".schema.xml", $output, TRUE);
	}
	
	/**
	 * Syncs the development and the production database information.
	 * 
	 * @param	array	$tableCategories
	 * 		The table categories to be synced.
	 * 
	 * @return	mixed
	 * 		True if all is ok or a description with the errors occurred.
	 */
	public static function sync($tableCategories = array())
	{
		// Check schemas first
		$result = self::checkSchemas();
		if (!empty($result['upload']) || !empty($result['delete']))
			return FALSE;
		
		// Initialize error
		$error = "";
		
		// Load schemas to get primary keys
		$devSchema = self::loadXMLSchema("local.testing");
		$prodSchema = self::loadXMLSchema("db28.grserver.gr");
		foreach ($tableCategories as $category => $data)
		{
			$method = self::$categories[$category]['method'];
			$tables = self::$categories[$category]['tables'];
			foreach ($tables as $table)
			{
				// Get parts
				$parts = explode(":", $table);
				$meth = $parts[0];
				$tableName = $parts[1];
				
				// Get primary keys
				$keys = array();
				$schema = ($method == "upload" ? $devSchema : $prodSchema);
				foreach ($schema[$tableName] as $attributes)
					if ($attirubtes['Key'] == "PRI")
						$keys[] = $attributes['Field'];
				
				// Sync
				if ($method == "upload")
					$status = self::upload($tableName, $meth, $keys);
				else
					$status = self::download($tableName, $meth, $keys);
				
				if (!is_bool($status))
					$error .= $status;
			}
		}
		
		if (!empty($error))
			return $error;
		
		return TRUE;
	}
	
	/**
	 * Upload information to production database.
	 * 
	 * @param	string	$table
	 * 		The table name to be uploaded.
	 * 
	 * @param	string	$method
	 * 		The upload method.
	 * 		Use 'update' or 'ignore' to react on insert statement.
	 * 
	 * @param	array	$pkeys
	 * 		The table's primary keys.
	 * 
	 * @return	mixed
	 * 		True on success, the error description otherwise.
	 */
	private static function upload($table, $method, $pkeys)
	{
		// Initialize error
		$error = "";
		
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get table columns
		$q = new dbQuery("14631754301299", "schema.database");
		$attr = array();
		$attr['table'] = $table;
		$result = $dbc->execute($q, $attr);
		$columns = $dbc->toArray($result, "Field", "Field");
		$types = $dbc->toArray($result, "Field", "Type");
		$defaults = $dbc->toArray($result, "Field", "Default");
		$nulls = $dbc->toArray($result, "Field", "Null");
		
		// Get data
		$q = new dbQuery("28116045007199", "schema.generic");
		$attr = array();
		$attr['table'] = $table;
		$result = $dbc->execute($q, $attr);
		$tableData = $dbc->fetch($result, TRUE);
		
		// Create one query for upload
		$query = self::createQuery($method, $table, $tableData, $columns, $types, $defaults, $nulls, $pkeys);
		
		// If there are no data, return TRUE
		if (empty($query))
			return TRUE;
		
		// Upload data
		$dbc = new dbConnection();
		$dbc->options("MySQL", "db28.grserver.gr", "redback", "rbdbman", "qAoc~935");
		$result = $dbc->execute($query);
		if (!$result)
		{
			$err = $dbc->getError();
			return "Error sending data of ".$table.": [".$err."]\n";
		}
		
		// Return success status
		return TRUE;
	}
	
	/**
	 * Download information from production database.
	 * 
	 * @param	string	$table
	 * 		The table name to be uploaded.
	 * 
	 * @param	string	$method
	 * 		The upload method.
	 * 		Use 'update' or 'ignore' to react on insert statement.
	 * 
	 * @param	array	$pkeys
	 * 		The table's primary keys.
	 * 
	 * @return	mixed
	 * 		True on success, the error description otherwise.
	 */
	private static function download($table, $method, $pkeys)
	{
		// Initialize error
		$error = "";
		
		$dbc = new dbConnection();
		$dbc->options("MySQL", "db28.grserver.gr", "redback", "rbdbman", "qAoc~935");
		// Get table columns
		$q = new dbQuery("14631754301299", "schema.database");
		$attr = array();
		$attr['table'] = $table;
		$query = $q->getQuery($attr);
		$result = $dbc->execute($query);
		$columns = $dbc->toArray($result, "Field", "Field");
		$types = $dbc->toArray($result, "Field", "Type");
		$defaults = $dbc->toArray($result, "Field", "Default");
		$nulls = $dbc->toArray($result, "Field", "Null");
		
		// Get data
		$q = new dbQuery("28116045007199", "schema.generic");
		$attr = array();
		$attr['table'] = $table;
		$query = $q->getQuery($attr);
		$result = $dbc->execute($query);
		$tableData = $dbc->fetch($result, TRUE);
		
		// Create query
		$query = self::createQuery($method, $table, $tableData, $columns, $types, $defaults, $nulls, $pkeys);

		// If there are no data, return TRUE
		if (empty($query))
			continue;
		
		// Execute query to development server
		$dbc = new dbConnection();
		$dbc->options("MySQL", "redback.dyndns-at-home.com", "redbackdb", "redback", "red2013B@CK");
		$result = $dbc->execute($query);
		if (!$result)
		{
			$err = $dbc->getError();
			return "Error sending data of ".$table.": [".$err."]\n";
		}
		
		// Return success status
		return TRUE;
	}
	
	/**
	 * Creates the insert query for all data.
	 * 
	 * @param	string	$method
	 * 		The insert reaction method.
	 * 		Use 'update' or 'ignore'.
	 * 
	 * @param	string	$tableName
	 * 		The table name.
	 * 
	 * @param	array	$tableData
	 * 		All the table data fetched by the database.
	 * 
	 * @param	array	$columns
	 * 		All the table's columns.
	 * 
	 * @param	array	$types
	 * 		All the types for each column of the table.
	 * 
	 * @param	array	$defaults
	 * 		All the defaults for each column of the table.
	 * 
	 * @param	array	$nulls
	 * 		All the null indicators for each column of the table.
	 * 
	 * @param	array	$pkeys
	 * 		All the primary keys for the table.
	 * 
	 * @return	string
	 * 		The full insert query.
	 */
	private function createQuery($method, $tableName, $tableData, $columns, $types, $defaults, $nulls, $pkeys)
	{
		// Create statement
		$query = "";
		$keyData = array();
		foreach ($tableData as $tData)
		{
			// Store key data
			$keyData[] = $tData[$pkeys[0]];
			
			// Start sql query
			if ($method == "ignore")
				$query .= "INSERT IGNORE INTO ".$tableName." (".implode(", ", $columns).") VALUES ";
			else
				$query .= "INSERT INTO ".$tableName." (".implode(", ", $columns).") VALUES ";
			
			$query .= "(";
			
			// Set field values
			$fieldValues = array();
			foreach ($tData as $field => $value)
			{
				$value = ($value == "" ? ($nulls[$field] == "YES" ? "NULL" : $defaults[$field]) : $value);
				$value = addslashes($value);
				$fieldValues[$field] = (self::stringable($types[$field])  && $value != "NULL" ? "'".str_replace("NULL", "", $value)."'" : $value);
			}
			
			foreach ($tData as $field => $value)
				$query .= $fieldValues[$field].", ";
				
			$query .= ") ";
			$query = str_replace(", )", ")", $query);
			
			if ($method == "update")
			{
				$query .= "ON DUPLICATE KEY UPDATE ";
				foreach ($types as $field => $colType)
					if (!in_array($field, $pkeys))
						$query .= $field." = ".$fieldValues[$field].", ";
			}
						
			$query .= ";\n";
			$query = str_replace(", ;", ";", $query);
		}
		
		return $query;
	}
	
	/**
	 * Checks if a given column type may contain strings and therefore must be quoted.
	 * 
	 * @param	string	$type
	 * 		The column type.
	 * 
	 * @return	boolean
	 * 		True if type is stringable, false otherwise.
	 */
	private static function stringable($type)
	{
		$types = array();
		$types[] = "varchar";
		$types[] = "char";
		$types[] = "text";
		$types[] = "time";
		$types[] = "date";
		$types[] = "enum";
		foreach ($types as $needle)
			if (strpos($type, $needle) !== FALSE)
				return TRUE;

		return FALSE;
	}
}
//#section_end#
?>