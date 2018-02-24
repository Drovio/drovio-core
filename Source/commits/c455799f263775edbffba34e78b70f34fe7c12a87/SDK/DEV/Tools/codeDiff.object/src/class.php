<?php
//#section#[header]
// Namespace
namespace DEV\Tools;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Code/Text Difference Detector
 * 
 * Given two strings (text) finds and annotates the differences between.
 * 
 * @version	0.1-2
 * @created	July 28, 2014, 12:57 (EEST)
 * @revised	July 28, 2014, 13:05 (EEST)
 */
class codeDiff
{
	/**
	 * Split mode is by word.
	 * 
	 * @type	char
	 */
	const MODE_WORD = 'w';
	/**
	 * Split mode is by character.
	 * 
	 * @type	char
	 */
	const MODE_CHAR = 'c';
	/**
	 * Split mode is by line.
	 * 
	 * @type	char
	 */
	const MODE_LINE = 'l';

	/**
	 * Type of change is addition.
	 * 
	 * @type	char
	 */
	const CH_ADD = '+';
	
	/**
	 * Type of change is deletion.
	 * 
	 * @type	char
	 */
	const CH_DEL = '-';
	
	/**
	 * Not a change.
	 * 
	 * @type	char
	 */
	const CH_NON = '=';

	/**
	 * Holds possible errors during a functions execution.
	 * 
	 * @type	string
	 */
	private $error = '';
	
	/**
	 * The original text.
	 * 
	 * @type	string
	 */
	private $originalText = '';
	
	/**
	 * The altered text.
	 * 
	 * @type	string
	 */
	private $alteredText = '';
	

	/**
	 * Initalazes the object giving the two strings to be compared.
	 * 
	 * @param	string	$originalText
	 * 		The original text
	 * 
	 * @param	string	$alteredText
	 * 		The altered text
	 * 
	 * @return	void
	 */
	public function __construct($originalText ='', $alteredText = '')
	{
		$this->originalText = $originalText;	 
		$this->alteredText = $alteredText;
	}
	
	/**
	 * Finds the differences between two strings
	 * 
	 * @param	string	$mode
	 * 		The type by which the comparison will be made.
	 * 		Use MODE_ constants to set this values.
	 * 		The default is MODE_CHAR
	 * 
	 * @param	boolean	$generatePatch
	 * 		Flag which indicates if a patch (array) will also be constructed for each change.
	 * 
	 * @return	mixed
	 * 		The array of the changes between the two strings or FALSE on failure.
	 */
	public function diff($mode = self::MODE_CHAR, $generatePatch = TRUE) 
	{
		$before = $this->originalText;
		$after = $this->alteredText; 
		
		// Set default mode if empty
		$mode = (isset($mode) ? $mode : self::MODE_CHAR);
		
		// Depending the parameters we are given in splitString,
		// defining the end of a line and the end of the requested unit(word)
		// we can split the string in lines or words
		switch($mode)
		{
			case self::MODE_CHAR:
				$originalLength = strlen($before);
				$alteredLength = strlen($after);
				break;
			case self::MODE_WORD:
				$before = $this->splitString($before, " \t", "\r\n", $posb);
				$originalLength = count($before);
				$after = $this->splitString($after, " \t", "\r\n", $posa);
				$alteredLength = count($after);
				break;

			case self::MODE_LINE:
				$before = $this->splitString($before, "\r\n", '', $posb);
				$originalLength = count($before);
				$after = $this->splitString($after, "\r\n", '', $posa);
				$alteredLength = count($after);
				break;

			default:
				$this->error = $mode.' is not a supported mode for getting the text differences';
				return FALSE;
		}
		
		$diff = array();
		
		// Itereting the two texts char by char
		// Altered text is checked against original (not the oposite)
		for($b = $a = 0; $b < $originalLength && $a < $alteredLength ;)
		{
			// Using a for loop to calculate $a and $pb values in order to point at the same letter
			// $a is the carret in the altered text and $pb the carret in the original text
			// $pb is a secondary carret to transverse a small area of  the "original text"
			// $a and $pb must point to the same character (but not necessarily in the same position ) in order to find the possible shift of the characters
			for($pb = $b; $a < $alteredLength && $pb < $originalLength && $after[$a] === $before[$pb]; ++$a, ++$pb);
			// char X is at the same position (inside the string text) in both cases (texts)
			if($pb !== $b)
			{
				// If the carret ($pb) from the above 'for loop' did not move
				// means that chars at positions $a and $b are not equal
				$diff[] = array(
					'change'=>'=',
					'position'=>($mode === self::MODE_CHAR ? $b : $posb[$b]),
					'length'=>($mode === self::MODE_CHAR ? $pb - $b : $posb[$pb] - $posb[$b])
				);
				$b = $pb;
			}
			// Check if we reached the ent of the 'original text'
			// Code after the loop will take care the left overs
			if($b === $originalLength)
				break;
			
			// calculate $pa to find the shift
			// we know that the char at position $b is altered but we need to know if it was deleted or
			// a new character has been added meaning that the character in position $b changed its position
			for($pb = $b; $pb < $originalLength; ++$pb)
			{
				// $pa is a secondary caret to transverse a small area of  the "altered text"
				for($pa = $a ; $pa < $alteredLength && $after[$pa] !== $before[$pb]; ++$pa);
				// Check if we reached the ent of the 'altered text'
				// Code after the loop will take care the left overs
				if($pa !== $alteredLength)
					break;
			}
			if($pb !== $b)
			{
				// If the carret ($pb) from the above outer 'for loop' did not move
				// means that char, at position $b does not exist in the 'altered text', therefore it has been deleted
				$diff[] = array(
					'change'=>'-',
					'position'=>($mode === self::MODE_CHAR ? $b : $posb[$b]),
					'length'=>($mode === self::MODE_CHAR ? $pb - $b : $posb[$pb] - $posb[$b])
				);
				$b = $pb;
			}
			if($pa !== $a)
			{
				// If the carret ($pa) from the above inner 'for loop' did not move
				// means that char at position $a does not exist in the 'original text', therefore it  has been added
				$position = ($mode === self::MODE_CHAR ? $a : $posa[$a]);
				$length = ($mode === self::MODE_CHAR ? $pa - $a : $posa[$pa] - $posa[$a]);
				$change = array(
					'change'=>'+',
					'position'=>$position,
					'length'=>$length
				);
				// Generating the pacth (store the char that need to be added)
				if($generatePatch)
				{
					if($mode === self::MODE_CHAR)
						$patch = substr($after, $position, $length);
					else
					{
						$patch = $after[$a];
						for(++$a; $a < $pa; ++$a)
							$patch .= $after[$a];
					}
					$change['patch'] = $patch;
				}
				
				// Add that 'change' to the overal array
				$diff[] = $change;
				$a = $pa;
			}
		}
		
		// In case that 'altered text' is bigger than the original, 
		// and the left over text is an addition at the end, the above code does not detect it
		// We need take care of it
		if($a < $alteredLength )
		{
			$position = ($mode === self::MODE_CHAR ? $a : $posa[$a]);
			$length = ($mode === self::MODE_CHAR ? $alteredLength - $a : $posa[$alteredLength] - $posa[$a]);
			$change = array(
				'change'=>'+',
				'position'=>$position,
				'length'=>$length
			);
			if($generatePatch)
			{
				if($mode === self::MODE_CHAR)
					$patch = substr($after, $position, $length);
				else
				{
					$patch = $after[$a];
					for(++$a; $a < $alteredLength; ++$a)
						$patch .= $after[$a];
				}
				$change['patch'] = $patch; 
			}
			// Add that 'change' to the overal array
			$diff[] = $change;
		}
		return $diff;
	}
	
