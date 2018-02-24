<?php
//#section#[header]
// Namespace
namespace API\Comm;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
// Import

// Usage

class ftp
{
	protected $host;
	protected $port;
	protected $connection;
	protected $username;
	protected $password;
	protected $passive;
	
	// Constructor
	public function __construct() {}
	
	public function set_host($host, $port = 21)
	{
		$this->host = $host;
		$this->port = $port;
	}
	
	public function set_user($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	public function set_passive($passive = TRUE)
	{
		$this->passive = $passive;
	}
	
	// Connect to an ftp server
	protected function connect()
	{
		$this->connection = ftp_connect($this->host, $this->port);
		
		return $this->connection;
	}
	
	// Login user
	protected function login()
	{
		return ftp_login($this->connection, $this->username, $this->password);
	}
	
	// Close the connection
	protected function disconnect()
	{
		return ftp_close($this->connection);
	}
	
	//________________________________________________________________________________ COMMON Methods ________________________________________________________________________________//
	//________________________________________ FILES ________________________________________//
	// Change a file's permissions
	protected function file_chmod($remote_file, $mode = 0644)
	{
		return ftp_chmod($this->connection, $mode, $remote_file);
	}
	
	// Change a file's permissions
	protected function file_delete($remote_file)
	{
		return ftp_delete($this->connection, $remote_file);
	}
	
	//____________________ PUT ____________________//
	// Put a text file
	protected function file_putText($local_file, $remote_file)
	{
		return $this->file_put($local_file, $remote_file, FTP_ASCII);
	}
	
	// Put a binary file
	protected function file_putBinary($local_file, $remote_file)
	{
		return $this->file_put($local_file, $remote_file, FTP_BINARY);
	}
	
	// Put a file
	protected function file_put($local_file, $remote_file, $mode)
	{
		return ftp_put($this->connection, $remote_file, $local_file, $mode);
	}
	
	//____________________ GET ____________________//
	// Put a text file
	protected function file_getText($local_file, $remote_file)
	{
		return $this->file_get($local_file, $remote_file, FTP_ASCII);
	}
	
	// Put a binary file
	protected function file_getBinary($local_file, $remote_file)
	{
		return $this->file_get($local_file, $remote_file, FTP_BINARY);
	}
	
	// Get a file
	protected function file_get($local_file, $remote_file, $mode)
	{
		return ftp_get($this->connection, $local_file, $remote_file, $mode);
	}
	
	//____________________ AUX ____________________//
	// Get file's last modified time
	protected function file_getTime($file)
	{
		return ftp_mdtm($this->connection, $file);
	}
	
	//________________________________________ DIRECTORIES ________________________________________//
	// Create a directory
	protected function dir_create($directory)
	{
		return ftp_mkdir($this->connection, $directory);
	}
	
	// Deletes an empty directory
	protected function dir_delete($directory)
	{
		return ftp_rmdir($this->connection, $directory);
	}
	
	// Deletes a directory recursively
	protected function dir_clear($directory)
	{
		return ftp_rmdir($this->connection, $directory);
	}
	
	// Goes up a directory
	protected function dir_up()
	{
		return ftp_cdup($this->connection);
	}
	
	// Goes up a directory
	protected function dir_open($directory)
	{
		return ftp_chdir($this->connection, $directory);
	}
	
	// Get directory's name
	protected function dir_name()
	{
		return ftp_pwd($this->connection);
	}
	
	// Get a list of files from the given directory
	protected function dir_getFiles($directory = ".")
	{
		return ftp_nlist($this->connection, $directory);
	}
	
	//________________________________________ AUXILIARY ________________________________________//	
	// Renames or moves a file or folder
	protected function move($old_file, $new_file)
	{
		return ftp_rename($this->connection, $old_file, $new_file);
	}
	
	// Execute a command on the server
	protected function execute($command)
	{
		return ftp_exec($this->connection, $command);
	}
}
//#section_end#
?>