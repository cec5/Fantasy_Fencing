<?php // Contains miscellaneous functions that are not related to the database, scraper, or user accounts

function getFlagEmoji($countryCode) {
    	if (strlen($countryCode) != 2) return '';
    	$firstLetter = mb_convert_encoding('&#' . (127397 + ord($countryCode[0])) . ';', 'UTF-8', 'HTML-ENTITIES');
    	$secondLetter = mb_convert_encoding('&#' . (127397 + ord($countryCode[1])) . ';', 'UTF-8', 'HTML-ENTITIES');
    	return $firstLetter . $secondLetter;
}
?>
