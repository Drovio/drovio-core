<?php
//#section#[header]
// Namespace
namespace UI\Forms\templates;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\templates
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "user");
importer::import("API", "Resources", "literals::literal");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "templates::loginForm");

use \API\Profile\user;
use \API\Resources\literals\literal;
use \UI\Html\DOM;
use \UI\Forms\templates\loginForm;

/**
 * User Authentication Form
 * 
 * Builds a user's authentication form.
 * 
 * @version	{empty}
 * @created	June 10, 2013, 12:33 (EEST)
 * @revised	August 15, 2013, 21:59 (EEST)
 * 
 * @deprecated	This class is no longer used and its deprecated.
 */
class authForm extends loginForm
{
	/**
	 * Builds the user's authentication form with the proper data.
	 * 
	 * @param	boolean	$self
	 * 		Indicates whether the access is onauth (user authenticates self).
	 * 
	 * @return	authForm
	 * 		The authForm object.
	 */
	public function build($self = FALSE)
	{
		// Get Authentication action
		$moduleID = 66;
		$action = "auth";
		
		// Check self authentication
		$usernameValue = "";
		
		// Build login Form
		return parent::build($moduleID, $action, $usernameValue, $rememberMe = TRUE);
	}
}
//#section_end#
?>