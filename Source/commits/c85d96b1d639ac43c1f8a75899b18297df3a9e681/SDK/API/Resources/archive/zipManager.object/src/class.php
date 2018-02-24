<?php
//#section#[header]
// Namespace
namespace API\Resources\archive;

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
 * @package	Resources
 * @namespace	\archive
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "accessControl");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("DEV", "Profiler", "logger");

use \ZipArchive;
use \API\Platform\accessControl;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\directory;
use \DEV\Profiler\logger;

/**
 * Zip Manager
 * 
 * System's zip archive manager
 * 
 * @version	0.1-3
 * @created	April 11, 2013, 22:35 (EEST)
 * @revised	November 5, 2014, 17:01 (EET)
 */
class zipManager
{
	/**
	 * Used in zipManager::read() to get resource's location inside the zip file
	 * 
	 * @type	integer
	 */
	const LOCATION = 0;
	/**
	 * Used in zipManager::read() to get resource from a zip file
	 * 
	 * @type	integer
	 */
	const RESOURCE = 1;
	/**
	 * Used in zipManager::read() to get resource's contents from the zip file
	 * 
	 * @type	integer
	 */
	const CONTENTS = 2;

	/**
	 * Checks if a file exists and is a zip archive
	 * 
	 * @param	string	$archive
	 * 		The path of the archive
	 * 
	 * @return	boolean
	 * 		True if file exists and is a zip archive, false otherwise
	 */
	public static function exists($archive)
	{
		$archive = directory::normalize($archive);
	
		// Log activity
		logger::log("Checking zip archive '".$archive."' ...");
		
		// Open zip
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("File '".$archive."' is not a zip archive.", logger::WARNING);
			return FALSE;
    		}
		$zip->close();
		logger::log("File '".$archive."' is a zip archive.", logger::NOTICE);
		return TRUE;
	}

	/**
	 * Create a zip archive that contains the desired files/folders.
	 * 
	 * @param	string	$archive
	 * 		The path of the new archive
	 * 
	 * @param	array	$contents
	 * 		An array that holds the paths of the files/folders to include in the zip:
	 * 		['dirs'] for directories
	 * 		['files'] for files
	 * 
	 * @param	boolean	$recursive
	 * 		If set to TRUE, all necessary parent folders of the archive will be created as well.
	 * 
	 * @param	boolean	$includeHidden
	 * 		If set to TRUE, hidden files and folders will be included in the zip.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function create($archive, $contents = array(), $recursive = FALSE, $includeHidden = FALSE)
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Creating zip archive '".$archive."' ...");
		
		// Create Directory
		if (!is_dir(dirname($archive)))
		{
			if ($recursive)
			{
				logger::log("Creating parent folders...");
				folderManager::create(dirname($archive), "", 0777, TRUE);
			}
			else
			{
				logger::log("Parent folder doesn't exists. Aborting...", logger::ERROR);
				return FALSE;
			}
		}
		
		if (empty($contents))
		{
			logger::log("No contents to add in archive. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Check if $contents has proper format
		if (!is_array($contents))
		{
			logger::log("Contents must be passed in an array. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		$allowed = array('dirs', 'files'); 
		$contents = array_intersect_key($contents, array_flip($allowed));
		if (empty($contents))
		{
			logger::log("No files found. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		foreach ($contents as $array)
			if (!is_array($array))
			{
				logger::log("Contents must contain a 'dirs' and/or a 'files' array. Aborting...", logger::WARNING);
				return FALSE;
			}

		// Create zip
		$zip = new ZipArchive();
		if ($zip->open($archive, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE ) !== TRUE)
		{
			logger::log("Cannot initialize zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}
		$status = self::zipFiles($zip, $contents, "", "", $includeHidden);
		$zip->close();
		
		if ($status)
			logger::log("Zip '".$archive."' created successfully.", logger::NOTICE);
		else
			logger::log("Zip '".$archive."' failed to be created.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Extracts the contents of a zip archive to the specified location.
	 * 
	 * @param	string	$archive
	 * 		The path of the archive.
	 * 
	 * @param	string	$destination
	 * 		The location where the extracted files will be placed into.
	 * 
	 * @param	boolean	$recursive
	 * 		If set to TRUE, all necessary parent folders of the destination folder will be created as well.
	 * 
	 * @param	mixed	$entries
	 * 		A (list of) file(s) / index(es) inside the zip that represents the file(s) to be extracted. If left empty, the whole archive is extracted.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function extract($archive, $destination, $recursive = FALSE, $entries = NULL)
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Extracting zip archive '".$archive."' ...");
		
		// Check Directories
		if (!file_exists($archive) || (!$recursive && !is_dir($destination."/")))
		{
			logger::log("Source file or destination folder doesn't exist. Aborting...", logger::ERROR);
			return FALSE;
		}
		
		// Create $destination folder if needed and if 'recursive'
		if ($recursive && (!is_dir($destination."/")))
		{
			logger::log("Creating parent folders...");
			folderManager::create($destination."/", "", 0777, TRUE);
		}
		
		
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("Cannot open zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}
		
		// Flip index entries to names
		if (is_int($entries))
			$entries = self::identify($zip, $entries);
		
		if (is_array($entries))
			foreach ($entries as $key => $e)
				if (is_int($e))
					$entries[$key] = self::identify($zip, $e);
		
		// Create zip
		$status = $zip->extractTo($destination, $entries);
		$zip->close();
		
		if ($status)
			logger::log("Zip '".$archive."' extracted successfully in '".$destination."'.", logger::NOTICE);
		else
			logger::log("Zip '".$archive."' failed to be extracted in the specified directory.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Returns the details of a zip archive in an array.
	 * 
	 * @param	string	$archive
	 * 		The path of the archive.
	 * 
	 * @param	mixed	$entry
	 * 		Get specific details for an entry by giving its name or index in the archive
	 * 
	 * @param	boolean	$byName
	 * 		Request result array keys to be the entries indicies, or names (if set to TRUE)
	 * 
	 * @return	array
	 * 		The details of a zip archive:
	 * 		['archive'] for general details
	 * 		['entries'] for specific entry details
	 * 		
	 * 		The general details include the following (keys):
	 * 			length		-> Number of entries in the archive
	 * 			status		-> The status of the archive in readable form
	 * 			_status		-> The archive status code
	 * 			_systemStatus	-> The system's status code
	 * 			file		-> Name of the archive file
	 * 			comment		-> Archive's comment
	 * 		
	 * 		The entry details include the following (keys):
	 * 			name		-> Entry's name in the archive
	 * 			index		-> Entry's index in the archive
	 * 			crc		-> Entry's crc code
	 * 			size		-> Entry's uncompressed size in bytes
	 * 			lastModified	-> Last modification time of the file that represents the entry
	 * 			compressedSize	-> Entry's compressed size in bytes
	 * 			compressionMethod	-> The amount (code) of compression in the archive
	 */
	public static function getDetails($archive, $entry = NULL, $byName = FALSE)
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Acquiring details for zip archive '".$archive."' ...");
		
		// Check source
		if (!file_exists($archive))
		{
			logger::log("Source file doesn't exist. Aborting...", logger::ERROR);
			return FALSE;
		}
			
		// Open zip
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("Cannot open zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}

		$details = self::acquireDetails($zip, $entry, $byName);
		$zip->close();
		
		if ($details === FALSE)
			logger::log("Could not acquire details for zip '".$archive."'.", logger::ERROR);
		else
			logger::log("Details for zip '".$archive."' acquired successfully.", logger::NOTICE);
		
		return $details;
	}
	
	/**
	 * Appends files/folders in the archive
	 * 
	 * @param	string	$archive
	 * 		The path of the archive.
	 * 
	 * @param	array	$files
	 * 		An array that holds the paths of the files/folders to append in the zip:
	 * 		['dirs'] for directories
	 * 		['files'] for files
	 * 
	 * @param	string	$innerDirectory
	 * 		A directory inside the archive where the appended files will be placed. If empty, all files will be appended in the archive's root
	 * 
	 * @param	boolean	$includeHidden
	 * 		If set to TRUE, hidden files and folders will be appended.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function append($archive, $files = array(), $innerDirectory = "", $includeHidden = FALSE)
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Appending to zip '".$archive."' ...");
		
		// Check Source
		if (!file_exists($archive))
		{
			logger::log("Source file doesn't exist. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Check if $files has proper format
		if (!is_array($files))
		{
			logger::log("Files must be passed in an array. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		$allowed = array('dirs', 'files'); 
		$files = array_intersect_key($files, array_flip($allowed));
		if (empty($files))
		{
			logger::log("No files found. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		foreach ($files as $array)
			if (!is_array($array))
			{
				logger::log("Contents must contain a 'dirs' and/or a 'files' array. Aborting...", logger::WARNING);
				return FALSE;
			}
		
		// Append to zip
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("Cannot open zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}
		if (!empty($innerDirectory))
			$innerDirectory = trim($innerDirectory, "/")."/";
		$status = self::zipFiles($zip, $files, "", $innerDirectory, $includeHidden);
		$zip->close();
		
		if ($status)
			logger::log("Files appended successfully to '".$archive."'.", logger::NOTICE);
		else
			logger::log("Failed to append files to '".$archive."'.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Creates an empty dir in an archive
	 * 
	 * @param	string	$archive
	 * 		The path of the archive
	 * 
	 * @param	string	$innerDirectoryName
	 * 		Name of the new folder
	 * 
	 * @param	string	$innerParentDirectory
	 * 		Path inside the archive
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function createInnerDirectory($archive, $innerDirectoryName, $innerParentDirectory = "")
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Creating empty directory in zip '".$archive."' ...");
		
		// Check Source and append to archive
		$zip = new ZipArchive();
		$openStatus = FALSE;
		if (!file_exists($archive))
			$openStatus = $zip->open($archive, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
		else
			$openStatus = $zip->open($archive);
		
		if ($openStatus !== TRUE)
		{
			logger::log("Cannot initialize zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}

		$innerDirectoryName = trim($innerDirectoryName, "/")."/";
		if (!empty($innerParentDirectory) && is_string($innerParentDirectory))
			$innerParentDirectory = trim(directory::normalize($innerParentDirectory), "/")."/";
		
		$status = $zip->addEmptyDir($innerParentDirectory.$innerDirectoryName);
		$zip->close();
		
		if ( $status )
			logger::log("Directory created successfully in '".$archive."'.", logger::NOTICE);
		else
			logger::log("Could not create the directory in '".$archive."'.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Appends "on the fly" the $fileContents in the specified archive
	 * 
	 * @param	string	$archive
	 * 		The path of the archive
	 * 
	 * @param	string	$fileContents
	 * 		Contents for the file in the archive
	 * 
	 * @param	string	$fileName
	 * 		Name of the file in the archive
	 * 
	 * @param	string	$innerDirectory
	 * 		Inner archive directory where the file will be placed
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function createInnerFile($archive, $fileContents, $fileName, $innerDirectory = "")
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Creating file in zip '".$archive."' ...");
		
		if (empty($fileName))
		{
			logger::log("Wrong file name. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Check Source and append to archive
		$zip = new ZipArchive();
		$openStatus = FALSE;
		if (!file_exists($archive))
			$openStatus = $zip->open($archive, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
		else
			$openStatus = $zip->open($archive);
		
		if ($openStatus !== TRUE)
		{
			logger::log("Cannot initialize zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}

		$fileName = trim($fileName, "/");
		if (!empty($innerDirectory))
			$innerDirectory = trim(directory::normalize($innerDirectory), "/")."/";
		
		$status = $zip->addFromString($innerDirectory.$fileName, $fileContents);
		$zip->close();
		
		if ( $status )
			logger::log("File created successfully in '".$archive."'.", logger::NOTICE);
		else
			logger::log("Could not create the file in '".$archive."'.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Removes files/folders from the archive
	 * 
	 * @param	string	$archive
	 * 		The path of the archive.
	 * 
	 * @param	mixed	$contents
	 * 		A (list of) file(s) / index(es) inside the zip that represents the file(s) to be removed.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function remove($archive, $contents)
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Remove from zip '".$archive."' ...");
		
		// Check Source
		if (!file_exists($archive))
		{
			logger::log("Source file doesn't exist. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Check if $files has proper format
		if (!is_array($contents))
		{
			$contents = array( $contents );
		}
		
		// Remove from zip
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("Cannot open zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}
		$status = self::removeFiles($zip, $contents);
		$zip->close();
		
		if ($status)
			logger::log("Files removed successfully from '".$archive."'.", logger::NOTICE);
		else
			logger::log("Failed to remove files from '".$archive."'.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Renames the contents of the archive
	 * 
	 * @param	string	$archive
	 * 		The path of the archive.
	 * 
	 * @param	mixed	$contents
	 * 		A (list of) file(s) / index(es) inside the zip that represents the file(s) to be renamed.
	 * 
	 * @param	mixed	$newNames
	 * 		A (list of) new name(s) for the renamed entries.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function rename($archive, $contents, $newNames)
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Rename entries in zip '".$archive."' ...");
		
		// Check Source
		if (!file_exists($archive))
		{
			logger::log("Source file doesn't exist. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Check if $contents has proper format
		if (!is_array($contents))
			$contents = array( $contents );
			
		// Check if $newNames has proper format
		if (!is_array($newNames))
			$newNames = array( $newNames );
		
		// Rename in zip
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("Cannot open zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}
		$status = self::renameFiles($zip, $contents, $newNames);
		$zip->close();
		
		if ($status)
			logger::log("Files renamed successfully in '".$archive."'.", logger::NOTICE);
		else
			logger::log("Failed to rename files in '".$archive."'.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Relocates contents inside the archive (NOT YET IMPLEMENTED)
	 * 
	 * @param	string	$archive
	 * 		The path of the archive.
	 * 
	 * @param	mixed	$origins
	 * 		A (list of) file(s) / index(es) inside the zip that represents the file(s) to be relocated.
	 * 
	 * @param	mixed	$destinations
	 * 		A (list of) destination(s) for the relocated entries.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function copy($archive, $origins, $destinations)
	{
		$archive = directory::normalize($archive);
		
		return FALSE;
		// Log activity
		logger::log("Copy entries in zip '".$archive."' ...");
		
		// Check Source
		if (!file_exists($archive))
		{
			logger::log("Source file doesn't exist. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Check if $origins has proper format
		if (!is_array($origins))
			$origins = array( $origins );
			
		// Check if $destinations has proper format
		if (!is_array($destinations))
			$destinations = array( $destinations );
		
		// Copy in zip
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("Cannot open zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}
		$status = self::copyFiles($zip, $origins, $destinations);
		$zip->close();
		
		if ($status)
			logger::log("Files copied successfully in '".$archive."'.", logger::NOTICE);
		else
			logger::log("Failed to copy files in '".$archive."'.", logger::ERROR);
		
		return $status;
	}
	
	/**
	 * Reads a source from the archive.
	 * 
	 * @param	string	$archive
	 * 		The path of the archive.
	 * 
	 * @param	mixed	$identifier
	 * 		A file / index inside the zip that represent the file to be read.
	 * 
	 * @param	integer	$typeOfResponse
	 * 		The type of the returned value. Use zipManager::LOCATION for the location of the resource, zipManager::CONTENTS for the contents of the resource, and zipManager::RESOURCE for the resource itself.
	 * 
	 * @return	mixed
	 * 		Returns either the location of the resource, the contents of the resource, or the resource itself.
	 */
	public static function read($archive, $identifier, $typeOfResponse = self::CONTENTS)
	{
		$archive = directory::normalize($archive);
		
		// Log activity
		logger::log("Reading from zip '".$archive."' ...");
		
		// Check Source
		if (!file_exists($archive))
		{
			logger::log("Source file doesn't exist. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Check if $identifier has proper format
		if (!is_int($identifier) && !is_string($identifier))
		{
			logger::log("Identifier type error. Aborting...", logger::WARNING);
			return FALSE;
		}
		
		// Read from zip
		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
			logger::log("Cannot open zip '".$archive."'. Aborting...", logger::ERROR);
			return FALSE;
    		}

		if (is_int($identifier))
			$identifier = self::identify($zip, $identifier);
		
		if ($typeOfResponse == self::LOCATION)
			return "zip://".dirname($archive)."/".basename($archive)."#".$identifier;
		
		if ($typeOfResponse == self::RESOURCE)
			return $zip->getStream($identifier);
			
		$contents = self::readFile($zip, $identifier);
		$zip->close();
		
		if ($contents == FALSE)
			logger::log("Failed to read from '".$archive."'.", logger::ERROR);
		else
			logger::log("Resource successfully read from '".$archive."'.", logger::NOTICE);
		
		return $contents;
	}
	
	/**
	 * Reads the contents of an entry
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	string	$identifier
	 * 		The name of an entry inside the archive
	 * 
	 * @return	string
	 * 		The contents of a file inside the archive.
	 */
	private static function readFile($zip, $identifier)
	{
		$contents = '';
		$fp = $zip->getStream($identifier);
		if (!$fp)
			return FALSE;
			
		while (!feof($fp))
		{
			$contents .= fread($fp, 2);
		}
		
		fclose($fp);
		return $contents;
	}
	
	/**
	 * Acquires the details of an archive and some or all of the entries of the archive
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	mixed	$entry
	 * 		A name or index of an entry to acquire details for. If empty the details of the whole archive are acquired.
	 * 
	 * @param	boolean	$byName
	 * 		Request result array keys to be the entries indicies, or names (if set to TRUE)
	 * 
	 * @return	array
	 * 		An array holding the details:
	 * 		archive -> 
	 * 			length : number of entries in the archive,
	 * 			status : status of the archive,
	 * 			_status : status code of the archive,
	 * 			_systemStatus : status system code of the archive,
	 * 			file : name of the archive,
	 * 			comment : comment of the archive
	 * 		
	 * 		entries -> index ->
	 * 			crc : entry's crc code,
	 * 			lastModified : entry's last date of modification,
	 * 			compressedSize : entry's compressed size,
	 * 			compressionMethod : entry's compression method
	 */
	private static function acquireDetails($zip, $entry = "", $byName = FALSE)
	{
		$details = array();
		
		// General file details
		$details['archive']['length'] = $zip->numFiles;
		$details['archive']['status'] = $zip->getStatusString();
		$details['archive']['_status'] = $zip->status;
		$details['archive']['_systemStatus'] = $zip->statusSys;
		$details['archive']['file'] = $zip->filename;
		$details['archive']['comment'] = $zip->comment;
	
		// Details for zip contents / needed entries
		$details['entries'] = array();
		if (empty($entry))
			for ($i = 0, $j = $zip->numFiles; $i < $j; $i++)
			{
				$stat = $zip->statIndex($i);
				$idx = ($byName ? self::identify($zip, $i) : $i);
				// Fix negative crc from PHP
				if ($stat['crc'] < 0)
				    $stat['crc'] += 0x100000000;
				
				// Change keys
				$stat['lastModified'] = $stat['mtime'];
				$stat['compressedSize'] = $stat['comp_size'];
				$stat['compressionMethod'] = $stat['comp_method'];
				unset($stat['mtime']);
				unset($stat['comp_size']);
				unset($stat['comp_method']);
				$details['entries'][$idx] = $stat;
			}
		else
		{
			if (!is_string($entry) && !is_int($entry))
			{
				logger::log("Invalid entry identifier. Aborting...", logger::WARNING);
				return FALSE;
			}
			
			if (is_string($entry))
				$entry = self::locate($zip, $entry);
				
			$stat = $zip->statIndex($entry);
			$entry = ($byName ? self::identify($zip, $entry) : $entry);
			// Fix negative crc from PHP
			if ($stat['crc'] < 0)
			    $stat['crc'] += 0x100000000;
			
			// Change keys
			$stat['lastModified'] = $stat['mtime'];
			$stat['compressedSize'] = $stat['comp_size'];
			$stat['compressionMethod'] = $stat['comp_method'];
			unset($stat['mtime']);
			unset($stat['comp_size']);
			unset($stat['comp_method']);
			$details['entries'][$entry] = $stat;
		}
		
		
		return $details;
	}
	
	/**
	 * Returns the name of an entry
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	integer	$index
	 * 		The index of the entry
	 * 
	 * @return	string
	 * 		Name of an entry inside an archive
	 */
	private static function identify($zip, $index)
	{
		return $zip->getNameIndex($index);
	}
	
	/**
	 * Returns the index of an entry
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	string	$name
	 * 		The name of the entry
	 * 
	 * @return	integer
	 * 		Index of an entry inside an archive
	 */
	private static function locate($zip, $name)
	{
		return $zip->locateName($name);
	}
	
	/**
	 * Sets / Gets comments inside the archive
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	string	$comment
	 * 		The comment to set in the archive. If empty, the already set comment is acquired, instead
	 * 
	 * @param	mixed	$identifier
	 * 		The name or index of an entry to get or set a comment for
	 * 
	 * @return	mixed
	 * 		Either the comment of an entry or the status of the commenting process
	 */
	private static function comment($zip, $comment = "", $identifier = "")
	{
		if (empty($comment))
		{
			// Get comment
			if (empty($identifier))
				return $zip->getArchiveComment();
			if (is_int($identifier))
				return $zip->getCommentIndex($identifier);
			return $zip->getCommentName($identifier);
		}
		
		// Set comment
		if (empty($identifier))
			return $zip->setArchiveComment($comment);
		if (is_int($identifier))
			return $zip->setCommentIndex($identifier, $comment);
		return $zip->setCommentName($identifier, $comment);
	}
	
	/**
	 * Packs the specified files in a zip archive
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	array	$contents
	 * 		An array that holds the paths of the files/folders to include in the zip:
	 * 		['dirs'] for directories
	 * 		['files'] for files
	 * 
	 * @param	string	$localname
	 * 		This is used to adjust the inner path of a file in the zip when packing folder's contents recursively.
	 * 
	 * @param	string	$innerDirectory
	 * 		The directory where the files will be added in the zip.
	 * 
	 * @param	boolean	$includeHidden
	 * 		If set to TRUE, hidden files and folders will be included.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	private static function zipFiles($zip, $contents, $localname = "", $innerDirectory = "", $includeHidden)
	{	
		foreach ((array)$contents['dirs'] as $dir)
		{
			if (!$zip->addEmptyDir($innerDirectory.$localname.basename($dir)))
			{
				logger::log("Failed to create inner directory ".$innerDirectory.$localname.basename($dir).". Aborting...", logger::ERROR);
				$zip->unchangeAll();
				return FALSE;
			}
			
			// For this dir, pack all inner files
			$c = directory::getContentList($dir."/", $includeHidden);
			
			self::zipFiles($zip, $c, basename($dir)."/", $innerDirectory.$localname, $includeHidden);
		}
		foreach ((array)$contents['files'] as $file)
		{
			if ($zip->addFile($file, $innerDirectory.$localname.basename($file)))
				continue;
			logger::log("Failed to pack ".$innerDirectory.$localname.basename($file).". Aborting...", logger::ERROR);
			$zip->unchangeAll();
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Removes files from an archive
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	array	$contents
	 * 		List of contents to be removed, in the form of name or index
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	private static function removeFiles($zip, $contents)
	{
		foreach ($contents as $file)
		{
			if (!is_string($file) && !is_int($file))
			{
				logger::log("Invalid entry identifier. Remove Incomplete.", logger::ERROR);
				$zip->unchangeAll();
				return FALSE;
			}
			
			if (is_int($file))
				$file = self::identify($zip, $file);
			
			$zipInfo = self::acquireDetails($zip, "");
			// Closure needs PHP > 5.3.0
			$children = array_filter($zipInfo['entries'], function($var) use ($file)
			{
				return !strncmp($var['name'], $file, strlen($file));
			});
			
			foreach ((array)$children as $details)
			{
				if ($zip->deleteName($details['name']))
					continue;
				logger::log("Could not delete ".$details['name'].". Aborting...", logger::ERROR);
				$zip->unchangeAll();
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Renames files in an archive
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	array	$contents
	 * 		A list of contents to be renamed, in the form of names or indexes
	 * 
	 * @param	array	$newNames
	 * 		A list of new names for the contents
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	private static function renameFiles($zip, $contents, $newNames)
	{
		// Check Equal contents / newNames entries
		if (count($contents) != count($newNames))
		{
			logger::log("Number of contents should match with number of new names. Aborting...", logger::WARNING);
			return FALSE;
		}
	
		foreach ($contents as $key => $file)
		{
			if (!is_string($file) && !is_int($file))
			{
				logger::log("Invalid entry identifier. Rename Incomplete.", logger::ERROR);
				$zip->unchangeAll();
				return FALSE;
			}
			
			$identifier = $file;
			if (is_int($file))
			{
				$file = self::identify($zip, $file);
			}
			
			if ($file === FALSE)
			{
				logger::log("Entry with identifier '".$identifier."' not found.", logger::WARNING);
				continue;
			}
			
			$zipInfo = self::acquireDetails($zip, "");
			// Closure needs PHP > 5.3.0
			$children = array_filter($zipInfo['entries'], function($var) use ($file)
			{
				return !strncmp($var['name'], $file, strlen($file));
			});
			
			if (count($children) == 0)
			{
				logger::log("Entry with identifier '".$identifier."' not found.", logger::WARNING);
				continue;
			}
			
			foreach ((array)$children as $details)
			{
				if ($zip->renameName($details['name'], preg_replace('/'.preg_quote($file, '/').'/', $newNames[$key], $details['name'], 1)))
					continue;
				logger::log("Could not rename ".$details['name'].". Aborting...", logger::ERROR);
				$zip->unchangeAll();
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Relocates files in an archive. (NOT IMPLEMENTED YET)
	 * 
	 * @param	ZipArchive	$zip
	 * 		A ZipArchive object that handles a zip file
	 * 
	 * @param	array	$origins
	 * 		A list of origin files in the archive to be relocated.
	 * 
	 * @param	array	$destinations
	 * 		A list of destinations in the archive.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	private static function copyFiles($zip, $origins, $destinations)
	{
		// Check Equal origins / destinations entries
		if (count($origins) != count($destinations))
		{
			logger::log("Number of origin contents should match with number of destinations. Aborting...", logger::WARNING);
			return FALSE;
		}

		foreach ($origins as $key => $file)
		{
			if (!is_string($file) && !is_int($file))
			{
				logger::log("Invalid entry identifier. Copy Incomplete.", logger::ERROR);
				$zip->unchangeAll();
				return FALSE;
			}
			
			$identifier = $file;
			if (is_int($file))
			{
				$file = self::identify($zip, $file);
			}
			
			if ($file === FALSE)
			{
				logger::log("Entry with identifier '".$identifier."' not found.", logger::WARNING);
				continue;
			}
			
			$zipInfo = self::acquireDetails($zip, "");
			// Closure needs PHP > 5.3.0
			$children = array_filter($zipInfo['entries'], function($var) use ($file)
			{
				return !strncmp($var['name'], $file, strlen($file));
			});
			
			if (count($children) == 0)
			{
				logger::log("Entry with identifier '".$identifier."' not found.", logger::WARNING);
				continue;
			}
			/*
			foreach ((array)$children as $details)
				$zip->renameName($details['name'], preg_replace('/'.preg_quote($file, '/').'/', $destinations[$key], $details['name'], 1));
			*/
		}
		
		return TRUE;
	}
}
//#section_end#
?>