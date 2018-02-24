<?php
//#section#[header]
// Namespace
namespace SYS\Comm\mail;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Comm
 * @namespace	\mail
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Comm", "mail/mailgun");

use \API\Comm\mail\mailgun;

/**
 * Drovio system mailer
 * 
 * This class is the main handler for sending mail messages from the drov.io mail server.
 * 
 * @version	0.2-1
 * @created	July 7, 2014, 13:06 (EEST)
 * @updated	September 25, 2015, 18:54 (EEST)
 */
class mailer extends mailgun
{
	/**
	 * Create a new mailer instance.
	 * 
	 * @param	string	$fromName
	 * 		The from field name.
	 * 
	 * @param	string	$fromAddress
	 * 		The from address.
	 * 
	 * @return	void
	 */
	public function __construct($fromName, $fromAddress = "")
	{
		// Initialize mailgun
		parent::__construct($domain = "drov.io", $apikey = "api:key-b596b7499f9c28896ee15586ab0dbc65");
		
		// Check names
		if (empty($fromName))
			return;
		
		// Set from
		if (empty($fromAddress))
			$fromAddress = $fromName."@drov.io";
		$from = array();
		$from[$fromAddress] = $fromName;
		$this->setFrom($from);
	}
}
//#section_end#
?>