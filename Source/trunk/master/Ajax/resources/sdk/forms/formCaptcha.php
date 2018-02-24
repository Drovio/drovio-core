<?php
//#section#[header]
require_once($_SERVER['DOCUMENT_ROOT'].'/_domainConfig.php');

// Importer
use \API\Platform\importer;

// Engine Start
importer::import("API", "Platform", "engine");
use \API\Platform\engine;
engine::start();

use \Exception;
//#section_end#
//#section#[code]
importer::import("ESS", "Protocol", "client::CaptchaProtocol");
importer::import("API", "Resources", "storage::session");

use \ESS\Protocol\client\CaptchaProtocol;
use \API\Resources\storage\session;

$formID = trim($_GET['fid']);

//__________ [Script Code] __________//

// Create a random string using time functions and md5...
// Trim it afterwards down to 7 chars
$md5 = md5(microtime() * mktime());
$string = substr($md5, 0, 7);

CaptchaProtocol::set($formID, $string);

$w = 100;
$h = 40;

//$captcha = @imagecreatefrompng("./captcha.png");
$captcha = @imagecreatetruecolor($w, $h);

// imageloadfont();

// Colors for string and lines
$text = imagecolorallocate($captcha, 0, 0, 0);
$line = imagecolorallocatealpha($captcha,233,239,239,50);
$background = imagecolorallocate($captcha,255,255,255);

// Draws the string on the image
imagefilledrectangle($captcha, 0, 0, $w, $h, $background);
imagestring($captcha, 5, 20, 10, $string, $text);

// Draw lines
$linesNumber = rand(7, 10);
$lineEnds = array();
for ($i = 0; $i < $linesNumber; $i++)
{
	$lineEnds['xstart'] = rand(0, $w);
	$lineEnds['ystart'] = rand(0, 10);
	$lineEnds['xend'] = rand(0, $w);
	$lineEnds['yend'] = rand($h-10, $h);
	
	imageline($captcha, $lineEnds['xstart'], $lineEnds['ystart'], $lineEnds['xend'], $lineEnds['yend'], $line);
}

ob_end_clean();
ob_start();
header("Content-type: image/png");
imagepng($captcha);

imagecolordeallocate( $line );
imagecolordeallocate( $text );
imagecolordeallocate( $background );
imagedestroy($captcha);
//#section_end#
?>