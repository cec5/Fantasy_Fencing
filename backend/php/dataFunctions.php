<?php
// This file contains functions relating to scraping data from the FIE and returning it in an array

require_once '../vendor/autoload.php';
require_once 'countries.php';
use Smalot\PdfParser\Parser;

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

    	$weapon = null;
    	$weapon2 = null;

    	// Check if there is a weapon dropdown for multiple weapons
    	$weaponOptions = $xpath->query("//select[@class='ProfileInfo-weaponDropdown js-athlete-dropdown-weapon']/option");
    	if ($weaponOptions->length > 1) {
        	// Multiple weapons: Assign primary and secondary
        	foreach ($weaponOptions as $option) {
            		if ($option->getAttribute('selected')) {
                		$weapon = strtolower($option->textContent);
            		} else {
                		$weapon2 = strtolower($option->textContent);
            		}
        	}
    	} else {
        	// Single weapon case
        	$weapons = ['sabre', 'epee', 'foil'];
        	foreach ($weapons as $weaponType) {
            		$weaponValue = trim($xpath->evaluate("string(//div[@class='ProfileInfo-item' and not(span[@class='ProfileInfo-label'])]/span[text()='$weaponType'])"));
            		if ($weaponValue) {
                		$weapon = $weaponType;
                		break;
            		}
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
        	if (array_key_exists($match, $validCountryCodes)) {
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
        	'weapon2' => $weapon2
    	];
    	return $data;
}
?>
