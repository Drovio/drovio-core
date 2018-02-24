<?php
/*
 *  The prepare script cleans the website files and folders
 *  and creates all the necessary structure for website production.
 */

/*
 * -- core files
	.htaccess
	.wec/
	.wec/data/status.xml
	.wec/scripts/update.php
	.wec/scripts/status.php

-- temporary folder for uploading files for setup, update and other procedures
	.temp/
 */

// Initialize variables
$log = array();
$status = TRUE;

// Clean Existing folder structure
$exclude = array("prepare.php", ".htaccess");
$r = folderManager::clean(".", "", TRUE, $exclude);
$status = $status && $r;
$log[] = var_export($r, true);

// Create website folders

$r = folderManager::create(".", ".wec");
$status = $status && $r;
$log[] = var_export($r, true);

$r = folderManager::create("./.wec", "data");
$status = $status && $r;
$log[] = var_export($r, true);

$r = folderManager::create("./.wec", "scripts");
$status = $status && $r;
$log[] = var_export($r, true);

$r = folderManager::create(".", ".temp");
$status = $status && $r;
$log[] = var_export($r, true);

// Return Report
header('Content-Type: application/json');
$report = array();
$report['status'] = $status;
$report['log'] = $log;
echo json_encode($report);
return;



class folderManager
{
	/**
	 * Create a new folder
	 *
	 * @param    string $path
	 *        The folder parent path
	 *
	 * @param    string $name
	 *        The folder's name
	 *
	 * @param    integer $mode
	 *        The linux file mode
	 *
	 * @param    boolean $recursive
	 *        Allows the creation of nested directories specified in the pathname.
	 *
	 * @return    boolean
	 *        True on success, False on failure.
	 */
	public static function create($path, $name = "", $mode = 0777, $recursive = TRUE)
	{
		// Create Directory
		$folderPath = $path . "/" . ($name = "" ? "" : $name . "/");

		// Collapse redundant slashes
		$folderPath = self::normalize($folderPath);

		if (!is_dir($folderPath))
			$status = mkdir($folderPath, $mode, $recursive);
		else
			return TRUE;

		return $status;
	}

	/**
	 * Removes a directory
	 *
	 * @param    string $path
	 *        The folder's parent path
	 *
	 * @param    string $name
	 *        The folder's name
	 *
	 * @param    boolean $recursive
	 *        Remove all inner contents of the folder recursively.
	 *
	 * @return    boolean
	 *        True on success, False on failure.
	 */
	public static function remove($path, $name = "", $recursive = FALSE)
	{
		// Remove Directory
		$directory = ($name = "" ? $path . "/" : $path . "/" . $name . "/");

		// Collapse redundant slashes
		$directory = self::normalize($directory);

		// Remove inner contents recursively
		if ($recursive) {

			// Remove Directory
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::CHILD_FIRST);

			// Remove inner directories first
			foreach ($iterator as $p) {
				if ($p->isDir()) {
					if (!preg_match("/\/\.+$/", $p->__toString()))
						rmdir($p->__toString());
				} else
					unlink($p->__toString());
			}
		}

		if (is_dir($directory))
			$status = rmdir($directory);
		else {
			return FALSE;
		}

		return $status;
	}

	/**
	 * Empties a directory
	 *
	 * @param    string $path
	 *        The folder's parent path, or the folder's path, if the name is omitted.
	 *
	 * @param    string $name
	 *        The folder's name
	 *
	 * @param    boolean $includeHidden
	 *        Whether to include hidden files and folders.
	 *
	 * @return    boolean
	 *        True on success, False on failure.
	 */
	public static function clean($path, $name = "", $includeHidden = TRUE, $exclude = array())
	{
		// Remove Directory
		$directory = (empty($name) ? $path . "/" : $path . "/" . $name . "/");

		// Collapse redundant slashes
		$directory = self::normalize($directory);

		// Get contents
		$contents = self::getContentList($directory, $includeHidden);

		foreach ((array)$contents['dirs'] as $dir)
			if(!in_array($dir, $exclude))
				self::remove($dir, $name = "", $recursive = TRUE);

		foreach ((array)$contents['files'] as $dir)
			if(!in_array($dir, $exclude))
				unlink($dir);

		return self::isEmpty($directory);
	}

	/**
	 * Normalizes a path by collapsing redundant slashes.
	 *
	 * @param	string	$path
	 * 		The path to be normalized.
	 *
	 * @return	string
	 * 		{description}
	 */
	public static function normalize($path)
	{
		return preg_replace("/\/{2,}/", "/", $path);
	}

	/**
	 * Returns all the contents of a folder in an array.
	 * ['dirs'] for directories
	 * ['files'] for files
	 *
	 * @param	string	$directory
	 * 		The directory we are searching
	 *
	 * @param	boolean	$includeHidden
	 * 		Include hidden files (files that start with a dot) in the results
	 *
	 * @param	boolean	$includeDotFolders
	 * 		Include dot folders ('.', '..') in the results
	 *
	 * @param	boolean	$relativeNames
	 * 		Return content names, instead of paths
	 *
	 * @return	array
	 * 		{description}
	 */
	public static function getContentList($directory, $includeHidden = FALSE, $includeDotFolders = FALSE, $relativeNames = FALSE)
	{
		$directory = self::normalize($directory."/");

		// Check directory existance
		if (!is_dir($directory))
		{
			return FALSE;
		}

		$contents = array();
		$iterator = new RecursiveDirectoryIterator($directory);

		// Inner directories first
		foreach ($iterator as $path)
		{
			// Full path or relative name
			$p = ($relativeNames ? $path->getBasename() : $path->__toString());

			if ($path->isDir())
			{
				// Filter dots
				$basename = $path->getBasename();

				if (!$includeDotFolders && ($basename == "." || $basename == ".."))
					continue;

				if (!$includeHidden && (!strncmp($basename, ".", strlen("."))))
					continue;

				$contents['dirs'][] = $p;
			}
			else
			{
				// Filter hidden
				if (!$includeHidden && (!strncmp($path->getBasename(), ".", strlen("."))))
					continue;

				$contents['files'][] = $p;
			}
		}

		return $contents;
	}

	/**
	 * Checks if a directory is empty.
	 *
	 * @param	string	$path
	 * 		The path of the folder
	 *
	 * @param	string	$name
	 * 		The name of the folder
	 *
	 * @return	mixed
	 * 		Returns if the given directory is empty [TRUE] or not [FALSE]. Returns NULL if an error occurs.
	 */
	public static function isEmpty($path, $name = "")
	{
		// Remove Directory
		$directory = ($name = "" ? $path."/" : $path."/".$name."/");

		// Collapse redundant slashes
		$directory = self::normalize($directory);

		if (!is_dir($directory))
			return NULL;


		$handle = opendir($directory);
		while (FALSE !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				return FALSE;
			}
		}
		return TRUE;
	}
}
?>