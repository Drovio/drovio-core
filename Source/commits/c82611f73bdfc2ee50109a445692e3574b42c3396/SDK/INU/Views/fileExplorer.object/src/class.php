<?php
//#section#[header]
// Namespace
namespace INU\Views;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Views
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "archive::zipManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "url");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "form");
importer::import("UI", "Presentation", "dataGridList");
importer::import("UI", "Presentation", "notification");
importer::import("UI", "Presentation", "frames::windowFrame");
importer::import("UI", "Navigation", "treeView");
importer::import("UI", "Interactive", "forms::switchButton");

use \finfo;
use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\DOMParser;
use \API\Resources\archive\zipManager;
use \API\Resources\filesystem\directory;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \API\Resources\storage\session;
use \API\Resources\url;
use \UI\Html\DOM;
use \UI\Forms\form;
use \UI\Presentation\dataGridList;
use \UI\Presentation\notification;
use \UI\Presentation\frames\windowFrame;
use \UI\Navigation\treeView;
use \UI\Interactive\forms\switchButton;

/**
 * File Explorer
 * 
 * Use this in order to provide an environment to explore files / folders in a directory.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 16:02 (EEST)
 * @revised	May 6, 2014, 12:44 (EEST)
 */
class fileExplorer extends UIObjectPrototype
{
	/**
	 * The fileExplorer element's id
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * Path of the folder to display as root, starting from within the WWW folder
	 * 
	 * @type	string
	 */
	private $rootPath;
	
	/**
	 * The friendly name for the root folder.
	 * 
	 * @type	string
	 */
	private $rootFriendlyName;
	
	/**
	 * If set to true the fileExplorer manipulates hidden files as well. (Set allways to true at the moment)
	 * 
	 * @type	bollean
	 */
	private $showHidden;
	
	/**
	 * Constructor Method. Creates and initializes a fileExplorer.
	 * 
	 * @param	string	$rootPath
	 * 		Path of the folder to display as root. Relative to system root
	 * 
	 * @param	string	$id
	 * 		FileExplorer's id.
	 * 
	 * @param	string	$friendlyRootName
	 * 		The friendly name for the root folder.
	 * 
	 * @param	boolean	$showHidden
	 * 		If set to true the fileExplorer will handle hidden files as well. (Currently set to always true)
	 * 
	 * @return	void
	 */
	public function __construct($rootPath, $id, $friendlyRootName = "", $showHidden = FALSE)
	{
		//$this->id = (empty($id) ? "fex_".rand() : $id);
			
		$this->id = $id;
		$this->rootFriendlyName = $friendlyRootName;
		$this->showHidden = $showHidden;
		if (empty($rootPath) || trim($rootPath) == trim(systemRoot))
			$this->rootPath = "/Library/Media/";
		else
			$this->rootPath = $rootPath."/";
	}
	
	/**
	 * Builds and returns the fileExplorer object. (Use fileExplorer::get to acquire the object's wrapper)
	 * 
	 * @param	string	$subPath
	 * 		If a subpath is supplied during build, the fileExplorer will be initialized at that path.
	 * 
	 * @param	{type}	$editable
	 * 		{description}
	 * 
	 * @return	fileExplorer
	 * 		The fileExplorer object
	 */
	public function build($subPath = "", $editable = TRUE)
	{
		// Create fileExplorer wrapper
		$wrapper = DOM::create("div", "", $this->id, "fileExplorer");
		DOM::attr($wrapper, "data-path", basename($this->rootPath));
		
		$this->set($wrapper);
		
		if (!file_exists(systemRoot.$this->rootPath))
			return $this;
		
		// Path to session
		session::set($this->id, $this->rootPath, "fileExplorer");
		
		// Get area that holds the path
		DOM::append($wrapper, $this->getPathArea($subPath));
		
		// Get area that holds the tools (for editable version)
		if ($editable)
			DOM::append($wrapper, $this->getToolArea());
			
		// Get area that holds files and their details
		DOM::append($wrapper, $this->getFileArea($subPath, $editable));
		
		// Get area that holds a "delete prevention" popup
		DOM::append($wrapper, $this->getMessageArea());
		
		// Get area that holds a folders treeview "popup" to use for move and copy
		$view = DOM::create("div", "", "", "folderView");
		DOM::append($wrapper, $view);
		
		// Get preferences area
		DOM::append($wrapper, $this->getPreferencesArea());
		
		// Get notifications area
		DOM::append($wrapper, $this->getNotificationsArea());
		
		$downloadFrame = DOM::create("iframe", "", "fediframe");
		DOM::attr($downloadFrame, "src", "");
		DOM::append($wrapper, $downloadFrame);
		
		return $this;
	}
	
