<?php
//#section#[header]
// Namespace
namespace API\Comm\phpmailer;

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
 * @namespace	\phpmailer
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * PHPMailer - PHP POP Before SMTP Authentication Class
 * 
 * Specifically for PHPMailer to allow POP before SMTP authentication.
 * This class is rfc 1939 compliant and implements all the commands required for POP3 connection, authentication and disconnection.
 * 
 * @version	{empty}
 * @created	July 7, 2014, 13:24 (EEST)
 * @revised	July 7, 2014, 14:21 (EEST)
 */
class POP3
{
	/**
	 * Default POP3 port
	 * 
	 * @type	integer
	 */
	public $POP3_PORT = 110;

	/**
	 * Default Timeout
	 * 
	 * @type	integer
	 */
	public $POP3_TIMEOUT = 30;

	/**
	 * POP3 Carriage Return + Line Feed
	 * 
	 * @type	string
	 */
	public $CRLF = "\r\n";

	/**
	 * POP3 Mail Server
	 * 
	 * @type	string
	 */
	public $host;

	/**
	 * POP3 Port
	 * 
	 * @type	integer
	 */
	public $port;

	/**
	 * POP3 Timeout Value
	 * 
	 * @type	integer
	 */
	public $tval;

	/**
	 * POP3 Username
	 * 
	 * @type	string
	 */
	public $username;

	/**
	 * POP3 Password
	 * 
	 * @type	string
	 */
	public $password;

	/**
	 * The pop3 connection object.
	 * 
	 * @type	connection
	 */
	private $pop_conn;
	
	/**
	 * Whether the class is connected to server.
	 * 
	 * @type	boolean
	 */
	private $connected;

	/**
	 * Constructor, sets the initial values
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->pop_conn  = 0;
		$this->connected = false;
	}

	/**
	 * Combination of public events - connect, login, disconnect
	 * 
	 * @param	string	$host
	 * 		The server host string.
	 * 
	 * @param	integer	$port
	 * 		The server's port number.
	 * 
	 * @param	integer	$tval
	 * 		The timeout value.
	 * 
	 * @param	string	$username
	 * 		The account's username.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @return	void
	 */
	public function Authorise($host, $port = false, $tval = false, $username, $password)
	{
		$this->host = $host;
		
		//  If no port value is passed, retrieve it
		if ($port == false)
			$this->port = $this->POP3_PORT;
		else
			$this->port = $port;
		
		//  If no port value is passed, retrieve it
		if ($tval == false)
			$this->tval = $this->POP3_TIMEOUT;
		else
			$this->tval = $tval;
		
		$this->username = $username;
		$this->password = $password;
		
		//  Connect
		$result = $this->Connect($this->host, $this->port, $this->tval);
		if ($result)
		{
			$login_result = $this->Login($this->username, $this->password);
			if ($login_result)
			{
				$this->Disconnect();
				return TRUE;
			}
		
		}
		
		//  We need to disconnect regardless if the login succeeded
		$this->Disconnect();
		
		return FALSE;
	}

	/**
	 * Connect to the POP3 server
	 * 
	 * @param	string	$host
	 * 		The server host name.
	 * 
	 * @param	string	$port
	 * 		The server's port number.
	 * 
	 * @param	integer	$tval
	 * 		The timeout value.
	 * 
	 * @return	boolean
	 * 		The connection status.
	 * 		True on success, false on failure.
	 */
	public function Connect($host, $port = false, $tval = 30)
	{
		//  Are we already connected?
		if ($this->connected)
			return true;
	
		// On Windows this will raise a PHP Warning error if the hostname doesn't exist.
		// Rather than supress it with @fsockopen, let's capture it cleanly instead
	
		//  Connect to the POP3 server
		$this->pop_conn = fsockopen($host,    //  POP3 Host
			$port,    //  Port #
			$errno,   //  Error Number
			$errstr,  //  Error Message
			$tval);   //  Timeout (seconds)
	
		//  Did we connect?
		if ($this->pop_conn == false)
			return false;
	
		//  Increase the stream time-out
	
		//  Check for PHP 4.3.0 or later
		if (version_compare(phpversion(), '5.0.0', 'ge'))
			stream_set_timeout($this->pop_conn, $tval, 0);
		else //  Does not work on Windows
			if (substr(PHP_OS, 0, 3) !== 'WIN')
				socket_set_timeout($this->pop_conn, $tval, 0);
	
		//  Get the POP3 server response
		$pop3_response = $this->getResponse();
	
		//  Check for the +OK
		if ($this->checkResponse($pop3_response))
		{
			//  The connection is established and the POP3 server is talking
			$this->connected = TRUE;
			return TRUE;
		}
	
	}

	/**
	 * Login to the POP3 server (does not support APOP yet)
	 * 
	 * @param	string	$username
	 * 		The account's username.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function Login($username = '', $password = '')
	{

		if (empty($username))
			$username = $this->username;

		if (empty($password))
			$password = $this->password;
			
		$pop_username = "USER $username" . $this->CRLF;
		$pop_password = "PASS $password" . $this->CRLF;
		
		//  Send the Username
		$this->sendString($pop_username);
		$pop3_response = $this->getResponse();
		
		if ($this->checkResponse($pop3_response))
		{
			//  Send the Password
			$this->sendString($pop_password);
			$pop3_response = $this->getResponse();
			
			if ($this->checkResponse($pop3_response))
				return TRUE;
			else
				return FALSE;
		}
		else
			return FALSE;
	}

	/**
	 * Disconnect from the POP3 server
	 * 
	 * @return	void
	 */
	public function Disconnect()
	{
		$this->sendString('QUIT');
		fclose($this->pop_conn);
	}

	/**
	 * Get the socket response back.
	 * 
	 * @param	integer	$size
	 * 		The maximum number of bytes to retrieve
	 * 
	 * @return	string
	 * 		The server's response.
	 */
	private function getResponse($size = 128)
	{
		return fgets($this->pop_conn, $size);
	}

	/**
	 * Send a string down the open socket connection to the POP3 server.
	 * 
	 * @param	string	$string
	 * 		The string to be sent to the socket.
	 * 
	 * @return	integer
	 * 		The number of bytes sent.
	 */
	private function sendString($string)
	{
		return fwrite($this->pop_conn, $string, strlen($string));
	}

	/**
	 * Checks the POP3 server response for +OK or -ERR
	 * 
	 * @param	string	$string
	 * 		The server's response.
	 * 
	 * @return	boolean
	 * 		True if response is +OK, false otherwise.
	 */
	private function checkResponse($string)
	{
		if (substr($string, 0, 3) !== '+OK')
			return FALSE;
		else
			return TRUE;
	}
}
//#section_end#
?>