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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "logger");

use \DEV\Profiler\logger;

/**
 * FTP Manager
 * 
 * Creates an ftp connection and manages all file transports and directory actions.
 * 
 * @version	6.1-1
 * @created	July 8, 2014, 12:48 (EEST)
 * @updated	May 22, 2015, 16:05 (EEST)
 */
class ftp
{
	/**
	 * FTP stream
	 * 
	 * @type	resource
	 */
	private $connectionID;

	/**
	 * Ensures that any ftp connection is successfully closed before the destruction of the class
	 * 
	 * @return	void
	 */
	public function __destruct()
	{
		// Close connection
		$this->close();
	}

	/**
	 * Connect to an ftp server
	 * 
	 * @param	string	$address
	 * 		The FTP server address.
	 * 		This parameter shouldn't have any trailing slashes and shouldn't be prefixed with ftp://.
	 * 
	 * @param	string	$username
	 * 		The ftp user username.
	 * 
	 * @param	string	$password
	 * 		The ftp user password.
	 * 
	 * @param	boolean	$passive
	 * 		Turns on the passive mode.
	 * 		In passive mode, data connections are initiated by the client, rather than by the server.
	 * 		It may be needed if the client is behind firewall.
	 * 		Default value is FALSE.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public function connect($address, $username, $password, $passive = FALSE)
	{ 
		// Set up basic connection
		$this->connectionID = ftp_connect($address);
		if (is_null($this->connectionID))
			return FALSE;
		
		// Login user
		$status = ftp_login($this->connectionID, $username, $password);		
		if (!$status)
		{
			$this->close();
			return FALSE;
		}
		
		// Turn passive mode on
		if ($passive)
			ftp_pasv($this->connectionID, TRUE);
		
		// Connection was successful, return TRUE
		return TRUE;
	}

	/**
	 * Uploads a file to the given location
	 * 
	 * @param	string	$localFilePath
	 * 		The local file path.
	 * 
	 * @param	string	$remoteFilePath
	 * 		The remote file path.
	 * 
	 * @param	string	$remoteFileName
	 * 		The remote file name.
	 * 		It is used basically for saving the file with different name. If $dName is empty the file name assumed to be concacated with the filepath ($dPath).
	 * 
	 * @param	mixed	$mode
	 * 		The ftp mode.
	 * 		Leave empty for auto mode.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public function put($localFilePath, $remoteFilePath, $remoteFileName = "", $mode = NULL)
	{
		// Check if local file exists
		if (!file_exists($localFilePath))
			return FALSE;
	
		// Set the transfer mode
		if (empty($mode))
		{
			// Get file extension
			$fileExtension = end(explode('.', $localFilePath));
			
			// Choose mode
			$asciiExtensions = array('txt', 'csv');
			$mode = (in_array($fileExtension, $asciiExtensions) ? FTP_ASCII : FTP_BINARY);
		}
		
		// Set remote file path properly
		if (!empty($remoteFileName))
			$remoteFilePath .= "/".$remoteFileName;
			
		// Upload the file
		if (ftp_put($this->connectionID, $remoteFilePath, $localFilePath, $mode))
			return TRUE;
		
		return FALSE;
	}
	
	
	/**
	 * Creates a files to the given remote path and writes its contents
	 * 
	 * @param	string	$remoteFilePath
	 * 		The remote file path.
	 * 
	 * @param	string	$contents
	 * 		The contents to be written to the file.
	 * 
	 * @param	string	$remoteFileName
	 * 		The remote file name.
	 * 		It is used basically for saving the file with different name. If $dName is empty the file name assumed to be concacated with the filepath ($dPath).
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public function write($remoteFilePath, $contents = "", $remoteFileName = "")
	{
		// Set default ascii mode
		$mode = FTP_ASCII;
		
		// Set remote file path properly
		if (!empty($remoteFileName))
			$remoteFilePath .= "/".$remoteFileName;
		
		// Create the stream to write
		$stream = fopen('data://text/plain,' . $contents, 'r');
		
		// Write to file
		if (ftp_fput($this->connectionID, $remoteFilePath, $stream, $mode))
			return TRUE;
		
		return FALSE;

	}
	
	/**
	 * Creates a folder to the remote location
	 * 
	 * @param	string	$name
	 * 		The folder name.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public function makeDir($name)
	{
		// Create folder
		if (ftp_mkdir($this->connectionID, $name))
		        return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Returns a list of files in the given directory.
	 * 
	 * @param	string	$directory
	 * 		The directory to be listed.
	 * 		Note that to avoid some issues with filenames containing spaces and other characters, this parameter should be escaped.
	 * 
	 * @param	string	$parameters
	 * 		Additional parameters to be considered in oder to return more info about eac file.
	 * 		The Default is '-la'
	 * 
	 * @return	mixed
	 * 		An array of filenames from the specified directory on success or FALSE on error.
	 */
	public function getDirContents($directory = '.', $parameters = '-la')
	{
		// Get contents of the current directory
		return ftp_nlist($this->connectionID, $parameters . '  ' . $directory);
	}
	
	/**
	 * Returns the ftp connection object
	 * 
	 * @return	resource
	 * 		The connection resource.
	 */
	public function getConnection()
	{
		return $this->connectionID;
	}
	
	/**
	 * Tries to change the current ftp working directory.
	 * If the create flag is TRUE, the target directory will be created if is not exists and then it will be set as current.
	 * 
	 * @param	string	$directory
	 * 		The target directory.
	 * 
	 * @param	boolean	$create
	 * 		Create the directory if doesn't exist.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on Success, False elsewhere
	 */
	public function changeDir($directory, $create = FALSE)
	{ 
		// If changing directory fails, PHP will throw a warning.
        	$status = ftp_chdir($this->connectionID, $directory);
        	if (!$status)
        	{
			// If failed to change the current directory
            		if ($create)
            		{
				// Attempt to create possible missing target dir
				if ($this->makeDir($directory))
					return $this->changeDir($directory, FALSE);
			}
			
			return FALSE;
        	}

		// Return status
        	return $status;
	}
	
	/**
	 * Closes the ftp connection
	 * 
	 * @return	void
	 */
	public function close()
	{
		// close the connection
		if (!is_null($this->connectionID))
		{
			ftp_close($this->connectionID);
			unset($this->connectionID);
		}
	}
}
//#section_end#
?>