	/**
	 * Creates and returns the file viewing area
	 * 
	 * @param	string	$subPath
	 * 		The path of the directory contents to display, relative to the fileExplorer's rootPath.
	 * 
	 * @param	{type}	$editable
	 * 		{description}
	 * 
	 * @return	DOMElement
	 * 		The file Viewing Area
	 */
	public function getFileArea($subPath = "", $editable = TRUE)
	{
		$fileViewWrapper = DOM::create("div", "", "", "fileViewer");
		$path = $this->rootPath."/".$subPath."/";
		
		if (!file_exists(systemRoot.$path))
		{
			$stateTile = $this->getStateTile("Directory does not exist!");
			DOM::append($fileViewWrapper, $stateTile);
			return $fileViewWrapper;
		}
		
		$dtGridList = new dataGridList();
		$glist = $dtGridList->build("", $editable)->get();
		
		$spans = array();
		$spans[] = 0.6;
		$spans[] = 0.1;
		$spans[] = 0.1;
		$spans[] = 0.2;
		$dtGridList->setColumnRatios($spans);
		
		$headers = array();
		$headers[] = "Name";
		$headers[] = "Size";
		$headers[] = "Type";
		$headers[] = "Modified";
		
		$dtGridList->setHeaders($headers);
		
		$contents = directory::getContentDetails(systemRoot.$path, TRUE);
		$count = 0;
		// Dirs go first/up on the list
		foreach ((array)$contents['dirs'] as $details)
		{	
			$count++;
			$gridRow = array();
			// Name
			$wrap = DOM::create("div");
			
			$ficon = DOM::create("div", "", "", "previewWrapper folderIcon");
			$fname = DOM::create("span", $details['name'], "", "folderName");
			
			DOM::append($wrap, $ficon);
			DOM::append($wrap, $fname);
			
			$gridRow[] = $wrap;
			$gridRow[] = "";//DOM::create("span", $formatedBytes);
			// Type
			$gridRow[] = "folder";
			// Last modified
			$formatedDate = date("F j, Y, G:i (T)", $details['lastModified']);
			$gridRow[] = $formatedDate;
			
			$dtGridList->insertRow($gridRow, "files[]", FALSE);
		}
		// Files go last/down on the list
		foreach ((array)$contents['files'] as $details)
		{	
			$count++;
			$gridRow = array();
			
			$nameWrapper = DOM::create("div");
			$fileClass = "fileName";
			// Name
			$a = DOM::create("a");

			$url = "preview.php?ri=".urlencode($this->id)."&sp=".urlencode($subPath."/")."&fn=".urlencode($details['name']);
			DOM::attr($a, "href", $url);
			DOM::attr($a, "target", "_blank");
			
			$wrap = DOM::create("div", "", "", "previewWrapper");
			
			$c = fileManager::get_contents($details['path']);
			$fileInfo = new finfo(FILEINFO_MIME_TYPE);
			$mimeType = $fileInfo->buffer($c);
			if (strstr($mimeType, "svg") !== FALSE)
			{
				$embed = DOM::create("embed", "", "", "previewIcon");
				$src = url::resource($this->rootPath."/".$subPath."/".$details['name']);
				DOM::attr($embed, "src", $src);
				DOM::attr($embed, "type", $mimeType);
				DOM::append($wrap, $embed);
			}
			else if (!strncmp($mimeType, "image", strlen("image")))
			{
				$img = DOM::create("img", "", "", "previewIcon");
				
				/*$imgInfo = getimagesize(systemRoot.$this->rootPath."/".$subPath."/".$details['name']);
				if (filesize(systemRoot.$this->rootPath."/".$subPath."/".$details['name']) > 20000
					&& is_array($imgInfo) && $imgInfo[0] != 0)
				{
					$w = $imgInfo[0];
					$h = $imgInfo[1];
					$resampled = imagecreatetruecolor(48, 48);
					imagesavealpha($resampled, TRUE);
					imagealphablending($resampled, FALSE);
					$alpha = imagecolorallocatealpha($resampled, 0, 0, 0, 0);
					imagefill($resampled, 0, 0, $alpha);
					$original = imagecreatefromstring($c);
					imagecopyresampled($resampled, $original, 0, 0, 0, 0, 48, 48, $w, $h);
					ob_start();
					imagepng($resampled);
					$c = ob_get_contents();
					ob_end_clean();
					
					imagedestroy($resampled);
					$mimeType = "image/png";
				}
				
				$base64 = $this->getBase64Representation($c);
				
				DOM::attr($img, "src", $base64['src']); 
				DOM::append($wrap, $img);*/
				//DOM::appendAttr($wrap, "class", "txtfi");
				//$url = url::resource($this->rootPath."/".$subPath."/".$details['name']);
				//DOM::data($wrap, "src", array($url));
				DOM::appendAttr($wrap, "class", "picfi initialize");
				//DOM::appendAttr($wrap, "style", "background-image:url('".$url."');");
			}
			else
			{
				$extension = pathinfo($this->rootPath."/".$subPath."/".$details['name'], PATHINFO_EXTENSION);
				DOM::appendAttr($wrap, "class", $extension."fi");
				if ($extension == "zip" 
					&& ($mimeType == "application/zip" 
						|| $mimeType == "application/octet-stream"))
					$fileClass .= " zipFile";
			}
			
			$fname = DOM::create("span", $details['name'], "", $fileClass);
			DOM::attr($f, "data-file-type", $details['type']);
			//DOM::attr($f, "data-file-extension", $details['extension']);
			
			DOM::append($a, $fname);
			
			DOM::append($nameWrapper, $wrap);
			DOM::append($nameWrapper, $a);
			
			$gridRow[] = $nameWrapper;
			
			// Size
			$formatedBytes = fileManager::getSize($details['path'], TRUE);
			$gridRow[] = DOM::create("span", $formatedBytes);
			// Type
			$gridRow[] = $mimeType;
			// Last modified
			$formatedDate = date("F j, Y, G:i (T)", $details['lastModified']);
			$gridRow[] = $formatedDate;
			
			$dtGridList->insertRow($gridRow, "files[]", FALSE);
		}
		
		$dtGridListWrapper = DOM::create("div", "", "", "fileViewerWrapper");
		DOM::append($dtGridListWrapper, $glist);
		
		DOM::append($fileViewWrapper, $dtGridListWrapper);
		$emptyTile = $this->getStateTile("Empty Folder");
		DOM::attr($emptyTile, "data-state", "empty");
		DOM::append($fileViewWrapper, $emptyTile);
		if ($count == 0)
			DOM::appendAttr($dtGridListWrapper, "class", "noDisplay");
		else
			DOM::appendAttr($emptyTile, "class", "noDisplay");
		/*$loadingTile = $this->getStateTile("Loading");
		DOM::attr($loadingTile, "data-state", "loading");
		DOM::appendAttr($loadingTile, "class", "noDisplay");
		DOM::append($fileViewWrapper, $loadingTile);
		*/
		return $fileViewWrapper;
	}
	
