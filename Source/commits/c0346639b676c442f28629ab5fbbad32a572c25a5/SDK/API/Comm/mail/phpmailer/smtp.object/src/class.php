<?php
//#section#[header]
// Namespace
namespace API\Comm\mail\phpmailer;

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
 * @namespace	\mail\phpmailer
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * PHP RFC821 SMTP client
 * 
 * Implements all the RFC 821 SMTP commands except TURN which will always return a not implemented error.
 * SMTP also provides some utility methods for sending mail to an SMTP server.
 * 
 * @version	0.1-1
 * @created	July 7, 2014, 15:04 (EEST)
 * @revised	November 10, 2014, 12:45 (EET)
 */
class SMTP
{
	/**
	 * SMTP server port
	 * 
	 * @type	integer
	 */
	public $SMTP_PORT = 25;
	
	/**
	 * SMTP reply line ending (don't change)
	 * 
	 * @type	string
	 */
	public $CRLF = "\r\n";
	
	/**
	 * Sets VERP use on/off (default is off)
	 * 
	 * @type	boolean
	 */
	public $do_verp = FALSE;
	
	/**
	 * Sets the SMTP timeout value for reads, in seconds
	 * 
	 * @type	integer
	 */
	public $Timeout = 15;
	
	/**
	 * Sets the SMTP timelimit value for reads, in seconds
	 * 
	 * @type	integer
	 */
	public $Timelimit = 30;
	
	/**
	 * The socket to the server
	 * 
	 * @type	resource
	 */
	private $smtp_conn;
	
	/**
	 * Error message, if any, for the last call
	 * 
	 * @type	string
	 */
	private $error;
	
	/**
	 * The reply the server sent to us for HELO
	 * 
	 * @type	string
	 */
	private $helo_rply;
	
	/**
	 * Initialize the class so that the data is in a known state.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->smtp_conn = 0;
		$this->error = null;
		$this->helo_rply = null;
	}
	
	/**
	 * Connect to the server specified on the port specified.
	 * If the port is not specified use the default SMTP_PORT.
	 * If tval is specified then a connection will try and be established with the server for that number of seconds.
	 * If tval is not specified the default is 30 seconds to try on the connection.
	 * 
	 * @param	string	$host
	 * 		The host of the server.
	 * 
	 * @param	integer	$port
	 * 		The server port to use.
	 * 
	 * @param	integer	$tval
	 * 		Timeout seconds to give up trying.
	 * 
	 * @return	void
	 */
	public function Connect($host, $port = 0, $tval = 30)
	{
		// set the error val to null so there is no confusion
		$this->error = null;
		
		// make sure we are __not__ connected
		if ($this->connected())
		{
			// already connected, generate error
			$this->error = array("error" => "Already connected to a server");
			return FALSE;
		}
		
		if (empty($port))
			$port = $this->SMTP_PORT;
		
		// connect to the smtp server
		$this->smtp_conn = @fsockopen($host,    // the host of the server
			$port,    // the port to use
			$errno,   // error number if any
			$errstr,  // error message if any
			$tval);   // give up after ? secs
		
		// verify we connected properly
		if (empty($this->smtp_conn))
			return FALSE;
			
		// SMTP server can take longer to respond, give longer timeout for first read
		// Windows does not have support for this timeout function
		if (substr(PHP_OS, 0, 3) != "WIN")
		{
			$max = ini_get('max_execution_time');	
			if ($max != 0 && $tval > $max) // don't bother if unlimited
				@set_time_limit($tval);
	
			stream_set_timeout($this->smtp_conn, $tval, 0);
		}
		// get any announcement
		$announce = $this->get_lines();
		return TRUE;
	}
	
