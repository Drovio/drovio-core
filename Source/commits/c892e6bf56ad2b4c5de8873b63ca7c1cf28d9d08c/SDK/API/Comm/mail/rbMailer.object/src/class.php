<?php
//#section#[header]
// Namespace
namespace API\Comm\mail;

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
 * @namespace	\mail
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "mail::mailer");
use \SYS\Comm\mail\mailer as newMailer;

/**
 * RB Mailer
 * 
 * Configure all mail proccedures from redback.gr mail server for predifined account such as support, info, admin
 * 
 * @version	0.1-1
 * @created	April 23, 2013, 13:44 (EEST)
 * @revised	September 15, 2014, 14:54 (EEST)
 * 
 * @deprecated	Use \SYS\Comm\mail\mailer instead.
 */
class rbMailer extends newMailer {}
//#section_end#
?>