	/**
	 * Acquires a rootPath from session, associated to a rootIdentifier.
	 * 
	 * @param	string	$rootIdentifier
	 * 		Unique identifier for each fileExplorer.
	 * 
	 * @return	string
	 * 		The associated root path
	 */
	public static function getSessionPath($rootIdentifier)
	{
		return session::get($rootIdentifier, $default = NULL, $namespace = 'fileExplorer');
	}
	
	/**
	 * Creates a folder.
	 * 
	 * @param	string	$folderName
	 * 		The path of the folder to create (including the name of the new folder), relative to the fileExplorer's rootPath
	 * 
	 * @return	boolean
	 * 		The status of creation
	 */
	public function createFolder($folderName)
	{
		//return TRUE;
		return folderManager::create(systemRoot.$this->rootPath, $name = $folderName, $mode = 0777, $recursive = FALSE);
	}
	
	/**
	 * Renames a file or folder.
	 * 
	 * @param	string	$path
	 * 		Path to the directory of the file / folder.
	 * 
	 * @param	string	$oldName
	 * 		Name of the file / folder to be renamed.
	 * 
	 * @param	string	$newName
	 * 		New name of the file / folder.
	 * 
	 * @return	boolean
	 * 		The status of rename
	 */
	public function renameFile($path, $oldName, $newName)
	{
		$old = $path."/".$oldName;
		$new = $path."/".$newName;
		if (is_dir(systemRoot.$old."/"))
			return folderManager::move(systemRoot.$old."/", systemRoot.$new."/");
		else if (file_exists(systemRoot.$old))
			return fileManager::move(systemRoot.$old, systemRoot.$new);
		else
			return FALSE;
	}
	
	/**
	 * Deletes files / folders (folders need to be empty). Returns an array with the statuses.
	 * 
	 * @param	array	$fileNames
	 * 		Holds the paths of the files / folders, relative to the fileExplorer's rootPath
	 * 
	 * @return	array
	 * 		An array with the statuses of the files
	 */
	public function drop($fileNames)
	{
		$status = array();
		foreach ($fileNames as $key => $file) {
			$path = systemRoot.$this->rootPath."/".$file;
			if (is_dir($path."/"))
			{
				if (directory::isEmpty($path."/"))
					$status[] = folderManager::remove(systemRoot.$this->rootPath, $name = $file."/");
				else
					$status[] = folderManager::remove(systemRoot.$this->rootPath, $name = $file."/", TRUE);
			}
			else
				$status[] = fileManager::remove($path);
		}
		return $status;
	}
	
	/**
	 * Moves an uploaded file to a new location
	 * 
	 * @param	string	$tmp_name
	 * 		Path of the uploaded file
	 * 
	 * @param	string	$fileName
	 * 		Path to the new file, relative to the fileExplorer's systemRoot.
	 * 
	 * @return	boolean
	 * 		The status of the move
	 */
	public function moveUpload($tmp_name, $fileName)
	{
		//return TRUE;
		
		return move_uploaded_file($tmp_name, systemRoot.$this->rootPath."/".$fileName);
		
	}
	
	/**
	 * Moves files to a new location under the root path. Returns an array with the statuses.
	 * 
	 * @param	string	$subPath
	 * 		Path from the root folder till the source containing folder
	 * 
	 * @param	array	$fileNames
	 * 		Holds the paths of the files / folders in the source folder
	 * 
	 * @param	string	$destination
	 * 		Destination folder under the root path.
	 * 
	 * @return	array
	 * 		An array with the statuses of the files
	 */
	public function moveFiles($subPath, $fileNames, $destination)
	{
		$status = array();
		foreach ($fileNames as $file) {
			$path = systemRoot.$this->rootPath."/".$subPath."/".$file;
			$dpath = systemRoot.$this->rootPath."/".$destination."/";
			if (is_dir($path."/"))
				$status[$file] = folderManager::move($path."/", $dpath);
			else
				$status[$file] = fileManager::move($path, $dpath."/".basename($path));
		}
		return $status;
	}
	
