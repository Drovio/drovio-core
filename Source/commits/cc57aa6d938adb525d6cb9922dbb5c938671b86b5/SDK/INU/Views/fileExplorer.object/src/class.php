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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Environment", "session");
importer::import("ESS", "Environment", "url");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "archive/zipManager");
importer::import("API", "Resources", "filesystem/directory");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Literals", "literal");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "formFactory");
importer::import("UI", "Presentation", "notification");
importer::import("UI", "Navigation", "treeView");
importer::import("UI", "Interactive", "forms/switchButton");

use \finfo;
use \ESS\Prototype\UIObjectPrototype;
use \ESS\Environment\session;
use \ESS\Environment\url;
use \API\Resources\DOMParser;
use \API\Resources\archive\zipManager;
use \API\Resources\filesystem\directory;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \API\Literals\literal;
use \UI\Html\DOM;
use \UI\Forms\formFactory;
use \UI\Presentation\notification;
use \UI\Navigation\treeView;
use \UI\Interactive\forms\switchButton;

/**
 * File Explorer
 * 
 * Use this in order to provide an environment to explore files / folders in a directory.
 * 
 * @version	2.0-2
 * @created	April 26, 2013, 16:02 (EEST)
 * @updated	February 24, 2015, 17:49 (EET)
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
	 * If set to true the fileExplorer will operate in read only mode; no creation nor deletion is allowed.
	 * 
	 * @type	boolean
	 */
	private $readOnly;
	
	/**
	 * Mapping of file types and extensions.
	 * 
	 * @type	array
	 */
	private static $fileTypes = array(
		"image" => array("jpg", "gif", "png", "ico", "bmp", "jpeg"),
		"audio" => array("mp3", "m4a", "wav", "ogg"),
		"movie" => array("mov", "mp4", "m4v", "avi", "mpeg", "mkv"),
		"code" => array("js", "php", "css", "xml", "html", "svg"),
		"archive" => array("zip", "tar", "rar", "7z"),
		"document" => array("doc", "docx", "ppt", "pptx", "xls", "xlsx", "txt", "rtf", "pdf"),
		"project" => array("ai", "ps")
	);
	
	/**
	 * Mapping of file types and dom representations.
	 * 
	 * @type	array
	 */
	private static $domTypes = array(
		"img" => array("jpg", "gif", "png", "ico", "bmp", "jpeg"),
		"pre" => array("js", "php", "css", "xml", "html", "txt", "rtf", "inc"),
		"audio" => array("mp3", "wav", "ogg"),
		"video" => array("mp4", "webm", "ogg"),
		"embed" => array(/*"m4a", "mov", "m4v", "avi", "mpeg", "mkv", */"svg"/*, "doc", "docx", "ppt", "pptx", "xls", "xlsx"*/),
		"iframe" => array("pdf")
	);
	
	/**
	 * Extensions of files that can become icons
	 * 
	 * @type	array
	 */
	private static $iconifyTypes = array("jpg", "gif", "png", "ico", "bmp", "jpeg", "svg");
	
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
	 * @param	boolean	$readOnly
	 * 		If set to true the fileExplorer operates in read only mode
	 * 
	 * @return	void
	 */
	public function __construct($rootPath, $id, $friendlyRootName = "", $showHidden = FALSE, $readOnly = TRUE)
	{
		$this->id = $id;
		$this->rootFriendlyName = $friendlyRootName;
		$this->showHidden = $showHidden;
		$this->rootPath = $rootPath."/";
		if (trim($rootPath, "/ \t\n\r\0\x0B") == "" || (!is_dir(systemRoot.$this->rootPath) && !is_dir($this->rootPath)))
		{
			$this->rootPath = NULL;
		}
		$this->readOnly = $readOnly;
	}
	
	/**
	 * Builds and returns the fileExplorer object. (Use fileExplorer::get to acquire the object's wrapper)
	 * 
	 * @param	string	$subPath
	 * 		If a subpath is supplied during build, the fileExplorer will be initialized at that path.
	 * 
	 * @return	fileExplorer
	 * 		The fileExplorer object
	 */
	public function build($subPath = "")
	{
		// Create fileExplorer wrapper
		$wrapper = DOM::create("div", "", $this->id, "fileExplorer");
		DOM::attr($wrapper, "data-path", basename($this->rootPath));
		if ($this->readOnly)
			DOM::attr($wrapper, "data-readonly", TRUE);
		
		$this->set($wrapper);
		
		if (empty($this->rootPath) || (!is_dir(systemRoot.$this->rootPath) && !is_dir($this->rootPath)))
			return $this;
		
		// Path to session
		session::set($this->id, $this->rootPath, "fileExplorer");
		session::set($this->id."_ro", $this->readOnly, "fileExplorer");
		if (!empty($this->rootFriendlyName))
			session::set($this->id."_rfn", $this->rootFriendlyName, "fileExplorer");
		
		// Get area that holds the path
		DOM::append($wrapper, $this->getPathArea($subPath));
		
		// Get area that holds the tools (for editable version)
		if (!$this->readOnly)
			DOM::append($wrapper, $this->getToolArea());
			
		// Get area that holds files and their details
		$fileAreaHolder = DOM::create("div", "", $this->id."_filev", "fileViewer");
		DOM::append($wrapper, $fileAreaHolder);
		
		// Get notifications area
		DOM::append($wrapper, $this->getNotificationsArea());
		
		$downloadFrame = DOM::create("iframe", "", "fediframe");
		DOM::attr($downloadFrame, "src", "");
		DOM::append($wrapper, $downloadFrame);
		
		if ($this->readOnly)
			return $this;
		
		// Get preferences area
		DOM::append($wrapper, $this->getPreferencesArea());
		
		// Get area that holds a "delete prevention" popup
		DOM::append($wrapper, $this->getMessageArea());
		
		// Get area that holds a folders treeview "popup" to use for move and copy
		$view = DOM::create("div", "", $this->id."_folderv", "folderView");
		DOM::append($wrapper, $view);
		
		// Upload Area
		DOM::append($wrapper, $this->getUploadArea());
		
		return $this;
	}
	
	/**
	 * Creates and returns the file viewing area
	 * 
	 * @param	string	$subPath
	 * 		The path of the directory contents to display, relative to the fileExplorer's rootPath.
	 * 
	 * @return	array
	 * 		An array that contains the contents list
	 */
	public function getDirectoryContents($subPath)
	{
		if (empty($this->rootPath))
			return array();
		return directory::getContentDetails(systemRoot."/".$this->rootPath."/".$subPath."/", TRUE);
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
	 * Returns if the fileExplorer with the given id operated in read only mode
	 * 
	 * @param	string	$rootIdentifier
	 * 		The id of the fileExplorer. Must be unique!
	 * 
	 * @return	boolean
	 * 		Returns true if the fileExplorer runs in read only mode.
	 */
	public static function isReadOnly($rootIdentifier)
	{
		return session::get($rootIdentifier."_ro", $default = TRUE, $namespace = 'fileExplorer');
	}
	
	/**
	 * Acquires a rootFriendlyName from session, associated to a rootIdentifier.
	 * 
	 * @param	string	$rootIdentifier
	 * 		Unique identifier for each fileExplorer.
	 * 
	 * @return	string
	 * 		The associated root friendly name
	 */
	public static function getRootFriendlyName($rootIdentifier)
	{
		return session::get($rootIdentifier."_rfn", $default = "", $namespace = 'fileExplorer');
	}
	
	/**
	 * Creates a folder.
	 * 
	 * @param	string	$folderName
	 * 		The name of the folder
	 * 
	 * @param	string	$subPath
	 * 		The subpath of the folder to create, relative to the fileExplorer's rootPath
	 * 
	 * @return	boolean
	 * 		The status of creation
	 */
	public function createFolder($folderName, $subPath = "")
	{
		if (empty($this->rootPath) || $this->readOnly)
			return FALSE;
		
		return folderManager::create(systemRoot.$this->rootPath."/".$subPath."/", $name = $folderName, $mode = 0777, $recursive = FALSE);
	}
	
	/**
	 * Aqcuires the size of a file.
	 * 
	 * @param	string	$name
	 * 		Name of the file
	 * 
	 * @param	string	$subPath
	 * 		Subpath to the file, relative to the fileExplorers rootPath
	 * 
	 * @return	string
	 * 		The file size, viewed in the closest unit possible (KB, MB, ...)
	 */
	public function getFileSize($name, $subPath = "")
	{
		if (empty($this->rootPath))
			return "0 B";
		return fileManager::getSize(systemRoot."/".$this->rootPath."/".$subPath."/".$name, TRUE);
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
		if (empty($this->rootPath) || $this->readOnly)
			return FALSE;
	
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
	 * 		Holds the names of the files / folders
	 * 
	 * @param	string	$subPath
	 * 		Holds the subPath of the files / folders, relative to the fileExplorer's rootPath
	 * 
	 * @return	array
	 * 		An array with the statuses of the files
	 */
	public function drop($fileNames, $subPath = "")
	{
		if (empty($this->rootPath) || $this->readOnly)
			return FALSE;
	
		$status = array();
		foreach ($fileNames as $key => $file)
		{
			$path = systemRoot.$this->rootPath."/".$subPath."/";
			if (is_dir($path.$file."/"))
				$status[] = folderManager::remove($path, $file."/", TRUE);
			else
				$status[] = fileManager::remove($path.$file);
		}
		return $status;
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
		if (empty($this->rootPath) || $this->readOnly)
			return FALSE;
	
		$status = array();
		foreach ($fileNames as $file)
		{
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
		if (empty($this->rootPath) || $this->readOnly)
			return FALSE;
	
		$status = array();
		foreach ($fileNames as $file)
		{
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
	 * Builds and returns a folder tree view according to the root path.
	 * 
	 * @param	mixed	$files
	 * 		Path to the file or array with paths to files to copy/pack in temp area
	 * 
	 * @return	DOMElement
	 * 		The folderView
	 */
	public function createTempCopy($files)
	{
		if (empty($this->rootPath) || $this->readOnly)
			return FALSE;
	
		$tempFile = sys_get_temp_dir()."/fileExplorer_".mt_rand().".temp";
		if (count($files) == 1 && !is_dir($files[0]."/"))
			fileManager::copy($files[0], $tempFile);
		else
			zipManager::create($tempFile, $files, FALSE, TRUE);
		
		return $tempFile;
	}
	
	/**
	 * To be called in case of undefined rootPath (usually undefined in session)
	 * 
	 * @return	DOMElement
	 * 		Returns DOMElement holding proper message
	 */
	public static function getInvalidRoot()
	{
		$fileViewWrapper = DOM::create("div", "", "", "fileViewerContents");
		
		$h2 = DOM::create("h2");
		$h2_msg = literal::get("sdk.INU.Views", "lbl_requestError");
		DOM::append($h2, $h2_msg);
		
		$p = DOM::create("p");
		$p_msg = literal::get("sdk.INU.Views", "msg_invalidRoot");
		DOM::append($p, $p_msg);
		
		$div = DOM::create("div");
		DOM::append($div, $h2);
		DOM::append($div, $p);
		
		$invalidTile = self::getStateTile($div, "invalid_root");
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
	public function getDOMRepresentation($fileName, $subPath = "")
	{
		// On content not found, return NULL
		$p = $this->rootPath."/".$subPath."/".$fileName;
		if (empty($this->rootPath) || !file_exists(systemRoot.$p))
			return NULL;
		
		$elem = $this->getDomType($fileName, $subPath);

		if ($elem == "pre")
		{
			// Text (text)
			$contents = fileManager::get(systemRoot.$p);
			if ($contents === FALSE)
				return NULL;
			if (empty($contents)){
				$wrap = DOM::create("pre");
				DOM::append($wrap, literal::get("sdk.INU.Views", "msg_emptyFile"));
				return $wrap;
			}
			$pre = DOM::create("pre", $contents);
			return $pre;
		}
		else if ($elem == "img")
		{
			// Image (image)
			$img = DOM::create("img");
			$url = Url::resource($p);
			DOM::attr($img, "src", $url);			
			return $img;
		}
		else if ($elem == "audio" || $elem == "video")
		{
			$av = DOM::create($elem);
			DOM::attr($av, "controls", TRUE);
			
			/*$contents = fileManager::get(systemRoot.$p);
			$fileInfo = new finfo(FILEINFO_MIME_TYPE);
			$mimeType = $fileInfo->buffer($contents);*/
			$source = DOM::create("source");
			DOM::attr($source, "src", Url::resource($p));
			//DOM::attr($source, "type", $mimeType);
			DOM::append($av, $source);
			DOM::append($av, DOM::create("span", "Your browser does not support this kind of multimedia."));
			return $av;
		}
		else if ($elem == "embed" || $elem == "iframe")
		{
			$obj = DOM::create($elem);
			
			/*$contents = fileManager::get(systemRoot.$p);
			$fileInfo = new finfo(FILEINFO_MIME_TYPE);
			$mimeType = $fileInfo->buffer($contents);*/
			DOM::attr($obj, "src", Url::resource($p));
			//DOM::attr($obj, "type", $mimeType);
			return $obj;
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
	public function getBase64Representation($contents)
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
		
		// New Folder
		$newFolder = DOM::create("div", "", "", "newFolder");
		DOM::attr($newFolder, "title", literal::get("sdk.INU.Views", "lbl_newFolder", NULL, FALSE));
		DOM::append($toolAreaWrapper, $newFolder);
		
		// Rename
		$rename = DOM::create("div", "", "", "rename disabledButton");
		DOM::attr($rename, "title", literal::dictionary("rename", FALSE));
		DOM::append($toolAreaWrapper, $rename);
		
		// Copy
		$copy = DOM::create("div", "", "", "copy disabledButton");
		DOM::attr($copy, "title", literal::dictionary("copy", FALSE));
		DOM::append($toolAreaWrapper, $copy);
		
		// Move
		$move = DOM::create("div", "", "", "move disabledButton");
		DOM::attr($move, "title", literal::dictionary("move", FALSE));
		DOM::append($toolAreaWrapper, $move);
		
		// Delete
		$delete = DOM::create("div", "", "", "delete disabledButton");
		DOM::attr($delete, "title", literal::dictionary("delete", FALSE));
		DOM::append($toolAreaWrapper, $delete);
		
		// Download
		$download = DOM::create("div", "", "", "download disabledButton");
		DOM::attr($download, "title", literal::dictionary("download", FALSE));
		DOM::append($toolAreaWrapper, $download);
		
		// Real Upload files
		$ff = new formFactory();
		$uploadFiles = $ff->getInput($type = "file", $name = "uploadFiles");
		DOM::attr($uploadFiles, "multiple", "multiple");
		DOM::attr($uploadFiles, "class", "noDisplay");
		//DOM::attr($uploadFiles, "accept", "image/*");
		DOM::append($toolAreaWrapper, $uploadFiles);
		
		// Upload Area
		$uploadsButton = DOM::create("div", "", "", "toggleUploads");
		DOM::attr($uploadsButton, "title", literal::get("sdk.INU.Views", "lbl_uploads", NULL, FALSE));
		DOM::append($toolAreaWrapper, $uploadsButton);
		
		$totalUploadProgress = DOM::create("div", "", "", "totalUploadProgress");
		DOM::append($uploadsButton, $totalUploadProgress);
		
		// Preferences
		$preferences = DOM::create("div", "", "", "prefIcon");
		DOM::append($toolAreaWrapper, $preferences);
		
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
		$preventDeleteHeader = DOM::create("h2", literal::dictionary("warning", FALSE)."!", "", "fepHeader preventDelete");
		DOM::append($viewWrapper, $preventDeleteHeader);
		
		// Main Body 
		$messageWrapper = DOM::create("div", "", "", "pdMessageWrapper");
		DOM::append($viewWrapper, $messageWrapper);
		
		$message = literal::get("sdk.INU.Views", "msg_deleteFiles");
		DOM::appendAttr($message, "class", "pdMessage");
		DOM::append($messageWrapper, $message);

		$ff = new formFactory();
		// Controls
		$controlsWrapper = DOM::create("div", "", "", "fepControlsWrapper");
		DOM::append($viewWrapper, $controlsWrapper);
		// Confirm Button
		$confirm = $ff->getButton("Confirm", "confirm");
		DOM::nodeValue($confirm, literal::dictionary("delete", FALSE));
		DOM::append($controlsWrapper, $confirm);
		// Cancel Button
		$cancel = $ff->getButton("Cancel", "cancel");
		DOM::nodeValue($cancel, literal::dictionary("cancel", FALSE));
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
		$hiddenLabel = literal::get("sdk.INU.Views", "lbl_hiddenFiles");
		DOM::append($switchWrapper, $hiddenLabel);
		$hiddenSwitch = new switchButton("fe_hiddenFilesSwitch");
		DOM::append($switchWrapper, $hiddenSwitch->build()->get());
		
		return $viewWrapper;
	}
	
	/**
	 * Creates the uploading area
	 * 
	 * @return	DOMElement
	 * 		The DOMElement representing the uploading area.
	 */
	private function getUploadArea()
	{
		$uploadArea = DOM::create("div", "", $this->id."_ua", "uploadArea");
		
		$title = DOM::create("div", literal::get("sdk.INU.Views", "lbl_uploads", NULL, FALSE), "", "uploadsTitle");
		DOM::append($uploadArea, $title); 
		
		// Fake Upload files
		$fakeUpload = DOM::create("div", "", "", "upload");
		DOM::attr($fakeUpload, "title", literal::dictionary("upload", FALSE));
		DOM::append($uploadArea, $fakeUpload);
		
		$body = DOM::create("div", "", "", "uploadsList");
		DOM::append($uploadArea, $body);
		
		return $uploadArea;
	}
	
	/**
	 * Returns the notifications that the fileExplorer may need.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement that holds the fileExplorer notifications.
	 */
	private function getNotificationsArea()
	{
		$viewWrapper = DOM::create("div", "", $this->id."_na", "notificationsArea");
		
		// ___ Upload
		// Generic file upload fail notification
		$notification = new notification();
		$uploadFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "unkFailedUpload");
		DOM::append($content, literal::get("sdk.INU.Views", "msg_uploadGenericFailure"));
		$notification->append($content);
		
		DOM::append($viewWrapper, $uploadFail);
		
		// Specific file upload fail notification
		$notification = new notification();
		$uploadFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "failedUpload");
		$msg = literal::get("sdk.INU.Views", "msg_uploadFailure", NULL, FALSE);
		$msg = str_replace("{filename}", "<span class='uploadedFile'></span>", $msg);
		$msg = str_replace("{errcode}", "<span class='uploadStatus'></span>", $msg);
		DOM::innerHTML($content, $msg);
		$notification->append($content);
		
		DOM::append($viewWrapper, $uploadFail);
		
		// Connection problem notification
		$notification = new notification();
		$uploadFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "failedConn");
		DOM::append($content, literal::get("sdk.INU.Views", "msg_uploadNoConnection"));
		$notification->append($content);
		
		DOM::append($viewWrapper, $uploadFail);
		
		// ___ Create folder
		// Create folder failure notification
		$notification = new notification();
		$folderFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "folderFail");
		$msg = literal::get("sdk.INU.Views", "msg_folderCreationFailure", NULL, FALSE);
		$msg = str_replace("{errcode}", "<span class='errorCode'></span>", $msg);
		DOM::innerHTML($content, $msg);
		$notification->append($content);
		
		DOM::append($viewWrapper, $folderFail);
		
		// ___ Rename
		// Rename failure notification
		$notification = new notification();
		$renameFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "renameFail");
		$msg = literal::get("sdk.INU.Views", "msg_renameFailure", NULL, FALSE);
		$msg = str_replace("{errcode}", "<span class='errorCode'></span>", $msg);
		DOM::innerHTML($content, $msg);
		$notification->append($content);
		
		DOM::append($viewWrapper, $renameFail);
		
		// ___ Delete
		// Delete failure notification
		$notification = new notification();
		$deleteFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "deleteFail");
		$msg = literal::get("sdk.INU.Views", "msg_deleteFailure", NULL, FALSE);
		$msg = str_replace("{errcode}", "<span class='errorCode'></span>", $msg);
		DOM::innerHTML($content, $msg);
		$notification->append($content);
		
		DOM::append($viewWrapper, $deleteFail);
		
		// Delete failure for specific files notification
		$notification = new notification();
		$deleteFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "partialDeleteFail");
		$msg = literal::get("sdk.INU.Views", "msg_deletePartialFailure", NULL, FALSE);
		$msg = str_replace("{list}", "<span class='fileList'></span>", $msg);
		DOM::innerHTML($content, $msg);
		$notification->append($content);
		
		DOM::append($viewWrapper, $deleteFail);
		
		// ___ Download
		$notification = new notification();
		$downloadSuccess = $notification->build("success")->get();
		$content = DOM::create("div", "", "", "pendingDownload");
		$span = literal::get("sdk.INU.Views", "msg_preDownload", NULL, FALSE);
		DOM::innerHTML($content, $span);
		$notification->append($content);
		
		DOM::append($viewWrapper, $downloadSuccess);
		
		// ___ Copy, Move
		$notification = new notification();
		$moveFail = $notification->build("error")->get();
		$content = DOM::create("div", "", "", "moveFail");
		$msg = literal::get("sdk.INU.Views", "msg_movePartialFailure", NULL, FALSE);
		$msg = str_replace("{errorCode}", "<span class='errorCode'></span>", $msg);
		$msg = str_replace("{list}", "<span class='fileList'></span>", $msg);
		DOM::innerHTML($content, $msg);
		$notification->append($content);
		
		DOM::append($viewWrapper, $moveFail);
		
		return $viewWrapper;
	}
	
	/**
	 * Get a tile that presents a state
	 * 
	 * @param	string	$msg
	 * 		The message that is presented in the tile.
	 * 
	 * @param	string	$state
	 * 		The state that the tile represents (empty, invalid, ...)
	 * 
	 * @return	DOMElement
	 * 		The state tile
	 */
	public function getStateTile($msg, $state)
	{
		$tile = DOM::create("div", $msg, "", "stateViewer");
		if (gettype($state) == "string")
			DOM::attr($tile, "data-state", $state);
			
		return $tile;
	}
	
	/**
	 * Identifies a file's type
	 * 
	 * @param	string	$filename
	 * 		The name of the file
	 * 
	 * @param	string	$subPath
	 * 		The subpath relative to the fileExplorer's root
	 * 
	 * @param	boolean	$extensionOnly
	 * 		If set to TRUE, the extension of the file will be returned
	 * 
	 * @return	mixed
	 * 		Returns the file type or NULL if not recognized.
	 */
	public function getFileType($filename, $subPath = "", $extensionOnly = FALSE)
	{
		if (empty($this->rootPath))
			return NULL;
	
		// Get extension
		$path = systemRoot.$this->rootPath."/".$subPath."/".$filename;
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		
		if ($extensionOnly)
			return $ext;
		
		// Check if extension is in known extension list
		foreach (self::$fileTypes as $type => $extensions)
			if (in_array($ext, $extensions))
				return $type;
		
		// Type not found, return NULL
		return NULL;
	}
	 
	/**
	 * Identifies a files dom representation
	 * 
	 * @param	string	$filename
	 * 		The subpath relative to the fileExplorer's root
	 * 
	 * @param	string	$subPath
	 * 		The subpath relative to the fileExplorer's root
	 * 
	 * @return	mixed
	 * 		Returns the appropriate representation element
	 */
	public function getDomType($filename, $subPath = "")
	{
		if (empty($this->rootPath))
			return NULL;
		
		// Get extension
		$path = systemRoot.$this->rootPath."/".$subPath."/".$filename;
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		
		// Check if extension is supported
		foreach (self::$domTypes as $type => $extensions)
			if (in_array($ext, $extensions))
				return $type;
		
		// Type not found, return NULL
		return NULL;
	}
	
	/**
	 * Check if the given extension belongs to a file type that can be iconified.
	 * 
	 * @param	string	$extension
	 * 		A given file extension.
	 * 
	 * @return	boolean
	 * 		Returns TRUE if the file with the given extension can be iconified.
	 */
	public function isIconified($extension)
	{
		if (empty($this->rootPath))
			return FALSE;
			
		return in_array($extension, self::$iconifyTypes);
	}
	
	/**
	 * Check if the given extension belongs to a generic image type.
	 * 
	 * @param	string	$extension
	 * 		A given file extension.
	 * 
	 * @return	boolean
	 * 		True if extension is image, false otherwise.
	 */
	public function isImage($extension)
	{
		if (empty($this->rootPath))
			return FALSE;
			
		return in_array($extension, self::$fileTypes['image']);
	}
}
//#section_end#
?>