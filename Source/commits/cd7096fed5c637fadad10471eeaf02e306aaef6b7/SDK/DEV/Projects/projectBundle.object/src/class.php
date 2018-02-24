<?php
//#section#[header]
// Namespace
namespace DEV\Projects;

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
 * @package	Projects
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;

/**
 * Project Bundle Manager
 * 
 * Manages project bundles for the market.
 * For now only the BOSS market will support bundles.
 * 
 * @version	0.1-1
 * @created	February 27, 2015, 15:22 (EET)
 * @updated	February 27, 2015, 15:22 (EET)
 */
class projectBundle
{
	/**
	 * The bundle id.
	 * 
	 * @type	integer
	 */
	protected $id;
	
	/**
	 * All the bundle projects, for cache.
	 * 
	 * @type	array
	 */
	private static $projects;
	
	/**
	 * Initialize the bundle.
	 * 
	 * @param	integer	$bundleID
	 * 		The bundle id.
	 * 		Leave empty for new bundles.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($bundleID = "")
	{
		$this->id = $bundleID;
	}
	
	/**
	 * Create a new bundle with projects.
	 * 
	 * @param	string	$title
	 * 		The bundle title.
	 * 
	 * @param	array	$projects
	 * 		An array of all project ids to add to the bundle.
	 * 		This list cannot be altered later.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($title, $projects)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Create bundle
		$dbq = new dbQuery("14913077391069", "developer.projects.bundles");
		
		// Set attributes and execute
		$attr = array();
		$attr['title'] = $title;
		$attr['tid'] = team::getTeamID();
		$result = $dbc->execute($dbq, $attr);
		if (!$result)
			return FALSE;
		
		// Fetch bundle id
		$info = $dbc->fetch($result);
		$this->id = $info['id'];
		
		// Add projects to bundle
		$dbq = new dbQuery("16991843019134", "developer.projects.bundles");
		foreach ($projects as $projectID)
		{
			// Set attributes and execute
			$attr = array();
			$attr['bid'] = $this->id;
			$attr['pid'] = $projectID;
			$result = $dbc->execute($dbq, $attr);
		}
		
		return TRUE;
	}
	
	/**
	 * Update bundle information.
	 * 
	 * @param	string	$title
	 * 		The bundle new title.
	 * 
	 * @param	string	$description
	 * 		The bundle description.
	 * 
	 * @param	string	$tags
	 * 		The bundle tags, separated with commas.
	 * 
	 * @param	float	$price
	 * 		The bundle price.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($title, $description, $tags = "", $price = 0)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("2511781857276", "developer.projects.bundles");
		
		// Set attributes and execute
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['tags'] = $tags;
		$attr['price'] = $price;
		$attr['id'] = $this->id;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Set the online status of the bundle for the market.
	 * 
	 * @param	boolean	$status
	 * 		The online status.
	 * 		TRUE for online, FALSE for offline.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setOnlineStatus($status)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("19593898817115", "developer.projects.bundles");
		
		// Set attributes and execute
		$attr = array();
		$attr['status'] = ($status ? 1 : 0);
		$attr['id'] = $this->id;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get all bundle projects.
	 * 
	 * @return	array
	 * 		An array of all project information that are part of this bundle.
	 */
	public function getProjects()
	{
		// Check cache
		if (empty(self::$projects[$this->id]))
		{
			// Initialize database connection
			$dbc = new dbConnection();
			$dbq = new dbQuery("31377323291181", "developer.projects.bundles");
			
			// Set attributes and execute
			$attr = array();
			$attr['id'] = $this->id;
			$result = $dbc->execute($dbq, $attr);
			self::$projects[$this->id] = $dbc->fetch($result, TRUE);
		}
		
		// Return projects
		return self::$projects[$this->id];
	}
	
	/**
	 * Remove this bundle from the database.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("22621082281466", "developer.projects.bundles");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>