	/**
	 * Applies the patch previously created, using diff function, on the original text.
	 * 
	 * @param	array	$difference
	 * 		An array of arrays (a list of changes), each one containing information about a change.
	 * 		The nested arrays must contain the 'patch' property.
	 * 
	 * @return	string
	 * 		The patched text.
	 */
	public function patch($difference)
	{
		$before = $this->originalText;	
		
		$this->error = '';
	
		$after = '';
		$b = 0;
		foreach($difference as $segment)
		{
			switch($segment['change'])
			{
				case '-':
					// just move ther carret $b nothing needs to be added, ignore the text
					if($segment['position'] !== $b)
					{
						$this->error = 'removed segment position is '.$segment['position'].' and not '.$b.' as expected';
						return false;
					}
					$b += $segment['length'];
					break;
				case '+':
					// add the 'new' text
					$after .= $segment['patch'];
					break;
				case '=':
					// the original text needs to be added.
					if($segment['position'] !== $b)
					{
						$this->error = 'removed segment position is '.$segment['position'].' and not '.$b.' as expected';
						return null;
					}
					$b += $segment['length'];
					$after .= substr($before, $segment['position'], $segment['length']);
					break;
				default:
					$this->error = $segment['change'].' change type is not supported';
					return null;
			}
		}
		return  $after;
	}
	
	
	/**
	 * Splits a string by word or line according to separator indicator and end (line) indicator.
	 * 
	 * @param	string	$string
	 * 		The string to be splitted.
	 * 
	 * @param	string	$separators
	 * 		The separators indicators.
	 * 
	 * @param	string	$end
	 * 		The end line indicators
	 * 
	 * @param	array	$positions
	 * 		(By reference) The positions table which hold the positions that the string was splitted.
	 * 
	 * @return	array
	 * 		The splitted values.
	 */
	private function splitString($string, $separators, $end, &$positions)
	{
		$lenght = strlen($string);
		$split = array();
		for($p = 0; $p < $lenght;)
		{
			// Find the number sequential character in $string until a character from $separators.
			// $end is found starting from $p
			$e = strcspn($string, $separators.$end, $p);
			$e += strspn($string, $separators, $p + $e);
			$split[] = substr($string, $p, $e);
			$positions[] = $p;
			$p += $e;
			if(strlen($end)
			&& ($e = strspn($string, $end, $p)))
			{
				$split[] = substr($string, $p, $e);
				$positions[] = $p;
				$p += $e;
			}
		}
		$positions[] = $p;
		return $split;
	}
	
}
//#section_end#
?>