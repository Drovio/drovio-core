<?php
//#section#[header]
// Namespace
namespace AEL\Resources\account;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Resources
 * @namespace	\account
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("AEL", "Resources", "DOMParser");

use \AEL\Resources\DOMParser as AELDOMParser;

/**
 * DOMParser for Applications
 * 
 * Class for parsing xml files in the account's application folder.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder or the application shared folder root.
 * The shared folder is one for all applications, so be careful of what you are storing there.
 * 
 * @version	3.0-1
 * @created	December 5, 2014, 16:55 (EET)
 * @updated	January 14, 2015, 9:48 (EET)
 * 
 * @deprecated	Use \AEL\Resources\DOMParser instead.
 */
class DOMParser extends AELDOMParser
{
	/**
	 * Create a new instance of a DOMParser
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, the DOMParser will have access to the shared application data folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($shared = FALSE)
	{
		parent::__construct(parent::ACCOUNT_MODE, $shared);
	}
}
//#section_end#
?>