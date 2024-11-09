<?php
require_once('../databaseFunctions.php');

// Manually adds/updates a singular Athlete to the database based on user input
// Usually used for adding athletes with missing PDFs (who can't be added automatically)

print_r("Database Update, Manually Add Athlete\n");
print_r("Enter Athlete ID: ");
$id = readline();

print_r("Enter Full Name: ");
$name = readline();

print_r("Enter First Name: ");
$firstName = readline();

print_r("Enter Last Name: ");
$lastName = readline();

print_r("Enter Gender (male/female): ");
$gender = readline();

print_r("Enter Nationality (3-letter code): ");
$nationality = readline();

print_r("Enter Primary Weapon (sabre/epee/foil): ");
$weapon = readline();

print_r("Enter Secondary Weapon (sabre/epee/foil or leave blank for none): ");
$weapon2 = readline();
$weapon2 = $weapon2 ?: null;

$athleteData = [
        'id' => (int) $id,
        'name' => $name,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'gender' => $gender,
        'nationality' => strtoupper($nationality),
        'weapon' => $weapon,
        'weapon2' => $weapon2
];

try {
    	updateAthlete($athleteData);
    	echo "Athlete data successfully added/updated in the database\n";
} catch (Exception $e) {
    	echo "Failed to add/update Athlete: " . $e->getMessage() . "\n";
}
?>
