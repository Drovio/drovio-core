<?php
//#section#[header]
// Namespace
namespace API\Developer\projects;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;

class project
{
	public static function create($title, $category, $repository, $description = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("906810998", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['title'] = $title;
		$attr['category'] = $category;
		$attr['repository'] = $repository;
		$attr['description'] = $description;
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
			return $project['id'];
		
		return FALSE;
	}
	
	public static function info($id)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("826439764", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
			return $project;
		
		return FALSE;
	}
	
	public static function update($id, $title, $description = "", $editorSub = "", $editorPath = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("994839582", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['editorSub'] = $editorSub;
		$attr['editorPath'] = $editorPath;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	public static function setType($id, $type)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("892768812", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['type'] = $type;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	public static function updateStatus($id, $status)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("254607052", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['status'] = $status;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
}
//#section_end#
?>