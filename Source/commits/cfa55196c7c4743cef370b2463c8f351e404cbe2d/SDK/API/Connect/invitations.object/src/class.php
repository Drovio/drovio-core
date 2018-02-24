<?php
//#section#[header]
// Namespace
namespace API\Connect;

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
 * @package	Connect
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "person");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\person;

/**
 * Platform Invitation Manager
 * 
 * Manages team and project invitations for non-registered users.
 * 
 * @version	0.1-2
 * @created	June 3, 2015, 23:00 (EEST)
 * @updated	June 6, 2015, 19:48 (EEST)
 */
class invitations
{
	/**
	 * The team invitation type value.
	 * 
	 * @type	string
	 */
	const TEAM_TYPE = 1;
	
	/**
	 * The project invitation type value.
	 * 
	 * @type	string
	 */
	const PROJECT_TYPE = 2;
	
	/**
	 * Create a new invitation for a given email for a non-registered user.
	 * 
	 * @param	string	$email
	 * 		The person's mail to invite.
	 * 
	 * @param	string	$context
	 * 		The team id or the project id.
	 * 
	 * @param	string	$type
	 * 		The context type.
	 * 		It should be either for team or for project.
	 * 		Use the class constants.
	 * 
	 * @return	mixed
	 * 		The invitation token if success, false otherwise.
	 */
	public static function create($email, $context, $type = self::TEAM_TYPE)
	{
		// Create an invitation database record
		$dbc = new dbConnection();
		$q = new dbQuery("34026273008937", "connection.invitations");
		
		// Create token
		$time = time();
		$token = self::token($email."_".$type."_".$time, $context);
		
		// Validate type
		$type = ($type != self::TEAM_TYPE ? self::PROJECT_TYPE : $type);
		
		$attr = array();
		$attr['email'] = $email;
		$attr['type'] = $type;
		$attr['context'] = $context;
		$attr['token'] = $token;
		$attr['time'] = $time;
		$status = $dbc->execute($q, $attr);
		if ($status)
			return $token;
		
		return FALSE;
	}
	
	/**
	 * Get all invitations for a given email of a person.
	 * 
	 * @param	string	$email
	 * 		The person's email to  get the invitations for.
	 * 		If empty, get the current person's mail.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		All person invitations.
	 */
	public static function getAccountInvitations($email = "")
	{
		// Get invitations for a team/project
		$dbc = new dbConnection();
		$q = new dbQuery("19477803813566", "connection.invitations");
		
		// Get proper email
		$email = (empty($email) ? person::getMail() : "");
		
		// Set attributes and execute
		$attr = array();
		$attr['email'] = $email;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get invitations based on the given context and type.
	 * 
	 * @param	string	$context
	 * 		The team id or the project id.
	 * 
	 * @param	string	$type
	 * 		The context type.
	 * 		It should be either for team or for project.
	 * 		Use the class constants.
	 * 
	 * @return	array
	 * 		An array of all invitations mathing the given parameters.
	 */
	public static function getInvitations($context, $type = self::TEAM_TYPE)
	{
		// Get invitations for a team/project
		$dbc = new dbConnection();
		$q = new dbQuery("15723458063635", "connection.invitations");
		
		// Validate type
		$type = ($type != self::TEAM_TYPE ? self::PROJECT_TYPE : $type);
		
		// Set attributes and execute
		$attr = array();
		$attr['type'] = $type;
		$attr['context'] = $context;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Remove an invitation from the system.
	 * 
	 * @param	string	$email
	 * 		The invitation email.
	 * 
	 * @param	string	$context
	 * 		The team id or the project id.
	 * 
	 * @param	string	$type
	 * 		The context type.
	 * 		It should be either for team or for project.
	 * 		Use the class constants.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($email, $context, $type = self::TEAM_TYPE)
	{
		// Delete an invitation database record
		$dbc = new dbConnection();
		$q = new dbQuery("14798578646544", "connection.invitations");
		
		// Validate type
		$type = ($type != self::TEAM_TYPE ? self::PROJECT_TYPE : $type);
		
		$attr = array();
		$attr['email'] = $email;
		$attr['type'] = $type;
		$attr['context'] = $context;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Generate an one-time token for the invitation.
	 * 
	 * @param	string	$prefix
	 * 		The key prefix.
	 * 
	 * @param	string	$value
	 * 		The key main context/value.
	 * 
	 * @return	string
	 * 		The token generated.
	 */
	private static function token($prefix, $value)
	{
		return md5("invitation_".$prefix."_".$value);
	}
}
//#section_end#
?>