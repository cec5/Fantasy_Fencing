<?php
// This file contains functions relating to scraping data from the FIE and storing it locally

require '../vendor/autoload.php';

use Smalot\PdfParser\Parser;

// List of valid 3-letter country codes
$validCountryCodes = [
    	"AFG", "AIN", "ALB", "ALG", "ANG", "ANT", "ARG", "ARM", "ARU", "ASA",
    	"AUS", "AUT", "AZE", "BAH", "BAN", "BAR", "BEL", "BEN", "BER", "BIZ",
    	"BOL", "BOT", "BRA", "BRN", "BRU", "BUL", "BUR", "CAM", "CAN", "CGO",
    	"CHI", "CHN", "CIV", "CMR", "COD", "COL", "CPV", "CRC", "CRO", "CUB",
    	"CYP", "CZE", "DEN", "DMA", "DOM", "ECU", "EGY", "ESA", "ESP", "EST",
    	"FIN", "FRA", "GAB", "GBR", "GEO", "GEQ", "GER", "GHA", "GRE", "GUA",
    	"GUI", "GUM", "GUY", "HAI", "HKG", "HON", "HUN", "INA", "IND", "IRL",
    	"IRI", "IRQ", "ISL", "ISR", "ISV", "ITA", "JAM", "JOR", "JPN", "KAZ",
    	"KEN", "KGZ", "KOR", "KSA", "KUW", "LAT", "LBA", "LBN", "LTU", "LUX",
    	"MAC", "MAD", "MAR", "MAS", "MDA", "MEX", "MKD", "MLI", "MLT", "MNE",
    	"MON", "MRI", "MYA", "NAM", "NCA", "NED", "NEP", "NGR", "NIG", "NOR",
    	"NZL", "OMA", "PAK", "PAN", "PAR", "PER", "PHI", "PLE", "PNG", "POL",
    	"POR", "PRK", "PUR", "QAT", "ROU", "RSA", "RUS", "RWA", "SAM", "SEN",
    	"SLE", "SLO", "SMR", "SOM", "SRB", "SRI", "SUI", "SVK", "SWE", "SYR",
    	"THA", "TJK", "TKM", "TOG", "TPE", "TUN", "TUR", "UAE", "UGA", "UKR",
    	"URU", "USA", "UZB", "VEN", "VIE", "YEM", "_AIN"
];

// Fetches fencer data from the FIE website based on ID
function scrapeFencerData($fencerId) {
    	global $validCountryCodes;

    	$baseHtmlUrl = "https://fie.org/athletes/";
    	$basePdfUrl = $baseHtmlUrl . $fencerId . "/profile";

    	// Fetch HTML content
    	$htmlUrl = $baseHtmlUrl . $fencerId;
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $htmlUrl);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	$html = curl_exec($ch);
    	curl_close($ch);

    	// Check if HTML request was successful
    	if (!$html || strpos($html, 'Error 404') !== false || strpos($html, 'Page not found') !== false) {
        	return null;
    	}

    	// Load HTML into DOMDocument and initialize DOMXPath
    	$dom = new DOMDocument();
    	@$dom->loadHTML($html);
    	$xpath = new DOMXPath($dom);

    	// Extract full name and split it into first and last names
    	$fullName = trim($xpath->evaluate("string(//h1[@class='AthleteHero-fencerName'])"));
    	$nameParts = explode(" ", $fullName);
    	$firstNameParts = [];
    	$lastNameParts = [];
    	foreach ($nameParts as $part) {
        	if (strtoupper($part) === $part) {
            		$lastNameParts[] = $part;
        	} else {
            		$firstNameParts[] = $part;
        	}
    	}

    	// Check weapon type from available options
    	$weapon = '';
    	$weapons = ['sabre', 'epee', 'foil'];
    	foreach ($weapons as $weaponType) {
        	$weaponValue = trim($xpath->evaluate("string(//div[@class='ProfileInfo-item' and not(span[@class='ProfileInfo-label'])]/span[text()='$weaponType'])"));
        	if ($weaponValue) {
            		$weapon = $weaponType;
            		break;
        	}
	}

    	// Retrieve PDF content for nationality and gender information
    	$pdfContent = @file_get_contents($basePdfUrl);
    	if ($pdfContent === false) {
        	return "Error: Unable to retrieve PDF content for fencer ID $fencerId.";
    	}

   	// Parse PDF content
    	$parser = new Parser();
    	$pdf = $parser->parseContent($pdfContent);
    	$pdfText = $pdf->getText();

    	// Extract valid nationality from PDF content
    	$nationality = '';
    	preg_match_all('/\b([A-Z]{3}|_AIN)\b/', $pdfText, $matches);
    	foreach ($matches[1] as $match) {
        	if (in_array($match, $validCountryCodes)) {
            		$nationality = $match;
            		break;
        	}
    	}

    	// Match a single 'M' or 'F' for gender
    	$gender = '';
    	if (preg_match('/\b(M|F)\b/i', $pdfText, $matches)) {
        	$gender = ($matches[1] === 'M') ? 'male' : 'female';
    	}

    	// Compile all data into an array
    	$data = [
        	'id' => $fencerId,
        	'name' => $fullName,
        	'firstName' => implode(" ", $firstNameParts),
        	'lastName' => implode(" ", $lastNameParts),
        	'gender' => $gender,
        	'nationality' => $nationality,
        	'weapon' => $weapon,
    	];
    	return $data;
}
?>