	/**
	 * Copies files to a new location under the root path. Returns an array with the statuses.
	 * 
	 * @param	string	$subPath
	 * 		Path from the root folder till the source containing folder
	 * 
	 * @param	array	$fileNames
	 * 		Holds the paths of the files / folders in the source folder
	 * 
	 * @param	string	$destination
	 * 		Destination folder under the root path.
	 * 
	 * @return	array
	 * 		An array with the statuses of the files
	 */
	public function copyFiles($subPath, $fileNames, $destination)
	{
		$status = array();
		foreach ($fileNames as $file) {
			$path = systemRoot.$this->rootPath."/".$subPath."/".$file;
			$dpath = systemRoot.$this->rootPath."/".$destination."/";
			if (is_dir($path."/"))
				$status[$file] = folderManager::copy($path."/", $dpath);
			else
				$status[$file] = fileManager::copy($path, $dpath."/".basename($path));
		}
		return $status;
	}
	
	/**
	 * Packs files in a newly created zip archive. Returns info relative to the archive, namely the path of the archive as "name", the suggested archive's name as "suggest", and the status as "info".
	 * 
	 * @param	array	$fileNames
	 * 		Holds the files / folders to be packed, as returned by API's Resources::filesystem::directory::getContentList()
	 * 
	 * @return	void
	 */
	public function packFiles($fileNames)
	{
		// Initialize info
		$info = array();
		
		// Locate the single file or Pack files in zip and locate that zip
		$tempFile = sys_get_temp_dir()."/fileExplorer_".mt_rand().".temp";
		$info['name'] = $tempFile;
		if (count($fileNames) == 1)
		{
			// Check if fileName is folder and create archive
			$file = $fileNames[0];
			if (is_dir(systemRoot.$this->rootPath."/".$file."/"))
			{
				$info['suggest'] = basename(systemRoot.$this->rootPath."/".$file."/").".zip";
				break;
			}
			
			// Copy file to temp
			$info['info'] = fileManager::copy(systemRoot.$this->rootPath."/".$file, $tempFile);
			
			// Add the rest information
			$info['suggest'] = basename($this->rootPath."/".$file);
			
			return $info;
		}
		
		$files = array();
		foreach ($fileNames as $file) {
			if (is_dir(systemRoot.$this->rootPath."/".$file."/"))
				$files['dirs'][] = systemRoot.$this->rootPath."/".$file;
			else
				$files['files'][] = systemRoot.$this->rootPath."/".$file;
		}
		
		// Zip file
		$info['info'] = zipManager::create($tempFile, $files, FALSE, TRUE);
		if (empty($info['suggest']))
			$info['suggest'] = basename($this->rootPath).".zip";
		
		// Return the zip information
		return $info;
	}
	