	/**
	 * Initiate a TLS communication with the server.
	 * 
	 * SMTP CODE 220 Ready to start TLS
	 * SMTP CODE 501 Syntax error (no parameters allowed)
	 * SMTP CODE 454 TLS not available due to temporary reason
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function StartTLS()
	{
		$this->error = null; # to avoid confusion
		if (!$this->connected())
		{
			$this->error = array("error" => "Called StartTLS() without being connected");
			return false;
		}
		
		fputs($this->smtp_conn,"STARTTLS" . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		
		if ($code != 220)
			return false;
			
		// Begin encrypted connection
		if (!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT))
			return false;
			
		return true;
	}
	
	/**
	 * Performs SMTP authentication.
	 * Must be run after running the Hello() method.
	 * 
	 * @param	string	$username
	 * 		The account's username.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @param	string	$authtype
	 * 		The authentication type. Can be:
	 * 		'PLAIN',
	 * 		'LOGIN',
	 * 		'NTLM'.
	 * 		It is 'LOGIN' by default.
	 * 
	 * @param	string	$realm
	 * 		For NTLM type.
	 * 
	 * @param	string	$workstation
	 * 		For NTLM type.
	 * 
	 * @return	boolean
	 * 		True if successfully authenticated, false otherwise.
	 */
	public function Authenticate($username, $password, $authtype = 'LOGIN', $realm='', $workstation='')
	{
		if (empty($authtype))
			$authtype = 'LOGIN';
			
		switch ($authtype)
		{
			case 'PLAIN':
				// Start authentication
				fputs($this->smtp_conn,"AUTH PLAIN" . $this->CRLF);
				    
				$rply = $this->get_lines();
				$code = substr($rply,0,3);
				    
				if ($code != 334)
				{
					$this->error =array("error" => "AUTH not accepted from server",
						"smtp_code" => $code,
						"smtp_msg" => substr($rply,4));
					return FALSE;
				}
				
				// Send encoded username and password
				fputs($this->smtp_conn, base64_encode("\0".$username."\0".$password) . $this->CRLF);
				$rply = $this->get_lines();
				$code = substr($rply,0,3);
				    
				if ($code != 235)
				{
					$this->error = array(
						"error" => "Authentication not accepted from server",
						"smtp_code" => $code,
						"smtp_msg" => substr($rply,4));
					return FALSE;
				}
				
				break;
		case 'LOGIN':
			// Start authentication
			fputs($this->smtp_conn,"AUTH LOGIN" . $this->CRLF);
			    
			$rply = $this->get_lines();
			$code = substr($rply,0,3);
			    
			if ($code != 334)
			{
				$this->error = array(
					"error" => "AUTH not accepted from server",
					"smtp_code" => $code,
					"smtp_msg" => substr($rply,4));
				return FALSE;
			}
			    
			// Send encoded username
			fputs($this->smtp_conn, base64_encode($username) . $this->CRLF);
			    
			$rply = $this->get_lines();
			$code = substr($rply,0,3);
			    
			if ($code != 334)
			{
				$this->error = array(
					"error" => "Username not accepted from server",
					"smtp_code" => $code,
					"smtp_msg" => substr($rply,4));
				return FALSE;
			}
			    
			// Send encoded password
			fputs($this->smtp_conn, base64_encode($password) . $this->CRLF);
			    
			$rply = $this->get_lines();
			$code = substr($rply,0,3);
			    
			if ($code != 235)
			{
				$this->error = array(
					"error" => "Password not accepted from server",
					"smtp_code" => $code,
					"smtp_msg" => substr($rply,4));
				return FALSE;
			}
			break;
		case 'NTLM':
		        /*
		         * ntlm_sasl_client.php
		         ** Bundled with Permission
		         **
		         ** How to telnet in windows: http://technet.microsoft.com/en-us/library/aa995718%28EXCHG.65%29.aspx
		         ** PROTOCOL Documentation http://curl.haxx.se/rfc/ntlm.html#ntlmSmtpAuthentication
		         */
			//require_once('ntlm_sasl_client.php');
			$temp = new stdClass();
			$ntlm_client = new ntlm_sasl_client_class;
			if (! $ntlm_client->Initialize($temp))
			{
				//let's test if every function its available
				$this->error = array("error" => $temp->error);
				return FALSE;
			}
			$msg1 = $ntlm_client->TypeMsg1($realm, $workstation);//msg1
			        
			fputs($this->smtp_conn,"AUTH NTLM " . base64_encode($msg1) . $this->CRLF);
			$rply = $this->get_lines();
			$code = substr($rply,0,3);
			        
			if ($code != 334)
			{
				$this->error = array(
					"error" => "AUTH not accepted from server",
					"smtp_code" => $code,
					"smtp_msg" => substr($rply,4));
				return FALSE;
			}
			        
			$challange = substr($rply,3);//though 0 based, there is a white space after the 3 digit number....//msg2
			$challange = base64_decode($challange);
			$ntlm_res = $ntlm_client->NTLMResponse(substr($challange,24,8),$password);
			$msg3 = $ntlm_client->TypeMsg3($ntlm_res,$username,$realm,$workstation);//msg3
			// Send encoded username
			fputs($this->smtp_conn, base64_encode($msg3) . $this->CRLF);
			$rply = $this->get_lines();
			$code = substr($rply,0,3);
			if ($code != 235)
			{
				$this->error = array(
					"error" => "Could not authenticate",
					"smtp_code" => $code,
					"smtp_msg" => substr($rply,4));
				return FALSE;
			}
			break;
		}
		
		return TRUE;
	}
	
