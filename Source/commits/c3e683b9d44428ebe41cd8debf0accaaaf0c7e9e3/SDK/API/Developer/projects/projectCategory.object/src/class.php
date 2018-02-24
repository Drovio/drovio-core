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
class projectCategory
{
	public static function create($name)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("951976139", "developer.projects.categories");
		
		// Set attributes and execute
		$attr = array();
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		$projectCategory = $dbc->fetch($result);
		
		if ($projectCategory)
			return $projectCategory['id'];
		
		return FALSE;
	}
	
	public static function get($array = FALSE)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("1100563189", "developer.projects.categories");
		
		// Set attributes and execute
		$attr = array();
		$result = $dbc->execute($dbq, $attr);
		
		if (!$array)
			return $result;
		
		$categories = $dbc->fetch($result, TRUE);
		return $project;
	}
	
	public static function info($id)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("331603421", "developer.projects.categories");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
			return $project;
		
		return FALSE;
	}
	
	public static function createType($category, $name)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("1037618671", "developer.projects.categories");
		
		// Set attributes and execute
		$attr = array();
		$attr['category'] = $category;
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		$projectType = $dbc->fetch($result);
		
		if ($project)
			return $projectType['id'];
		
		return FALSE;
	}
	
	public static function getTypes($category = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		$attr = array();
		
		// Get query
		if (empty($category))
			$dbq = new dbQuery("1100563189", "developer.projects.categories");
		else
		{
			$attr['category'] = $category;
			$dbq = new dbQuery("184965085", "developer.projects.categories");
		}
		
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>