	/**
	 * Get a DOM representation of a file's contents.
	 * 
	 * @param	string	$fileName
	 * 		Name of the file
	 * 
	 * @param	string	$subPath
	 * 		File's path, relative to the fileExplorer's rootPath.
	 * 
	 * @return	DOMElement
	 * 		The DOM element that represents the contents of a file
	 */
	public function previewFile($fileName, $subPath = "")
	{
		try
		{
			$element = $this->getDOMRepresentation($fileName, $subPath);
			if (is_null($element))
				return DOM::create("div", "Content not found!");
			else if($element === FALSE)
				return DOM::create("div", "File type not supported!");
			else
				return $element;
		} catch (Exception $e)
		{
			return DOM::create("div", $e->getMessage());
		}
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$fileName
	 * 		{description}
	 * 
	 * @param	{type}	$subPath
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function previewFileIcon($fileName, $subPath = "")
	{
		/*try
		{
			$element = $this->getDOMRepresentationIcon($fileName, $subPath);
			if (empty($element))
				$element = DOM::create("div", "", "", "previewWrapper");
			
			return $element;
		} catch (Exception $e)
		{
			return DOM::create("div", "", "", "previewWrapper");
		}*/
	}
	
	/**
	 * To be called in case of undefined rootPath (usually undefined in session)
	 * 
	 * @return	DOMElement
	 * 		Returns DOMElement holding proper message
	 */
	public static function getInvalidRoot()
	{
		$fileViewWrapper = DOM::create("div", "", "", "fileViewer");
		$invalidTile = self::getStateTile("Root directory does not exist!");
		DOM::attr($invalidTile, "data-state", "invalid_root");
		DOM::append($fileViewWrapper, $invalidTile);
		
		return $fileViewWrapper;
	}
	
	/**
	 * Set file explorer's root path
	 * 
	 * @param	string	$rootPath
	 * 		Path to directory that will become the fileExplorer's root
	 * 
	 * @return	void
	 */
	protected function setPath($rootPath)
	{
		$this->rootPath = $rootPath."/";
	}
	
	/**
	 * Get file explorer's root path.
	 * 
	 * @return	string
	 * 		The rootPath
	 */
	protected function getPath()
	{
		return $this->rootPath;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$fileName
	 * 		{description}
	 * 
	 * @param	{type}	$subPath
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function iconifyFile($fileName, $subPath = "")
	{ 
		$wrap = DOM::create("div", "", "", "previewWrapper");
		
		$contents = fileManager::get_contents(systemRoot.$this->rootPath."/".$subPath."/".$fileName);
		
		// On content not found, return NULL
		if ($contents === FALSE) {
			DOM::appendAttr($wrap, "class", "brokenfi");
			return $wrap;
		}
		
		$fileInfo = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fileInfo->buffer($contents);	
		
		if (strstr($mimeType, "svg") !== FALSE)
		{
			$embed = DOM::create("embed", "", "", "previewIcon");			
			DOM::attr($embed, "src", $this->rootPath."/".$subPath."/".$fileName);
			DOM::attr($embed, "type", $mimeType);
			DOM::append($wrap, $embed);
		}
		else if (!strncmp($mimeType, "image", strlen("image")))
		{
			$img = DOM::create("img", "", "", "previewIcon");
				
			$imgInfo = getimagesize(systemRoot.$this->rootPath."/".$subPath."/".$fileName);
			if (filesize(systemRoot.$this->rootPath."/".$subPath."/".$fileName) > 20000
				&& is_array($imgInfo) && $imgInfo[0] != 0)
			{
				$w = $imgInfo[0];
				$h = $imgInfo[1];
				$resampled = imagecreatetruecolor(48, 48);
				imagesavealpha($resampled, TRUE);
				imagealphablending($resampled, FALSE);
				$alpha = imagecolorallocatealpha($resampled, 0, 0, 0, 0);
				imagefill($resampled, 0, 0, $alpha);
				$original = imagecreatefromstring($contents);
				imagecopyresampled($resampled, $original, 0, 0, 0, 0, 48, 48, $w, $h);
				ob_start();
				imagepng($resampled);
				$contents = ob_get_contents();
				ob_end_clean();
				
				imagedestroy($resampled);
				$mimeType = "image/png";
			}
			
			$base64 = $this->getBase64Representation($contents);
			
			DOM::attr($img, "src", $base64['src']); 
			DOM::append($wrap, $img);
		}
		else
		{ 
			$extension = pathinfo($this->rootPath."/".$subPath."/".$fileName, PATHINFO_EXTENSION);
			DOM::appendAttr($wrap, "class", $extension."fi");
			if ($extension == "zip" 
				&& ($mimeType == "application/zip" 
					|| $mimeType == "application/octet-stream"))
				$fileClass .= " zipFile";
		}
		
		return $wrap;
		/*try
		{
			$element = $this->getDOMRepresentation($fileName, $subPath);
			if (is_null($element))
				return DOM::create("div", "Content not found!");
			else if($element === FALSE)
				return DOM::create("div", "File type not supported!");
			else
				return $element;
		} catch (Exception $e)
		{
			return DOM::create("div", $e->getMessage());
		}*/
	}

	/**
	 * Get a DOM representation of a file's contents. Returns DOMElement on success, NULL if the file is missing, FALSE if the file type is not supported.
	 * 
	 * @param	string	$fileName
	 * 		Name of the file.
	 * 
	 * @param	string	$subPath
	 * 		Path where the file exists, relative to the fileExplorer's rootPath
	 * 
	 * @return	mixed
	 * 		Returns a DOM elements representing a file's contents on success, NULL if the file is missing, or FALSE if the file type is not supported.
	 */
	private function getDOMRepresentation($fileName, $subPath)
	{
		/*
		REQUEST_AUTO = 0;
		REQUEST_TEXT = 1;
		REQUEST_IMAGE = 2;
		REQUEST_MEDIA = 3;
		REQUEST_DOWNLOAD = 4;
		*/
		$contents = fileManager::get(systemRoot.$this->rootPath."/".$subPath."/".$fileName);
		
		// On content not found, return NULL
		if ($contents === FALSE)
			return NULL;
		
		$fileInfo = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fileInfo->buffer($contents);
		
		// Starts with : (!strncmp($haystack, $needle, strlen($needle)))
		if (!strncmp($mimeType, "text", strlen("text")) || substr($mimeType, -strlen("/xml")) === "/xml")
		{
			// Text (text)
			$pre = DOM::create("pre", $contents);
			return $pre;
		}
		else if (strstr($mimeType, "svg") !== FALSE || !strncmp($mimeType, "video", strlen("video")) || !strncmp($mimeType, "audio", strlen("audio")))
		{
			// Video / Audio (video/audio)
			$embed = DOM::create("embed");
			$url = Url::resource($url = $this->rootPath."/".$subPath."/".$fileName);
			DOM::attr($embed, "src", $url);
			DOM::attr($embed, "type", $mimeType);
			return $embed;
		} 
		else if (!strncmp($mimeType, "image", strlen("image")))
		{
			// Image (image)
			$img = DOM::create("img");
			$url = Url::resource($url = $this->rootPath."/".$subPath."/".$fileName);
			DOM::attr($img, "src", $url);			
			return $img;
		}
		else
		{
			if (empty($contents))
				return DOM::create("div", "File is empty!");
			
			// Check extension?
			// Download
			/*$ifr = DOM::create("iframe", "", "fediframe", "noDisplay");
			DOM::attr($ifr, "src", "/ajax/resources/sdk/fileExplorer/downloadFiles.php?subPath=".urlencode($subPath)."&fexId=".urlencode($this->id)."&".urlencode("fNames[]")."=".$fileName);
			return $ifr;*/
		}
		
		// On type not supported, return FALSE
		return FALSE;
	}
	
	/**
	 * Encodes a file's contents in base64 format and returns an array with the appropriate mime-type and source string.
	 * 
	 * @param	string	$contents
	 * 		The contents of the file.
	 * 
	 * @return	array
	 * 		An array with the appropriate mime-type and source string.
	 */
	private function getBase64Representation($contents)
	{	
		$fileInfo = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fileInfo->buffer($contents);
		
		$base64contents = base64_encode($contents);
		
		$response = array();
		$response['type'] = $mimeType;
		$response['src'] = "data:".$mimeType.";base64,".$base64contents;
		
		return $response;
	}
	
	/**
	 * Build and get tools area (upload, add, delete, etc..)
	 * 
	 * @return	DOMElement
	 * 		The tools area
	 */
	private function getToolArea()
	{
		$toolAreaWrapper = DOM::create("div", "", "", "toolbar");
	
		// Add files, Upload files, Cancel Upload, Delete files, View remote (and local) files
	
		// Fake Upload files
		$fakeUpload = form::button("button", "upload", "", "upload");
		DOM::nodeValue($fakeUpload, "Upload");
		DOM::append($toolAreaWrapper, $fakeUpload);
		
		// New Folder
		$newFolder = form::button("button", "newFolder", "", "newFolder");
		DOM::nodeValue($newFolder, "New Folder");
		DOM::append($toolAreaWrapper, $newFolder);
		
		// Delete
		$delete = form::button("button", "delete", "", "delete", TRUE);
		DOM::nodeValue($delete, "Delete");
		DOM::append($toolAreaWrapper, $delete);
		
		// Download
		$download = form::button("button", "download", "", "download", TRUE);
		DOM::nodeValue($download, "Download");
		DOM::append($toolAreaWrapper, $download);
		
		// Move
		$move = form::button("button", "move", "", "move", TRUE);
		DOM::nodeValue($move, "Move");
		DOM::append($toolAreaWrapper, $move);
		
		// Copy
		$copy = form::button("button", "copy", "", "copy", TRUE);
		DOM::nodeValue($copy, "Copy");
		DOM::append($toolAreaWrapper, $copy);
		
		// Rename
		$rename = form::button("button", "rename", "", "rename", TRUE);
		DOM::nodeValue($rename, "Rename");
		DOM::append($toolAreaWrapper, $rename);
		
		// Real Upload files
		$uploadFiles = form::input("file", "uploadFiles", "", "");
		DOM::attr($uploadFiles, "multiple", "multiple");
		DOM::attr($uploadFiles, "class", "noDisplay");
		//DOM::attr($uploadFiles, "accept", "image/*");
		DOM::append($toolAreaWrapper, $uploadFiles);
		
		// Preferences
		$preferences = DOM::create("div", "", "", "prefIcon");
		DOM::append($toolAreaWrapper, $preferences);
		
		// Views
		$views = DOM::create("div", "", "", "viewIcon gridIcon");
		DOM::append($toolAreaWrapper, $views);
		
		return $toolAreaWrapper;
	}
	
	/**
	 * Build and get path area.
	 * 
	 * @param	string	$subPath
	 * 		If a subPath is supplied the path area will be initialized at that path
	 * 
	 * @return	DOMElement
	 * 		The path area
	 */
	private function getPathArea($subPath = "")
	{
		$pathWrapper = DOM::create("div", "", "", "pathbar");
		
		// Set $this->rootPath element as root
		$rootName = (empty($this->rootFriendlyName) ? basename($this->rootPath) : $this->rootFriendlyName);
		$element = DOM::create("span", $rootName, "", "pathElement");
		//DOM::attr("data-path", basename($this->rootPath));
		DOM::append($pathWrapper, $element);
		
		$normalizedPath = trim(directory::normalize($subPath), " \t\n\r\0\x0B/");
		if ($normalizedPath == "")
			return $pathWrapper;
		
		$pathParts = explode("/", $normalizedPath);
		foreach ($pathParts as $sub)
		{
			$rel = DOM::create("span", ">", "", "pathRelation", TRUE);
			DOM::append($pathWrapper, $rel);
			$pathElem = DOM::create("span", $sub, "", "pathElement", TRUE);
			DOM::append($pathWrapper, $pathElem);
		}
		
		return $pathWrapper;
	}
	
	/**
	 * Builds and returns a folder tree view according to the root path.
	 * 
	 * @param	string	$subPath
	 * 		If a subpath is supplied, the folder treeview will have that path opened on initialization
	 * 
	 * @return	DOMElement
	 * 		The folderView
	 */
	public function getFolderTreeview($subPath = "")
	{
		$view = DOM::create("div", "", "", "folderView");
		$viewWrapper = DOM::create("div", "", "", "folderViewWrapper");
		DOM::append($view, $viewWrapper);
		
		// Header
		$moveTo = DOM::create("h2", "Move files to...", "", "fepHeader move");
		DOM::append($viewWrapper, $moveTo);
		$copyTo = DOM::create("h2", "Copy files to...", "", "fepHeader copy");
		DOM::append($viewWrapper, $copyTo);
		
		// Main Body (Folder view)
		$folderTreeWrapper = DOM::create("div", "", "", "fepFolderTreeWrapper");
		DOM::append($viewWrapper, $folderTreeWrapper);
		
		// Folder treeview
		$treeView = new treeView();
		$folderTree = $treeView->build($id = "folderTree", $class = "fepFolderViewer", $sorting = TRUE)->get();
		DOM::append($folderTreeWrapper, $folderTree);
		
		$curSubPath = trim(directory::normalize($subPath), " \t\n\r\0\x0B/");
		
		// Scan folders
		$name = (empty($this->rootFriendlyName) ? basename($this->rootPath) : $this->rootFriendlyName);
		$id = "feftv_".basename($this->rootPath);
		$rootParent = $treeView->insertSemiExpandableTreeItem($id, DOM::create("div", $name), $parentId = "", TRUE);
		$treeView->assignSortValue($rootParent, $name);
		DOM::attr($rootParent, "subPath", "");
		$this->buildFolderTree($treeView, $id, systemRoot.$this->rootPath, $curSubPath);

		// Controls
		$controlsWrapper = DOM::create("div", "", "", "fepControlsWrapper");
		DOM::append($viewWrapper, $controlsWrapper);		
		// Confirm Button
		$confirm = form::button("button", "confirm", "", "confirm", TRUE);
		DOM::nodeValue($confirm, "Confirm");
		DOM::append($controlsWrapper, $confirm);
		// Cancel Button
		$cancel = form::button("button", "cancel", "", "cancel");
		DOM::nodeValue($cancel, "Cancel");
		DOM::append($controlsWrapper, $cancel);
		
		return $view;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$subPath
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getFileDetails($subPath, $name = "")
	{
		/*$info = array();
		$file = systemRoot.$this->rootPath."/".$subPath."/".$name;
		$file = trim(directory::normalize($file), " /");
		$info['debug'] = file_exists($file);
		if (!file_exists($file))
			return $info;
		$info['debug'] = $file;
		$info['name'] = basename($file);
		$info['type'] = filetype($file);
		$info['size'] = fileManager::getSize($file, TRUE);
		$info['modified'] = filemtime($file);
		
		return $info;*/
	}
	
	/**
	 * Populates the folder tree
	 * 
	 * @param	treeView	$treeView
	 * 		The treeView to populate
	 * 
	 * @param	DOMElement	$parent
	 * 		The parent element in the treeView to append an element to.
	 * 
	 * @param	string	$path
	 * 		The path under which all folders will be accounted in the folder tree.
	 * 
	 * @param	string	$curSubPath
	 * 		If a subpath is supplied, the folder view will have that path's tree itam's opened.
	 * 
	 * @return	void
	 */
	private function buildFolderTree($treeView, $parent, $path, $curSubPath)
	{
		$contents = directory::getContentList($path, TRUE);
		if (empty($contents) || (!isset($contents['dirs'])))
			return;
		$contents = $contents['dirs'];
		$normalizedRoot = directory::normalize(systemRoot.$this->rootPath);
		foreach ((array)$contents as $dir)
		{
			$normalizedDir = directory::normalize($dir);
			$subPath = str_replace($normalizedRoot, "", $normalizedDir);
			$id = "feftv_".str_replace("/", "_", $normalizedDir); 
			$name = basename($dir);
			$open = (strpos($curSubPath."/", trim($subPath, " \t\n\r\0\x0B/")."/") === 0 ? TRUE : FALSE);
			$newParent = $treeView->insertSemiExpandableTreeItem($id, DOM::create("div", $name), $parent, $open);
			$treeView->assignSortValue($newParent, $name);
			DOM::attr($newParent, "subPath", trim($subPath, " /"));
			
			$this->buildFolderTree($treeView, $id, $normalizedDir, $curSubPath);
		}
	}
	
	/**
	 * Creates and returns a message area for the file explorer with some of its messages. Something like a message pool.
	 * 
	 * @return	DOMelement
	 * 		The message area
	 */
	private function getMessageArea()
	{
		$view = DOM::create("div", "", "", "messageArea");
		$viewWrapper = DOM::create("div", "", "", "preventDeleteWrapper");
		DOM::append($view, $viewWrapper);
		
		// Header
		$preventDeleteHeader = DOM::create("h2", "Warning!", "", "fepHeader preventDelete");
		DOM::append($viewWrapper, $preventDeleteHeader);
		
		// Main Body 
		$messageWrapper = DOM::create("div", "", "", "pdMessageWrapper");
		DOM::append($viewWrapper, $messageWrapper);
		
		$message = DOM::create("span", "You are about to permanently delete the selected files, without being able to restore them! Make sure you keep a backup of important files. Proceed?", "", "pdMessage");
		DOM::append($messageWrapper, $message);

		// Controls
		$controlsWrapper = DOM::create("div", "", "", "fepControlsWrapper");
		DOM::append($viewWrapper, $controlsWrapper);
		// Confirm Button
		$confirm = form::button("button", "confirm", "", "confirm");
		DOM::nodeValue($confirm, "Confirm");
		DOM::append($controlsWrapper, $confirm);
		// Cancel Button
		$cancel = form::button("button", "cancel", "", "cancel");
		DOM::nodeValue($cancel, "Cancel");
		DOM::append($controlsWrapper, $cancel);
		
		return $view;
	}
	
	/**
	 * Returns the preferences area wrapper.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement that holds the fileExplorer preferences
	 */
	private function getPreferencesArea()
	{
		$viewWrapper = DOM::create("div", "", "", "preferencesArea");
		
		$switchWrapper = DOM::create("div", "", "", "switchWrapper");
		DOM::append($viewWrapper, $switchWrapper);
		$hiddenLabel = DOM::create("span", "Hidden Files:");
		DOM::append($switchWrapper, $hiddenLabel);
		$hiddenSwitch = new switchButton("fe_hiddenFilesSwitch");
		DOM::append($switchWrapper, $hiddenSwitch->build()->get());
		
		return $viewWrapper;
	}
	
	/**
	 * Returns the notifications that the fileExplorer may need.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement that holds the fileExplorer notifications.
	 */
	private function getNotificationsArea()
	{
		$viewWrapper = DOM::create("div", "", "", "notificationsArea");
		
		// ___ Upload
		// Generic file upload fail notification
		$notification = new notification();
		$uploadFail = $notification->build("error")->get();
		$content = DOM::create("div", "There was an error with the upload! The file was not uploaded!", "", "unkFailedUpload");
		$notification->append($content);
		
		DOM::append($viewWrapper, $uploadFail);
		
		// Specific file upload fail notification
		$notification = new notification();
		$uploadFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "failedUpload");
		$span1 = DOM::create("span", "File ");
		$span2 = DOM::create("span", "", "", "uploadedFile");
		$span3 = DOM::create("span", " could not be uploaded! Error code: ");
		$span4 = DOM::create("span", "", "", "uploadStatus");
		DOM::append($content, $span1);
		DOM::append($content, $span2);
		DOM::append($content, $span3);
		DOM::append($content, $span4);
		$notification->append($content);
		
		DOM::append($viewWrapper, $uploadFail);
		
		// Connection problem notification
		$notification = new notification();
		$uploadFail = $notification->build("error")->get();
		$content = DOM::create("div", "There was an error with the connection! Ongoing uploads were canceled!", "", "failedConn");
		$notification->append($content);
		
		DOM::append($viewWrapper, $uploadFail);
		
		// ___ Create folder
		// Create folder failure notification
		$notification = new notification();
		$folderFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "folderFail");
		$span1 = DOM::create("span", "The folder could not be created! ");
		$span2 = DOM::create("span", "", "", "errorCode");
		DOM::append($content, $span1);
		DOM::append($content, $span2);
		$notification->append($content);
		
		DOM::append($viewWrapper, $folderFail);
		
		// ___ Rename
		// Rename failure notification
		$notification = new notification();
		$renameFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "renameFail");
		$span1 = DOM::create("span", "The folder or file could not be renamed! ");
		$span2 = DOM::create("span", "", "", "errorCode");
		DOM::append($content, $span1);
		DOM::append($content, $span2);
		$notification->append($content);
		
		DOM::append($viewWrapper, $renameFail);
		
		// ___ Delete
		// Delete failure notification
		$notification = new notification();
		$deleteFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "deleteFail");
		$span1 = DOM::create("span", "The folders or files could not be deleted! ");
		$span2 = DOM::create("span", "", "", "errorCode");
		DOM::append($content, $span1);
		DOM::append($content, $span2);
		$notification->append($content);
		
