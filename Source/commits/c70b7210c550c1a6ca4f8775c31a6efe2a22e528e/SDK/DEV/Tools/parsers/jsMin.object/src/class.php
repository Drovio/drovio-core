<?php
//#section#[header]
// Namespace
namespace DEV\Tools\parsers;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Tools
 * @namespace	\parsers
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * jsMin for minifying a javascript document.
 * 
 * This class includes the right functions for minifying a given javascript document.
 * 
 * @version	0.1-2
 * @created	September 24, 2014, 14:29 (EEST)
 * @revised	September 24, 2014, 14:33 (EEST)
 */
class jsMin
{
	/**
	 * The line feed ord number.
	 * 
	 * @type	integer
	 */
	const ORD_LF            = 10;
	/**
	 * The space ord number.
	 * 
	 * @type	integer
	 */
	const ORD_SPACE         = 32;
	/**
	 * The 'keep a' action code.
	 * 
	 * @type	integer
	 */
	const ACTION_KEEP_A     = 1;
	/**
	 * The 'delete a' action code.
	 * 
	 * @type	integer
	 */
	const ACTION_DELETE_A   = 2;
	/**
	 * The 'delete a and b' action code.
	 * 
	 * @type	integer
	 */
	const ACTION_DELETE_A_B = 3;

	/**
	 * The first character to check.
	 * 
	 * @type	string
	 */
	protected $a           = '';
	/**
	 * The second character to check.
	 * 
	 * @type	string
	 */
	protected $b           = '';
	/**
	 * The input javascript document to minify.
	 * 
	 * @type	string
	 */
	protected $input       = '';
	/**
	 * The current parser index.
	 * 
	 * @type	integer
	 */
	protected $inputIndex  = 0;
	/**
	 * The total input length in characters.
	 * 
	 * @type	integer
	 */
	protected $inputLength = 0;
	/**
	 * The next character.
	 * 
	 * @type	string
	 */
	protected $lookAhead   = null;
	/**
	 * The generated output.
	 * 
	 * @type	string
	 */
	protected $output      = '';

	/**
	 * Constructor Method.
	 * Initializes the given javascript input.
	 * 
	 * @param	string	$input
	 * 		The javascript input to be minified.
	 * 
	 * @return	void
	 */
	public function __construct($input)
	{
		$this->input       = str_replace("\r\n", "\n", $input);
		$this->inputLength = strlen($this->input);
	}

	/**
	 * Action -- do something! What to do is determined by the $command argument.
	 * 
	 * Action treats a string as a single character.
	 * Action recognizes a regular expression if it is preceded by ( or , or =.
	 * 
	 * throws Exception If parser errors are found:
	 * - Unterminated string literal
	 * - Unterminated regular expression set in regex literal
	 * - Unterminated regular expression literal
	 * 
	 * @param	integer	$command
	 * 		One of class constants:
	 * 		ACTION_KEEP_A
	 * 		- Output A. Copy B to A. Get the next B.
	 * 		ACTION_DELETE_A
	 * 		- Copy B to A. Get the next B. (Delete A).
	 * 		ACTION_DELETE_A_B
	 * 		- Get the next B. (Delete B).
	 * 
	 * @return	void
	 * 
	 * @throws	Exception
	 */
	protected function action($command)
	{
		switch($command)
		{
			case self::ACTION_KEEP_A:
		    		$this->output .= $this->a;
		
			case self::ACTION_DELETE_A:
				$this->a = $this->b;

				if ($this->a === "'" || $this->a === '"')
	      				for (;;)
					{
						$this->output .= $this->a;
						$this->a = $this->get();

						if ($this->a === $this->b)
							break;

						if (ord($this->a) <= self::ORD_LF)
							throw new Exception('Unterminated string literal.');

						if ($this->a === '\\') {
							$this->output .= $this->a;
							$this->a = $this->get();
						}
					}

			case self::ACTION_DELETE_A_B:
				$this->b = $this->next();

				if ($this->b === '/' && (
					$this->a === '(' || $this->a === ',' || $this->a === '=' ||
					$this->a === ':' || $this->a === '[' || $this->a === '!' ||
					$this->a === '&' || $this->a === '|' || $this->a === '?' ||
					$this->a === '{' || $this->a === '}' || $this->a === ';' ||
					$this->a === "\n" ))
				{

				$this->output .= $this->a . $this->b;

				for (;;)
				{
					$this->a = $this->get();

					if ($this->a === '[') 
					{
						for (;;)
						{
							$this->output .= $this->a;
							$this->a = $this->get();

							if ($this->a === ']')
								break;
							else if ($this->a === '\\')
							{
								$this->output .= $this->a;
								$this->a       = $this->get();
							}
							else if (ord($this->a) <= self::ORD_LF)
								throw new Exception('Unterminated regular expression set in regex literal.');
						}
					}
					else if ($this->a === '/')
						break;
					else if ($this->a === '\\')
					{
						$this->output .= $this->a;
						$this->a       = $this->get();
					}
					else if (ord($this->a) <= self::ORD_LF)
						throw new Exception('Unterminated regular expression literal.');

					$this->output .= $this->a;
				}

				$this->b = $this->next();
			}
		}
	}

