<?php
//#section#[header]
// Namespace
namespace API\Developer\misc;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\misc
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
 * Synchronizes redback's developer and publish databases.
 * 
 * @version	{empty}
 * @created	January 22, 2014, 16:10 (EET)
 * @revised	January 23, 2014, 11:35 (EET)
 */
class dbSync
{
	/**
	 * Checks the two stored schemas for differencies.
	 * For now, it checks only the table columns without the keys.
	 * 
	 * @return	boolean
	 * 		Returns an array of "upload" and "delete" fields for the published server.
	 */
	public static function checkSchemas()
	{
		// load schemas
		$localSchema = self::loadXMLSchema("local.testing");
		$pubSchema = self::loadXMLSchema("db10.grserver.gr");
		
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
	 * 		An array of all diferrencies.
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
	 * Loads and parses the schemas of the two databases.
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
		$dbc->options("MySQL", "db10.grserver.gr", "redbackdb", "rb_sql_user", "3fgVb9#0if5$4Rt");
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
		self::generateXMLSchema($pubColumns, "db10.grserver.gr");
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
	 * Uploads all system data to the published server.
	 * 
	 * @return	boolean
	 * 		True on success, the error string containing the tables not updated on failure.
	 */
	public static function uploadSystemData()
	{
		// Initialize error
		$error = "";
		
		// Get data for each table and publish
		$tables = array();
		$tables[] = "update:GLC_region:id";
		$tables[] = "update:GLC_country:id";
		$tables[] = "update:GLC_town:id";
		$tables[] = "update:GLC_timeZone:id";
		$tables[] = "ignore:GLC_timeZoneCountry";
		$tables[] = "update:GLC_language:id";
		$tables[] = "update:GLC_locale:locale";
		$tables[] = "update:GLC_currency:id";
		$tables[] = "ignore:GLC_countryCurrency";
		
		$tables[] = "update:UNIT_moduleStatus:id";
		$tables[] = "update:UNIT_moduleScope:id";
		$tables[] = "update:UNIT_moduleGroup:id";
		$tables[] = "update:UNIT_module:id";
	
		$tables[] = "update:UNIT_domain:name";
		$tables[] = "update:UNIT_pageFolder:id";
		$tables[] = "update:UNIT_page:id";
		
		$tables[] = "update:TR_literalScope:scope";
		$tables[] = "update:TR_literal:id";
		// TEMP
		$tables[] = "update:TR_literalValue:literal_id,locale";
		$tables[] = "update:TR_literalTranslation:id";
		$tables[] = "update:TR_literalTranslationVote:translation_id,translator_id";
		
		$tables[] = "update:PLM_userGroup:id";
		$tables[] = "ignore:PLM_userGroupCommand:userGroup_id,module_id";
		$tables[] = "ignore:PLM_accountAtGroup:account_id,userGroup_id,key";
		
		$tables[] = "update:DEV_projectType:id";
		$tables[] = "update:DEV_projectStatus:id";
		$tables[] = "update:DEV_projectCategory:id";
		
		
		foreach ($tables as $table)
		{
			// Get info
			$tbparts = explode(":", $table);
			$method = $tbparts[0];
			$tableName = $tbparts[1];
			$pkeys = explode(",", $tbparts[2]);
			
			$dbc = new interDbConnection();
			
			// Get table columns
			$q = new dbQuery("14631754301299", "schema.database");
			$attr = array();
			$attr['table'] = $tableName;
			$result = $dbc->execute($q, $attr);
			$columns = $dbc->toArray($result, "Field", "Field");
			$types = $dbc->toArray($result, "Field", "Type");
			$defaults = $dbc->toArray($result, "Field", "Default");
			$nulls = $dbc->toArray($result, "Field", "Null");
			
			// Get data
			$q = new dbQuery("28116045007199", "schema.generic");
			$attr = array();
			$attr['table'] = $tableName;
			$result = $dbc->execute($q, $attr);
			$tableData = $dbc->fetch($result, TRUE);
			
			// Store keys to delete the rest from the upload server
			$keyData = array();
			
			$query = self::createQuery($method, $tableName, $tableData, $columns, $types, $defaults, $nulls, $pkeys);
			
			if (empty($query))
				continue;
			
			$dbc = new dbConnection();
			$dbc->options("MySQL", "db10.grserver.gr", "redbackdb", "rb_sql_user", "3fgVb9#0if5$4Rt");
			$result = $dbc->execute($query);
			if (!$result)
				$error .= "Error sending data of ".$tableName."\n";
			
		}
		
		// Check for error and return status
		if (empty($error))
			return TRUE;
		
		return $error;
	}
	
	/**
	 * Downloads all user data from the published server.
	 * 
	 * @return	boolean
	 * 		True on success, the error string containing the tables not updated on failure.
	 */
	public static function downloadUserData()
	{
		// Initialize error
		$error = "";
		
		// Get data for each table and publish
		$tables = array();
		$tables[] = "update:DEV_project:id";
		$tables[] = "ignore:DEV_accountToProject:account_id,project_id";
		
		$tables[] = "update:RB_person:id";
		$tables[] = "update:RB_company:id";
		$tables[] = "update:PLM_account:id";
		$tables[] = "ignore:PLM_accountAtGroup:account_id,userGroup_id,key";
		$tables[] = "ignore:PLM_personToAccount:person_id,account_id";
		
		$tables[] = "update:RB_apps:id";
		$tables[] = "update:TR_translator:id";
		
		foreach ($tables as $table)
		{
			// Get info
			$tbparts = explode(":", $table);
			$method = $tbparts[0];
			$tableName = $tbparts[1];
			$pkeys = explode(",", $tbparts[2]);
			
			
			$dbc = new dbConnection();
			$dbc->options("MySQL", "db10.grserver.gr", "redbackdb", "rb_sql_user", "3fgVb9#0if5$4Rt");
			// Get table columns
			$q = new dbQuery("14631754301299", "schema.database");
			$attr = array();
			$attr['table'] = $tableName;
			$query = $q->getQuery($attr);
			$result = $dbc->execute($query);
			$columns = $dbc->toArray($result, "Field", "Field");
			$types = $dbc->toArray($result, "Field", "Type");
			$defaults = $dbc->toArray($result, "Field", "Default");
			$nulls = $dbc->toArray($result, "Field", "Null");
			
			// Get data
			$q = new dbQuery("28116045007199", "schema.generic");
			$attr = array();
			$attr['table'] = $tableName;
			$query = $q->getQuery($attr);
			$result = $dbc->execute($query);
			$tableData = $dbc->fetch($result, TRUE);
			
			// Create query
			$query = self::createQuery($method, $tableName, $tableData, $columns, $types, $defaults, $nulls, $pkeys);

			if (empty($query))
				continue;
			
			// Execute query to development server
			$dbc = new dbConnection();
			$dbc->options("MySQL", "redback.dyndns-at-home.com", "redbackdb", "redback", "red2013B@CK");
			$result = $dbc->execute($query);
			if (!$result)
				$error .= "Error getting data of ".$tableName."\n";
		}
		
		// Check for error and return status
		if (empty($error))
			return TRUE;
		
		return $error;
	}
	
	/**
	 * Creates the insert query for all data.
	 * 
	 * @param	string	$method
	 * 		Defines the insert method. "ignore" or "update".
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
	 * 		The insert query.
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
	 * {description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	void
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