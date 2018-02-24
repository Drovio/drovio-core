<?php

// Extract the web sdk core files to the desired locations


// Initialize variables
$log = array();
$status = TRUE;

// Extract core
$r = zipManager::extract('./.temp/wsdk.zip', './.temp/_wsdkFiles', TRUE);
$status = $status && $r;
$log[] = var_export($r, true);


if ($status)
{
	// Copy the files from the temporary folder
	// to the correct location

	$r = folderManager::move('./.temp/_wsdkFiles/.wec/lib', './');
	$status = $status && $r;
	$log[] = var_export($r, true);


	$r = folderManager::move('./.temp/_wsdkFiles/.wec', './');
	$status = $status && $r;
	$log[] = var_export($r, true);

	// Delete temp files

	$r = folderManager::remove('./.temp/', '_wsdkFiles', TRUE);
	$status = $status && $r;
	$log[] = var_export($r, true);

	$r = folderManager::remove('./.temp/', 'wsdk.zip');
	$status = $status && $r;
	$log[] = var_export($r, true);

}

// Return Report
header('Content-Type: application/json');
$report = array();
$report['status'] = $status;
$report['log'] = $log;
echo json_encode($report);
return;


class zipManager
{
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
		$archive = folderManager::normalize($archive);

		// Log activity


		// Check Directories
		if (!file_exists($archive) || (!$recursive && !is_dir($destination."/")))
		{
			return FALSE;
		}

		// Create $destination folder if needed and if 'recursive'
		if ($recursive && (!is_dir($destination."/")))
		{
			folderManager::create($destination."/", "", 0777, TRUE);
		}

		$zip = new ZipArchive();
		if ($zip->open($archive) !== TRUE)
		{
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

		return $status;
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
}

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
	 * @param	string	$path
	 * 		The folder's parent path
	 *
	 * @param	string	$name
	 * 		The folder's name
	 *
	 * @param	boolean	$recursive
	 * 		Remove all inner contents of the folder recursively.
	 *
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function remove($path, $name = "", $recursive = FALSE)
	{
		// Remove Directory
		$directory = ($name = "" ? $path : $path . "/" . $name);

		if (is_dir($directory))
			$directory  .=  "/";

		// Collapse redundant slashes
		$directory = self::normalize($directory);

		if (is_dir($directory))
		{
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
			$status = rmdir($directory);
		}
		else
		{
			if (file_exists($directory))
				$status = unlink($directory);
			else
			{
				$status = FALSE;
			}
		}

		return $status;
	}


	/**
	 * Copy a folder (recursively)
	 *
	 * @param	string	$source
	 * 		The source folder path.
	 *
	 * @param	string	$destination
	 * 		The destination folder path.
	 *
	 * @param	boolean	$contents_only
	 * 		Defines whether only the contents of the folder will be copied or the folder selected also.
	 *
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function copy($source, $destination, $contents_only = FALSE)
	{
		$source = self::normalize(trim($source)."/");
		$destination = self::normalize(trim($destination)."/");

		// Copy to subfolder is not yet supported
		if (!strncmp($destination, $source, strlen($source)))
		{
			return FALSE;
		}

		// If source (or sometimes destination) are not dirs, return
		if (!is_dir($source) || ($contents_only && !is_dir($destination)))
		{
			return FALSE;
		}

		$selected = basename($source)."/";

		// Outermost Dir
		$outmostDir = ($contents_only ? $destination : $destination.$selected);
		if (!is_dir($outmostDir))
			self::create($outmostDir);

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

		$sourceCopy = $source;
		str_replace(dirname($source), "", $sourceCopy);
		foreach ($iterator as $path)
		{
			$innerPath = $path->__toString();
			$innerPath = str_replace($sourceCopy, "", $innerPath);
			if ($path->isDir())
				$status = @mkdir($outmostDir.$innerPath."/");
			else
				$status = copy($path->__toString(), $outmostDir.$innerPath);
		}

		return TRUE;
	}

	/**
	 * Move a folder (recursively).
	 *
	 * @param	string	$source
	 * 		The source folder path.
	 *
	 * @param	string	$destination
	 * 		The destination folder path.
	 *
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function move($source, $destination)
	{
		$source = self::normalize(trim($source)."/");
		$destination = self::normalize(trim($destination)."/");

		// Move to subfolder is not yet supported
		if (!strncmp($destination, $source, strlen($source)))
		{
			return FALSE;
		}

		$selected = basename($source)."/";

		$rename = !is_dir($destination);
		if ($rename)
			self::create($destination);

		self::copy($source, $destination, $contents_only = $rename);
		self::remove($source, $name = "", $recursive = TRUE);

		return TRUE;
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
}