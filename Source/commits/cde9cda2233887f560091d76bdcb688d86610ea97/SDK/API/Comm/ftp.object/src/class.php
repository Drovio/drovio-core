<?php
//#section#[header]
// Namespace
namespace API\Comm;

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
 * @package	Comm
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "logger");

use \DEV\Profiler\logger;

/**
 * FTP Manager
 * 
 * Creates an ftp connection and manages all file transports and directory actions.
 * 
 * @version	3.0-1
 * @created	July 8, 2014, 12:48 (EEST)
 * @revised	November 3, 2014, 9:38 (EET)
 */
class ftp
{

	/**
	 * FTP stream
	 * 
	 * @type	resource
	 */
	private $connID;

	/**
	 * The constructor method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		//  Place constructor code here 
	}

	/**
	 * The deconstructor method. Ensures that any ftp connection is successfully closed opun the destruction of the class
	 * 
	 * @return	void
	 */
	public function __destruct()
	{
		if (!is_null($this->connID))
			$this->close();
	}

	/**
	 * Connect to an ftp server
	 * 
	 * @param	string	$address
	 * 		The FTP server address. This parameter shouldn't have any trailing slashes and shouldn't be prefixed with ftp://.
	 * 
	 * @param	string	$ftpUser
	 * 		The ftp user username
	 * 
	 * @param	string	$ftpPassword
	 * 		The ftp user password
	 * 
	 * @param	boolean	$passive
	 * 		turns on the passive mode. In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind firewall. The Default is FALSE
	 * 
	 * @return	boolean
	 * 		True on Success, False elsewhere
	 */
	public function connect($address, $ftpUser, $ftpPassword, $passive = FALSE)
	{ 
		// set up basic connection
		$this->connID = ftp_connect($address);
		if (is_null($this->connID))
		{
			logger::log("Server at ".$address." is not accessible", logger::NOTICE);
			return FALSE;
		}
		
		$login = ftp_login($this->connID, $ftpUser, $ftpPassword);		
		if (!$login)
		{
			$this->close();
			logger::log("Failed to authendicate user ".$ftpUser, logger::WARNING);
			return FALSE;
		}
		
		// turn passive mode on
		if ($passive)
			ftp_pasv($this->connID, TRUE);
			
		return TRUE;
	}

	/**
	 * Uploads a file to the given location
	 * 
	 * @param	string	$oFile
	 * 		The local file path.
	 * 
	 * @param	string	$dPath
	 * 		The remote file path.
	 * 
	 * @param	string	$dName
	 * 		The remote file name. Used basically for saving the file with different name. If $dName is empty the file name assumed to be concacated with the filepath ($dPath)
	 * 
	 * @return	boolean
	 * 		True on Success, False elsewhere
	 */
	public function put($oFile, $dPath, $dName = '')
	{
		if (!file_exists($oFile))
		{
			logger::log("The ".$oFile." does not exist ", logger::ERROR);
			return FALSE;
		}
	
		// Set the transfer mode		
		$asciiArray = array('txt', 'csv');
		$extension = end(explode('.', $fileFrom));
		if (in_array($extension, $asciiArray))
			$mode = FTP_ASCII;
		else
			$mode = FTP_BINARY;
		
		//ftp_chdir($this->connID, '/www/site/');
		if(!empty($dName))
			$dPath .= "/".$dName;
			
		// upload a file
		if (ftp_put($this->connID, $dPath, $oFile, $mode))
		{
			logger::log("File ".$oFile." was uploaded to ".$dPath, logger::INFO);
			return TRUE;
		} 
		else
		{
			logger::log("Could not upload ".$oFile." to ".$dPath, logger::ERROR);
			return FALSE;
		}
	}
	
	
	/**
	 * Creates a folder to the remote location
	 * 
	 * @param	string	$name
	 * 		The folder name
	 * 
	 * @return	boolean
	 * 		True on Success, False elsewhere
	 */
	public function makeDir($name)
	{
		// *** If creating a directory is successful...
		if (ftp_mkdir($this->connID, $name))
		{
			logger::log("Directory ".$dir." was created", logger::INFO);;
		        return TRUE;
		}
		else
		{
			logger::log("Could not create".$dir." directory", logger::ERROR);
			return FALSE;
		}
	}
	
	/**
	 * Returns a list of files in the given directory
	 * 
	 * @param	string	$dir
	 * 		The directory to be listed. 
	 * 		Note that to avoid some issues with filenames containing spaces and other characters, this parameter should be escaped
	 * 
	 * @param	string	$parameters
	 * 		Additional parameters to be considered in oder to return more info about eac file. The Default is '-la'
	 * 
	 * @return	array
	 * 		Returns an array of filenames from the specified directory on success or FALSE on error.
	 */
	public function getDirContents($dir = '.', $parameters = '-la')
	{
		//ftp_chdir($directory);
		// get contents of the current directory
		$contentsArray = ftp_nlist($this->connectionId, $parameters . '  ' . $dir);
		return $contentsArray;
	}
	
	/**
	 * Returns the ftp connection object
	 * 
	 * @return	resource
	 * 		The connection resource.
	 */
	public function getConnection()
	{
		return $this->connID;
	}
	
	/**
	 * Closes the ftp connection
	 * 
	 * @return	void
	 */
	public function close()
	{
		// close the connection
		ftp_close($this->connID);
		unset($this->connID);
	}
}
//#section_end#
?>