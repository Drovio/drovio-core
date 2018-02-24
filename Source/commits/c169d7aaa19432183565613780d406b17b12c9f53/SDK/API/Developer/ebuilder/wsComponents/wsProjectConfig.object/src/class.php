<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder\wsComponents;

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
 * @namespace	\ebuilder\wsComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");

use \API\Resources\DOMParser;

/**
 * Website Project Config
 * 
 * Class to set and alter website project's settings
 * 
 * @version	{empty}
 * @created	March 17, 2014, 22:47 (EET)
 * @revised	March 17, 2014, 22:47 (EET)
 */
class wsProjectConfig
{
	/**
	 * The filepath (inclunding name) for the config file
	 * 
	 * @type	string
	 */
	private $filename;
	
	/**
	 * {description}
	 * 
	 * @type	DOMParser
	 */
	private $parser;

	/**
	 * The contructor method
	 * 
	 * @param	string	$filename
	 * 		The filepath (inclunding name) for the config file
	 * 
	 * @return	void
	 */
	public function __construct($filename)
	{
		$this->filenme = $filename;
	}
	
	/**
	 * Returns the object instance
	 * 
	 * @return	wsProjectConfig
	 * 		{description}
	 */
	public function get()
	{
		$this->load();
		return $this;
	}
	
	/**
	 * Loads the file into context ($parser variable)
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	private function load()
	{
		$this->parser = new DOMParser();
		return $this->parser->load($this->filenme);
	}
	
	/**
	 * Create the config file
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create()
	{
		// Create Website configuration root
		$parser = new DOMParser();
		$root = $parser->create("configuration");
		$parser->append($root);
		return $parser->save($this->filenme, '');
	}
	
	/**
	 * Set the value of template option into the file
	 * 
	 * @param	The template id	$value
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function setTemplate($value)
	{
		$tmpl = $this->parser->evaluate("//template")->item(0);
		if(!is_null($tmpl))
			$this->parser->nodeValue($tmpl, $value);
		else
		{
			$root = $this->parser->evaluate("//configuration")->item(0);
			// Link website with template
			$tmpl = $this->parser->create("template", $value);
			$this->parser->append($root, $tmpl);
		}		
		return $parser->update();
	}
	
	/**
	 * Return the template id that is used for the website
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getTemplate()
	{
		return $this->parser->evaluate("//template")->item(0)->nodeValue;
	}
	
	/**
	 * Set the value of server option into the file
	 * 
	 * @param	string	$name
	 * 		The server's name
	 * 
	 * @param	string	$address
	 * 		The server's address
	 * 
	 * @return	void
	 */
	public function setServer($name, $address)
	{
		$srv = $this->parser->evaluate("//server[@name='".$name."']")->item(0);
		if(is_null($srv))
		{
			$root = $this->parser->evaluate("//configuration")->item(0);
			// Link website with template
			$srv = $this->parser->create("server");
			$this->parser->append($root, $srv);
		}
		$this->parser->attr($srv, "name", $name);
		$this->parser->attr($srv, "address", $address);	
		
		return $parser->update();
	}
	
	/**
	 * Get the Server's properties
	 * 
	 * @param	string	$name
	 * 		The server's name
	 * 
	 * @return	array
	 * 		Array{name, address}
	 */
	public function getServer($name)
	{
		$result = array();
		$srv = $this->parser->evaluate("//server[@name='".$name."']")->item(0);
		
		$result['name'] = $this->parser->attr($srv, "name");
		$result['address'] = $this->parser->attr($srv, "address");
		
		return $result;
	}
	
	/**
	 * A list of all register servers
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getServerList()
	{
		$result = array();
		$servers = $this->parser->evaluate("//server");
		foreach($servers as $server)
		{
			$result[] = $this->parser->attr($server, 'name');
		}
		return $result;
	}
}
//#section_end#
?>