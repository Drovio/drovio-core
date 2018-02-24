<?php
//#section#[header]
// Namespace
namespace GTL\Docs\md;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	GTL
 * @package	Docs
 * @namespace	\md
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

interface MarkdownInterface
{

  #
  # Initialize the parser and return the result of its transform method.
  # This will work fine for derived classes too.
  #
  public static function defaultTransform($text);

  #
  # Main function. Performs some preprocessing on the input text
  # and pass it through the document gamut.
  #
  public function transform($text);

}
//#section_end#
?>