	/**
	 * Checks if class is connected to a server.
	 * 
	 * @return	boolean
	 * 		True if connected to a server, false otherwise.
	 */
	public function Connected()
	{
		if (!empty($this->smtp_conn))
		{
			$sock_status = socket_get_status($this->smtp_conn);
			if ($sock_status["eof"])
			{
				// the socket is valid but we are not connected
				$this->Close();
				return FALSE;
			}
			
			// everything looks good
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Closes the socket and cleans up the state of the class.
	 * It is not considered good to use this function without first trying to use QUIT.
	 * 
	 * @return	void
	 */
	public function Close()
	{
		// so there is no confusion
		$this->error = null;
		$this->helo_rply = null;
		
		// Check empty
		if (!empty($this->smtp_conn))
		{
			// close the connection and cleanup
			fclose($this->smtp_conn);
			$this->smtp_conn = 0;
		}
	}
	
	/**
	 * Issues a data command and sends the msg_data to the server finializing the mail transaction.
	 * $msg_data is the message that is to be send with the headers.
	 * Each header needs to be on a single line followed by a <CRLF> with the message headers and the message body being seperated by and additional <CRLF>.
	 * 
	 * Implements rfc 821: DATA <CRLF>
	 * 
	 * SMTP CODE INTERMEDIATE: 354
	 *    [data]
	 *    <CRLF>.<CRLF>
	 *    SMTP CODE SUCCESS: 250
	 *    SMTP CODE FAILURE: 552,554,451,452
	 * SMTP CODE FAILURE: 451,554
	 * SMTP CODE ERROR  : 500,501,503,421
	 * 
	 * @param	string	$msg_data
	 * 		The message content to set as data.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function Data($msg_data)
	{
		$this->error = null; // so no confusion is caused
		if (!$this->connected())
		{
			$this->error = array("error" => "Called Data() without being connected");
			return false;
		}
		fputs($this->smtp_conn,"DATA" . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		if ($code != 354)
		{
			$this->error = array("error" => "DATA command not accepted from server",
				"smtp_code" => $code,
				"smtp_msg" => substr($rply,4));
			return FALSE;
		}
		/* the server is ready to accept data!
		 * according to rfc 821 we should not send more than 1000
		 * including the CRLF
		 * characters on a single line so we will break the data up
		 * into lines by \r and/or \n then if needed we will break
		 * each of those into smaller lines to fit within the limit.
		 * in addition we will be looking for lines that start with
		 * a period '.' and append and additional period '.' to that
		 * line. NOTE: this does not count towards limit.
		 */
		// normalize the line breaks so we know the explode works
		$msg_data = str_replace("\r\n","\n",$msg_data);
		$msg_data = str_replace("\r","\n",$msg_data);
		$lines = explode("\n",$msg_data);
		
		/* we need to find a good way to determine is headers are
		 * in the msg_data or if it is a straight msg body
		 * currently I am assuming rfc 822 definitions of msg headers
		 * and if the first field of the first line (':' sperated)
		 * does not contain a space then it _should_ be a header
		 * and we can process all lines before a blank "" line as
		 * headers.
		 */
		$field = substr($lines[0],0,strpos($lines[0],":"));
		$in_headers = false;
		if (!empty($field) && !strstr($field," "))
			$in_headers = TRUE;
			
		$max_line_length = 998; // used below; set here for ease in change
		while (list(,$line) = @each($lines))
		{
			$lines_out = null;
			if ($line == "" && $in_headers)
				$in_headers = FALSE;
				
			// ok we need to break this line up into several smaller lines
			while(strlen($line) > $max_line_length)
			{
				$pos = strrpos(substr($line,0,$max_line_length)," ");
				// Patch to fix DOS attack
				if (!$pos)
				{
					$pos = $max_line_length - 1;
					$lines_out[] = substr($line,0,$pos);
					$line = substr($line,$pos);
				}
				else
				{
					$lines_out[] = substr($line,0,$pos);
					$line = substr($line,$pos + 1);
				}
				
				/* if processing headers add a LWSP-char to the front of new line
				 * rfc 822 on long msg headers
				 */
				if ($in_headers)
					$line = "\t" . $line;
			}
			$lines_out[] = $line;
			
			// send the lines to the server
			while(list(,$line_out) = @each($lines_out))
			{
				if(strlen($line_out) > 0)
					if(substr($line_out, 0, 1) == ".")
						$line_out = "." . $line_out;
				fputs($this->smtp_conn,$line_out . $this->CRLF);
			}
		}
		// message data has been sent
		fputs($this->smtp_conn, $this->CRLF . "." . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		if ($code != 250)
		{
			$this->error = array("error" => "DATA not accepted from server",
				"smtp_code" => $code,
				"smtp_msg" => substr($rply,4));
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Sends the HELO command to the smtp server.
	 * This makes sure that we and the server are in the same known state.
	 * 
	 * Implements from rfc 821: HELO <SP> <domain> <CRLF>
	 * 
	 * SMTP CODE SUCCESS: 250
	 * SMTP CODE ERROR  : 500, 501, 504, 421
	 * 
	 * @param	string	$host
	 * 		The server host.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function Hello($host = '')
	{
		// so no confusion is caused
		$this->error = null;
		
		if (!$this->connected())
		{
			$this->error = array("error" => "Called Hello() without being connected");
			return FALSE;
		}
		
		// if hostname for HELO was not specified send default
		// determine appropriate default to send to server
		if (empty($host))
			$host = "localhost";
			
		// Send extended hello first (RFC 2821)
		if (!$this->SendHello("EHLO", $host))
			if (!$this->SendHello("HELO", $host))
				return FALSE;
				
		return TRUE;
	}
	
	/**
	 * Sends a HELO/EHLO command.
	 * 
	 * @param	string	$hello
	 * 		The hello word.
	 * 
	 * @param	string	$host
	 * 		The server host address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function SendHello($hello, $host)
	{
		fputs($this->smtp_conn, $hello . " " . $host . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		if ($code != 250)
		{
			$this->error = array(
				"error" => $hello . " not accepted from server",
				"smtp_code" => $code,
				"smtp_msg" => substr($rply,4));
			return FALSE;
		}
		
		$this->helo_rply = $rply;
		return TRUE;
	}
	
	/**
	 * Starts a mail transaction from the email address specified in $from.
	 * Returns true if successful or false otherwise.
	 * If True the mail transaction is started and then one or more Recipient commands may be called followed by a Data command.
	 * 
	 * Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
	 * 
	 * SMTP CODE SUCCESS: 250
	 * SMTP CODE SUCCESS: 552,451,452
	 * SMTP CODE SUCCESS: 500,501,421
	 * 
	 * @param	string	$from
	 * 		The from mail address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function Mail($from)
	{
		// so no confusion is caused
		$this->error = null;
		if (!$this->connected())
		{
			$this->error = array("error" => "Called Mail() without being connected");
			return FALSE;
		}
		
		$useVerp = ($this->do_verp ? " XVERP" : "");
		fputs($this->smtp_conn,"MAIL FROM:<" . $from . ">" . $useVerp . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		if ($code != 250)
		{
			$this->error = array(
				"error" => "MAIL not accepted from server",
				"smtp_code" => $code,
				"smtp_msg" => substr($rply,4));
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Sends the quit command to the server and then closes the socket if there is no error or the $close_on_error argument is true.
	 * 
	 * Implements from rfc 821: QUIT <CRLF>
	 * 
	 * SMTP CODE SUCCESS: 221
	 * SMTP CODE ERROR  : 500
	 * 
	 * @param	boolean	$close_on_error
	 * 		Indicates whether to close the connection if an error occurred.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function Quit($close_on_error = TRUE)
	{
		// so there is no confusion
		$this->error = null;
		if (!$this->connected())
		{
			$this->error = array("error" => "Called Quit() without being connected");
			return FALSE;
		}
		
		// send the quit command to the server
		fputs($this->smtp_conn,"quit" . $this->CRLF);
		
		// get any good-bye messages
		$byemsg = $this->get_lines();
		$rval = true;
		$e = null;
		$code = substr($byemsg,0,3);
		if ($code != 221)
		{
			// use e as a tmp var cause Close will overwrite $this->error
			$e = array(
				"error" => "SMTP server rejected quit command",
				"smtp_code" => $code,
				"smtp_rply" => substr($byemsg,4));
			$rval = FALSE;
		}
		
		if (empty($e) || $close_on_error)
			$this->Close();
			
		return $rval;
	}
	
	/**
	 * Sends the command RCPT to the SMTP server with the TO: argument of $to.
	 * Returns true if the recipient was accepted false if it was rejected.
	 * 
	 * Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
	 * 
	 * SMTP CODE SUCCESS: 250,251
	 * SMTP CODE FAILURE: 550,551,552,553,450,451,452
	 * SMTP CODE ERROR  : 500,501,503,421
	 * 
	 * @param	string	$to
	 * 		The recipient mail address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function Recipient($to)
	{
		// so no confusion is caused
		$this->error = null;
		if (!$this->connected())
		{
			$this->error = array("error" => "Called Recipient() without being connected");
			return FALSE;
		}
		
		fputs($this->smtp_conn,"RCPT TO:<" . $to . ">" . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		if ($code != 250 && $code != 251)
		{
			$this->error = array(
				"error" => "RCPT not accepted from server",
				"smtp_code" => $code,
				"smtp_msg" => substr($rply,4));
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Sends the RSET command to abort and transaction that is currently in progress. Returns true if successful false otherwise.
	 * 
	 * Implements rfc 821: RSET <CRLF>
	 * 
	 * SMTP CODE SUCCESS: 250
	 * SMTP CODE ERROR  : 500,501,504,421
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function Reset()
	{
		// so no confusion is caused
		$this->error = null;
		if (!$this->connected())
		{
			$this->error = array("error" => "Called Reset() without being connected");
			return FALSE;
		}
		
		fputs($this->smtp_conn,"RSET" . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		if ($code != 250)
		{
			$this->error =array(
				"error" => "RSET failed",
				"smtp_code" => $code,
				"smtp_msg" => substr($rply,4));
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Starts a mail transaction from the email address specified in $from.
	 * Returns true if successful or false otherwise.
	 * If True the mail transaction is started and then one or more Recipient commands may be called followed by a Data command.
	 * This command will send the message to the users terminal if they are logged in and send them an email.
	 * 
	 * Implements rfc 821: SAML <SP> FROM:<reverse-path> <CRLF>
	 * 
	 * SMTP CODE SUCCESS: 250
	 * SMTP CODE SUCCESS: 552,451,452
	 * SMTP CODE SUCCESS: 500,501,502,421
	 * 
	 * @param	string	$from
	 * 		The from mail address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function SendAndMail($from)
	{
		// so no confusion is caused
		$this->error = null;
		if (!$this->connected())
		{
			$this->error = array("error" => "Called SendAndMail() without being connected");
			return FALSE;
		}
		fputs($this->smtp_conn,"SAML FROM:" . $from . $this->CRLF);
		$rply = $this->get_lines();
		$code = substr($rply,0,3);
		if ($code != 250)
		{
			$this->error = array(
				"error" => "SAML not accepted from server",
				"smtp_code" => $code,
				"smtp_msg" => substr($rply,4));
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Get the current error.
	 * 
	 * @return	array
	 * 		The error array.
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Read in as many lines as possible either before eof or socket timeout occurs on the operation.
	 * With SMTP we can tell if we have more lines to read if the 4th character is '-' symbol. If it is a space then we don't need to read anything else.
	 * 
	 * @return	void
	 */
	private function get_lines()
	{
		$data = "";
		$endtime = 0;
		/* If for some reason the fp is bad, don't inf loop */
		if (!is_resource($this->smtp_conn))
			return $data;
		
		stream_set_timeout($this->smtp_conn, $this->Timeout);
		if ($this->Timelimit > 0)
			$endtime = time() + $this->Timelimit;
		
		while (is_resource($this->smtp_conn) && !feof($this->smtp_conn))
		{
			$str = @fgets($this->smtp_conn,515);
			$data .= $str;
			
			// if 4th character is a space, we are done reading, break the loop
			if(substr($str,3,1) == " ")
				break;
				
			// Timed-out? Log and break
			$info = stream_get_meta_data($this->smtp_conn);
			if ($info['timed_out'])
				break;
				
			// Now check if reads took too long
			if ($endtime && $endtime < time())
				break;
					
		}
		return $data;
	}
}
//#section_end#
?>