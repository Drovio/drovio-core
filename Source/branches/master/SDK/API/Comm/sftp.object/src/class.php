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
class sftp
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
	public function connect($address, $username, $password)
	{ 
		// Set up basic connection
		$this->connectionID = ssh2_connect($address);
		if (is_null($this->connectionID))
			return FALSE;
		
		// Login user
		$status = ssh2_auth_password($this->connectionID, $username, $password);
		if (!$status)
		{
			$this->close();
			return FALSE;
		}
		
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
	public function put($localFilePath, $remoteFilePath, $remoteFileName = "", $mode = 0644)
	{
		// Check if local file exists
		if (!file_exists($localFilePath))
			return FALSE;
		
		// Set remote file path properly
		if (!empty($remoteFileName))
			$remoteFilePath .= "/".$remoteFileName;
			
		// Upload the file
		$mode = (empty($mode) ? 0644 : $mode);
		if (ssh2_scp_send($this->connectionID, $remoteFilePath, $localFilePath, $mode))
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
		// Set remote file path properly
		if (!empty($remoteFileName))
			$remoteFilePath .= "/".$remoteFileName;
		
		// Create the stream to write
		$sftp = ssh2_sftp($this->connectionID);
		$stream = fopen("ssh2.sftp://".$sftp."/".$remoteFilePath, 'w');
		if (!$stream)
			return FALSE;
		
		// Write to file
		if (fwrite($stream, $contents))
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
		$sftp = ssh2_sftp($this->connectionID);
		if (ssh2_sftp_mkdir($sftp, $name))
		        return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Returns the ftp connection object
	 * 
	 * @return	resource
	 * 		The connection resource.
	 */
	public function getConnection()
	{
		return ssh2_sftp($this->connectionID);
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
		return FALSE;
		
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
		unset($this->connectionID);
	}
}
//#section_end#
?>