<?php
//#section#[header]
// Namespace
namespace API\Developer\components\units\modules\moduleComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "components::units::modules::moduleGroup");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "DOMParser");

use \ESS\Protocol\client\BootLoader;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\misc\vcs;
use \API\Developer\components\units\modules\moduleGroup;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\resources\paths;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;


class mView
{
	private $id;
	
	private $viewsRoot;
	
	private $viewDirectory;
	
	private $vcs;
	
	// Constructor Method
	public function __construct($vcs, $moduleID, $viewsRoot, $viewID = "")
	{
		// Put your constructor method code here.
		$this->viewsRoot = $viewsRoot;
		$this->vcs = $vcs;
		$this->id = $viewID;
	}
	
	public function create($viewID)
	{
		// Initialize variables
		$this->id = $viewID;
		$this->viewDirectory = $this->viewsRoot."/".$this->getDirectoryName($this->id);
		
		// Create structure
		folderManager::create($this->viewDirectory, TRUE);
		
		$parser = new DOMParser();
		$root = $parser->create("view", "", $this->id);
		$parser->append($root);
		$parser->save($this->viewDirectory."/index.xml");
		
		$dependencies = $parser->create("dependencies");
		$parser->append($root, $dependencies);
		
		$innerModules = $parser->create("inner");
		$parser->append($root, $innerModules);
		
		// Update content
		$this->updateView();
		$this->updatePHPCode();
		$this->updateJS();
	}
	
	public function updateInfo($dependencies = array(), $innerModules = array())
	{
	}
	
	public function updateView($html = "", $css = "")
	{
		fileManager::put($this->viewDirectory."/view.html", $html);
		fileManager::put($this->viewDirectory."/view.css", $css);
	}
	
	public function updatePHPCode($code = "")
	{
		// Set source code (default if not given)
		$sourceCode = (trim($code) == "" ? $this->getDefaultSourceCode() : $code);
		
		// Clear code from unwanted characters
		$sourceCode = phpParser::clearCode($sourceCode);
		// Comment out dangerous commands
		$sourceCode = phpParser::safe($sourceCode);
		// Set code section
		$sourceCode = phpCoder::section($sourceCode, "code");
		$header = $this->buildHeader(TRUE);
		
		// Merge all source code
		$sourceCode = $header.$sourceCode;
		$sourceCode = phpParser::wrap($sourceCode);
		
		// Get File Path
		$filePath = $this->viewDirectory."/view.php";
		
		// Create temp file to check syntax
		$tempFile = $filePath.".temp";
		fileManager::create($tempFile, $sourceCode);
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;
			
		// Remove temp file
		fileManager::remove($tempFile);

		// Update File
		return fileManager::put($filePath, $sourceCode);
	}
	
	private function getDefaultSourceCode()
	{
		// Get Default Module Code
		$sourceCode = fileManager::get(systemRoot.paths::getDevRsrcPath()."/Content/Modules/Code/default.inc");
		
		// Clear Delimiters
		$sourceCode = phpParser::unwrap($sourceCode);
		
		// Wrap to section
		return trim($sourceCode);
	}
	
	private function buildHeader($wrapped = FALSE)
	{
		// Module ID
		$moduleHeader = "";
		$moduleHeader .= phpParser::variable("moduleID").' = '.$this->id.";\n\n";
		
		// Inner Modules
		$innerHeader = phpParser::variable("innerModules").' = array();'."\n";
		foreach ($this->innerModules as $key => $value)
			$innerHeader .= '$innerModules[\''.$key.'\'] = '.$value.";\n";
		
		// Get Headers
		$path = systemRoot.paths::getDevRsrcPath()."/Content/Modules/Headers/private.inc";
		$privateHeader = fileManager::get($path);
		$privateHeader = phpParser::unwrap($privateHeader);
		
		// Imports
		$importsHeader = $this->buildImports();
		
		// Merge
		$headerContent = "";
		$headerContent .= $moduleHeader;
		$headerContent .= $innerHeader;
		$headerContent .= $privateHeader;
		$headerContent .= $importsHeader;
		
		return ($wrapped ? phpCoder::section($headerContent, "header") : $headerContent);
	}
	
	private function buildImports()
	{
		// Build Imports
		$imports = "\n\n";
		$imports .= phpParser::comment("Import Packages", $multi = FALSE)."\n";
		$startups = "\n";
		foreach ($this->getImports() as $lib => $packages)
			foreach ($packages as $pkg)
				$imports .= 'importer::import("'.$lib.'", "'.$pkg.'");'."\n";
		
		// Merge and Clear
		$imports .= $startups;
		return preg_replace('/\n$/', '', $imports);
	}
	
	public function updateJS($code = "")
	{
		fileManager::put($this->viewDirectory."/view.js", $js);
	}
	
	private static function getDirectoryName($id)
	{
		return $id.".view";
	}
}
//#section_end#
?>