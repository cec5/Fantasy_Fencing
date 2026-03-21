<?php
require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/src/dataArrays.php');

use Smalot\PdfParser\Parser;

class FIEScraperService {
    private static function fetchHtml($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html || strpos($html, 'Error 404') !== false || strpos($html, 'Page not found') !== false) {
            return null;
        }
        return $html;
    }

    private static function extractJavascriptJson($html, $variableName) {
        if (preg_match('/window\.' . preg_quote($variableName) . '\s*=\s*(.+?);/s', $html, $matches)) {
            return json_decode($matches[1], true);
        }
        return null;
    }

    public static function scrapeAthleteData($fencerId) {
        global $countryCodeMap;

        $baseHtmlUrl = "https://fie.org/athletes/";
        $html = self::fetchHtml($baseHtmlUrl . $fencerId);
        
        if (!$html) return null;

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Extract Name
        $fullName = trim($xpath->evaluate("string(//h1[@class='AthleteHero-fencerName'])"));
        $nameParts = explode(" ", $fullName);
        $firstNameParts = [];
        $lastNameParts = [];
        
        foreach ($nameParts as $part) {
            if (strtoupper($part) === $part) $lastNameParts[] = $part;
            else $firstNameParts[] = $part;
        }

        // Extract Weapons
        $weapon = null;
        $weapon2 = null;
        $weaponOptions = $xpath->query("//select[@class='ProfileInfo-weaponDropdown js-athlete-dropdown-weapon']/option");
        
        if ($weaponOptions->length > 1) {
            foreach ($weaponOptions as $option) {
                if ($option->getAttribute('selected')) $weapon = strtolower($option->textContent);
                else $weapon2 = strtolower($option->textContent);
            }
        } else {
            foreach (['sabre', 'epee', 'foil'] as $weaponType) {
                $weaponValue = trim($xpath->evaluate("string(//div[@class='ProfileInfo-item' and not(span[@class='ProfileInfo-label'])]/span[text()='$weaponType'])"));
                if ($weaponValue) {
                    $weapon = $weaponType;
                    break;
                }
            }
        }

        // Extract Nationality
        $nationality = "FIE"; // Default
        $flagElement = $xpath->query("//span[contains(@class, 'AthleteHero-flag')]");
        if ($flagElement->length > 0) {
            $flagClass = $flagElement->item(0)->getAttribute("class");
            if (preg_match('/Flag-icon--([a-z_]{2,3})/i', $flagClass, $matches)) {
                $twoLetterCode = strtoupper($matches[1]);
                if ($twoLetterCode === "A_" || $twoLetterCode === "_A") {
                    $nationality = "AIN";
                } else {
                    $nationality = array_search($twoLetterCode, $countryCodeMap) ?: "FIE";
                }
            }
        }

        // PDF Gender Extraction
        $pdfContent = @file_get_contents($baseHtmlUrl . $fencerId . "/profile");
        $gender = '';
        if ($pdfContent) {
            $parser = new Parser();
            $pdfText = $parser->parseContent($pdfContent)->getText();
            if (preg_match('/\b(M|F)\b/i', $pdfText, $matches)) {
                $gender = ($matches[1] === 'M') ? 'male' : 'female';
            }
        } else {
            echo "WARNING: No PDF found for Athlete ID:[$fencerId]\n";
        }

        return [
            'id' => $fencerId,
            'name' => $fullName,
            'firstName' => implode(" ", $firstNameParts),
            'lastName' => implode(" ", $lastNameParts),
            'gender' => $gender,
            'nationality' => $nationality,
            'weapon' => $weapon,
            'weapon2' => $weapon2
        ];
    }

	// Extracts Competition metadata
    public static function scrapeCompetitionData($season, $competitionId) {
        $html = self::fetchHtml("https://fie.org/competitions/$season/$competitionId");
        if (!$html) return null;

        $competitionData = self::extractJavascriptJson($html, '_competition');
        if (!$competitionData) return null;

        $validCategories = ['S', 'J', 'C', 'V'];
        $ageCategory = in_array($competitionData['category'] ?? '', $validCategories) ? $competitionData['category'] : 'S';
        
        $weaponStr = strtolower($competitionData['weapon']);
        $weapon = $weaponStr === 's' ? 'sabre' : ($weaponStr === 'e' ? 'epee' : 'foil');

        return [
            'competitionId' => $competitionData['competitionId'],
            'season' => $competitionData['season'],
            'name' => $competitionData['name'],
            'category' => $competitionData['competitionCategory'],
            'weapon' => $weapon,
            'gender' => ($competitionData['gender'] ?? '') === 'M' ? 'male' : 'female',
            'country' => $competitionData['federation'],
            'location' => $competitionData['location'],
            'startDate' => $competitionData['startDate'],
            'endDate' => $competitionData['endDate'],
            'ageCategory' => $ageCategory
        ];
    }

    // Fetches array of Athlete IDs who competed in a particular tournament
    public static function scrapeAthletesCompetition($season, $competitionId) {
        $html = self::fetchHtml("https://fie.org/competitions/$season/$competitionId");
        if (!$html) return null;

        $athletesData = self::extractJavascriptJson($html, '_athletes');
        if (!$athletesData) return null;

        $athleteIds = [];
        foreach ($athletesData as $athlete) {
            if (isset($athlete['fencer']['id'])) {
                $athleteIds[] = $athlete['fencer']['id'];
            }
        }
        return $athleteIds;
    }

    // Extracts full results (Rank and Points) for a competition
    public static function scrapeCompetitionResults($season, $competitionId) {
        $html = self::fetchHtml("https://fie.org/competitions/$season/$competitionId");
        if (!$html) return null;

        $athletesData = self::extractJavascriptJson($html, '_athletes');
        if (!$athletesData) return null;

        $competitionResults = [];
        foreach ($athletesData as $athlete) {
            if (isset($athlete['fencer']['id'], $athlete['rank'], $athlete['points'])) {
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
    }

    // Fetches array of Athlete IDs entered before the competition starts (from PDF view)
    public static function scrapeCompetitionEntries($season, $competitionId) {
        $html = self::fetchHtml("https://fie.org/competition/$season/$competitionId/entry/pdf");
        if (!$html) return null;

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $rows = $xpath->query("//tbody/tr");

        $athleteIds = [];
        foreach ($rows as $row) {
            $columns = $row->getElementsByTagName("td");
            if ($columns->length >= 6) {
                $athleteId = trim($columns->item(5)->textContent);
                if (!empty($athleteId) && is_numeric($athleteId)) {
                    $athleteIds[] = $athleteId;
                }
            }
        }
        return $athleteIds;
    }
}
?>