	/**
	 * Get next char. Convert ctrl char to space.
	 * 
	 * @return	mixed
	 * 		The next char as string or null if there is no chars left.
	 */
	protected function get()
	{
		$c = $this->lookAhead;
		$this->lookAhead = null;

		if ($c === null)
		{
			if ($this->inputIndex < $this->inputLength)
			{
				$c = substr($this->input, $this->inputIndex, 1);
				$this->inputIndex += 1;
			}
			else
				$c = null;
		}

		if ($c === "\r")
			return "\n";

		if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE)
			return $c;

		return ' ';
	}

	/**
	 * Is $c a letter, digit, underscore, dollar sign, or non-ASCII character.
	 * 
	 * @param	string	$c
	 * 		The string to check.
	 * 
	 * @return	boolean
	 * 		Returns true if the given character is alpha num, false otherwise.
	 */
	protected function isAlphaNum($c)
	{
		return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
	}

	/**
	 * Perform the minification.
	 * 
	 * @return	string
	 * 		The minified result.
	 */
	protected function min()
	{
		if (strncmp($this->peek(), "\xef", 1) == 0) {
			$this->get();
			$this->get();
			$this->get();
		} 

		$this->a = "\n";
		$this->action(self::ACTION_DELETE_A_B);

		while ($this->a !== null)
		{
			switch ($this->a)
			{
				case ' ':
					if ($this->isAlphaNum($this->b))
						$this->action(self::ACTION_KEEP_A);
					else
						$this->action(self::ACTION_DELETE_A);
						
					break;
				case "\n":
					switch ($this->b)
					{
						case '{':
						case '[':
						case '(':
						case '+':
						case '-':
						case '!':
						case '~':
							$this->action(self::ACTION_KEEP_A);
							break;

						case ' ':
							$this->action(self::ACTION_DELETE_A_B);
							break;
						default:
							if ($this->isAlphaNum($this->b))
								$this->action(self::ACTION_KEEP_A);
							else
								$this->action(self::ACTION_DELETE_A);
					}
					break;
				default:
					switch ($this->b)
					{
						case ' ':
							if ($this->isAlphaNum($this->a))
							{
								$this->action(self::ACTION_KEEP_A);
								break;
							}

							$this->action(self::ACTION_DELETE_A_B);
							break;

						case "\n":
							switch ($this->a)
							{
								case '}':
								case ']':
								case ')':
								case '+':
								case '-':
								case '"':
								case "'":
									$this->action(self::ACTION_KEEP_A);
									break;

								default:
									if ($this->isAlphaNum($this->a))
										$this->action(self::ACTION_KEEP_A);
									else
										$this->action(self::ACTION_DELETE_A_B);
							}
							break;

						default:
							$this->action(self::ACTION_KEEP_A);
							break;
					}
			}
		}

		return $this->output;
	}

	/**
	 * Get the next character, skipping over comments.
	 * peek() is used to see if a '/' is followed by a '/' or '*'.
	 * 
	 * @return	string
	 * 		The next character.
	 * 
	 * @throws	Exception
	 */
	protected function next()
	{
		$c = $this->get();

		if ($c === '/')
		{
			switch($this->peek())
			{
				case '/':
					for (;;)
					{
						$c = $this->get();
						if (ord($c) <= self::ORD_LF)
							return $c;
					}
				case '*':
					$this->get();
					for (;;)
						switch($this->get())
						{
							case '*':
								if ($this->peek() === '/')
								{
									$this->get();
									return ' ';
								}
								break;

							case null:
								throw new Exception('Unterminated comment.');
						}
				default:
					return $c;
			}
		}

		return $c;
	}

	/**
	 * Get next char.
	 * If is ctrl character, translate to a space or newline.
	 * 
	 * @return	string
	 * 		The next character.
	 */
	protected function peek()
	{
		$this->lookAhead = $this->get();
		return $this->lookAhead;
	}
}
//#section_end#
?>