		DOM::append($viewWrapper, $deleteFail);
		
		// Delete failure for specific files notification
		$notification = new notification();
		$deleteFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "partialDeleteFail");
		$span1 = DOM::create("span", "The following folders or files could not be deleted: ");
		$span2 = DOM::create("span", "", "", "fileList");
		DOM::append($content, $span1);
		DOM::append($content, $span2);
		$notification->append($content);
		
		DOM::append($viewWrapper, $deleteFail);
		
		// ___ Download
		$notification = new notification();
		$downloadSuccess = $notification->build("success")->get();
		$content = DOM::create("div", "", "", "pendingDownload");
		$span1 = DOM::create("span", "The requested files and folders are being packed for download. ");
		$span2 = DOM::create("span", "If the download does not start in a few moments, please refresh your page and try again.");
		DOM::append($content, $span1);
		DOM::append($content, $span2);
		$notification->append($content);
		
		DOM::append($viewWrapper, $downloadSuccess);
		
		// ___ Copy, Move
		$notification = new notification();
		$moveFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "moveFail");
		$span1 = DOM::create("span", "Some of the files or folders could not be relocated! ");
		$span2 = DOM::create("span", "", "", "errorCode");
		$span3 = DOM::create("span", "", "", "fileList");
		DOM::append($content, $span1);
		DOM::append($content, $span2);
		DOM::append($content, $span3);
		$notification->append($content);
		
		DOM::append($viewWrapper, $moveFail);
		
		return $viewWrapper;
	}
	
	/**
	 * Get a tile that presents a state
	 * 
	 * @param	mixed	$span
	 * 		Contents of the tile.
	 * 
	 * @return	DOMElement
	 * 		The state tile
	 */
	private function getStateTile($span)
	{
		return DOM::create("div", $span, "", "stateViewer");
	}
	
}
//#section_end#
?>