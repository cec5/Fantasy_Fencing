<?php
// This file contains functions relating to scraping data from the FIE and returning it in an array

require_once '../../../backend/vendor/autoload.php';
require_once '../../../backend/php/dataArrays.php';
use Smalot\PdfParser\Parser;

// Fetches fencer data from the FIE website based on ID
function scrapeAthleteData($fencerId) {

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
    	if ($pdfContent === false || empty($pdfContent)) {
        	echo "Warning: No PDF content found for fencer ID $fencerId. Skipping.\n";
        	return null;  // Skip this athlete if PDF content is empty
    	}

    	// Parse PDF content
    	$parser = new Parser();
    	$pdf = $parser->parseContent($pdfContent);
    	$pdfText = $pdf->getText();

    	// Extract valid nationality from PDF content
    	$nationality = '';
    	preg_match_all('/\b([A-Z]{3}|_AIN)\b/', $pdfText, $matches);
    	foreach ($matches[1] as $match) {
    		if ($match === "_AIN"){
    			$nationality = "AIN";
    			break;
    		} else if (array_key_exists($match, $validCountryCodes)) {
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

function scrapeCompetitionData($season, $competitionId) {
    	$url = "https://fie.org/competitions/$season/$competitionId";
    
    	// Initialize a cURL session
   	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	$html = curl_exec($ch);
    	curl_close($ch);

    	// Check if HTML request was successful
    	if (!$html || strpos($html, 'Error 404') !== false || strpos($html, 'Page not found') !== false) {
        	return null;
    	}

    	// Extract JSON data from the "window._competition" JavaScript variable
    	if (preg_match('/window\._competition\s*=\s*(\{.*?\});/', $html, $matches)) {
        	$competitionData = json_decode($matches[1], true);

        	// Check if JSON decoding was successful
        	if (!$competitionData) {
            		return null;
        	}

        	// Extract the gender by finding the first occurrence of "gender":"M" or "gender":"F"
        	$gender = null;
        	if (preg_match('/"gender":"(M|F)"/', $matches[1], $genderMatch)) {
            		$gender = ($genderMatch[1] === 'M') ? 'male' : 'female';
        	}

        	// Map the extracted data to the structure of the competitions table
        	$data = [
            		'competitionId' => $competitionData['competitionId'],
            		'season' => $competitionData['season'],
            		'name' => $competitionData['name'],
            		'category' => $competitionData['competitionCategory'],
            		'weapon' => strtolower($competitionData['weapon']) === 's' ? 'sabre' : (strtolower($competitionData['weapon']) === 'e' ? 'epee' : 'foil'),
            		'gender' => $gender,  // Use extracted gender
            		'country' => $competitionData['federation'],
            		'location' => $competitionData['location'],
            		'startDate' => $competitionData['startDate'],
           		'endDate' => $competitionData['endDate']
        	];
        	return $data;
    	} else {
        	return null;  // Return null if the competition data is not found in the page
    	}
}

// Fetches array of Athlete IDs who competed in a particular tournament
function scrapeAthletesCompetition($season, $competitionId) {
    	$url = "https://fie.org/competitions/$season/$competitionId";

    	// Initialize a cURL session
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	$html = curl_exec($ch);
    	curl_close($ch);

    	// Check if HTML request was successful
    	if (!$html || strpos($html, 'Error 404') !== false || strpos($html, 'Page not found') !== false) {
        	return null;
    	}

    	// Extract JSON data from the "window._athletes" JavaScript variable
    	if (preg_match('/window\._athletes\s*=\s*(\[[^\]]*\]);/', $html, $matches)) {
        	$athletesData = json_decode($matches[1], true);

        	// Check if JSON decoding was successful
        	if (!$athletesData) {
            		return null;
        	}

        	// Extract athlete IDs
        	$athleteIds = [];
        	foreach ($athletesData as $athlete) {
            		if (isset($athlete['fencer']['id'])) {
                		$athleteIds[] = $athlete['fencer']['id'];
            		}
        	}
        	return $athleteIds;
    	} else {
        	return null;  // Return null if the athlete data is not found in the page
    	}
}

function scrapeCompetitionResults($season, $competitionId) {
    	$url = "https://fie.org/competitions/$season/$competitionId";

    	// Initialize a cURL session
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	$html = curl_exec($ch);
    	curl_close($ch);

    	// Check if HTML request was successful
    	if (!$html || strpos($html, 'Error 404') !== false || strpos($html, 'Page not found') !== false) {
        	return null;
    	}

    	// Extract JSON data from the "window._athletes" JavaScript variable
    	if (preg_match('/window\._athletes\s*=\s*(\[[^\]]*\]);/', $html, $matches)) {
        	$athletesData = json_decode($matches[1], true);
        	// Check if JSON decoding was successful
        	if (!$athletesData) {
            		return null;
        	}

        	// Extract the relevant data for each athlete
        	$competitionResults = [];
        	foreach ($athletesData as $athlete) {
            		if (isset($athlete['fencer']['id']) && isset($athlete['rank']) && isset($athlete['points'])) {
                		$competitionResults[] = [
                    			'competitionId' => $competitionId,
                    			'season' => $season,
                    			'athleteId' => $athlete['fencer']['id'],
                    			'finished' => $athlete['rank'],
                    			'points' => $athlete['points']
                		];
            		}
        	}
        	return $competitionResults;
    	} else {
        	return null;  // Return null if the athlete data is not found in the page
    	}
}
?>
