<?php
// Common Function to Initialize MySQLi Connection
function dbConnect(){
	$db = new mysqli('localhost', 'admin', 'fantasyFencing', 'fencing');
	if ($db->connect_error){
		die("Connection failed: " . $db->connect_error);
	}
	return $db;
}

// Adds/Updates Athlete in local db
function updateAthlete($data) {
    	$db = dbConnect();
    	$stmt = $db->prepare("
        	INSERT INTO athletes (id, name, firstName, lastName, gender, nationality, weapon, weapon2)
        	VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        	ON DUPLICATE KEY UPDATE
            		name = VALUES(name),
            		firstName = VALUES(firstName),
            		lastName = VALUES(lastName),
            		gender = VALUES(gender),
            		nationality = VALUES(nationality),
            		weapon = VALUES(weapon),
            		weapon2 = VALUES(weapon2)
    	");
    	$stmt->bind_param(
        	"isssssss",
		$data['id'],
		$data['name'],
		$data['firstName'],
		$data['lastName'],
		$data['gender'],
		$data['nationality'],
		$data['weapon'],
		$data['weapon2']
    	);

    	if ($stmt->execute()) {
        	echo "Athlete data stored successfully\n";
    	} else {
        	echo "Error storing athlete data: " . $stmt->error;
    	}
    	$stmt->close();
    	$db->close();
}
?>
