<?php // Common Function to Initialize MySQLi Connection
function dbConnect() {
	$db = new mysqli('localhost', 'admin', 'fantasyFencing', 'fencing');
	if ($db->connect_error) {
		die("Connection failed: " . $db->connect_error);
	}
	return $db;
}
?>
