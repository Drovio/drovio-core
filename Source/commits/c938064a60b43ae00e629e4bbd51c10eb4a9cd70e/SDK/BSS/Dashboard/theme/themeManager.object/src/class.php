<?php
//#section#[header]
// Namespace
namespace BSS\Dashboard\theme;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("API", "Profile", "teamSettings");

use \API\Profile\teamSettings;

class themeManager
{
	private $ts;
	
	// Constructor Method
	public function __construct($teamID = "")
	{
		// Initialize team settings
		$this->ts = new teamSettings($teamID);
	}
	
	public function setBackgroundImage($image)
	{
		$this->ts->set("background_image", $image);
	}
	
	public function getBackgroundImage()
	{
		$this->ts->get("background_image");
	}
	
	public function getBackgroundImageList()
	{
		// Create the template background image list
		$imageList = array();
		$imageList[] = "%{media}/boss/dashboard/themes/thm01.png";
		$imageList[] = "%{media}/boss/dashboard/themes/thm02.png";
		$imageList[] = "%{media}/boss/dashboard/themes/thm03.png";
		$imageList[] = "%{media}/boss/dashboard/themes/thm04.png";
		$imageList[] = "%{media}/boss/dashboard/themes/thm05.png";
		$imageList[] = "%{media}/boss/dashboard/themes/thm06.png";
		
		return $imageList;
	}
	
	public function setBackgroundColor($color)
	{
		$this->ts->set("background_color", $color);
	}
	
	public function getBackgroundColor()
	{
		$this->ts->get("background_color");
	}
}
//#section_end#
?>