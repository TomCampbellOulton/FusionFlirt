<?php

// Connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'fusion_flirt_db';
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// First we check if the email and code exists...
if (isset($_GET['email'], $_GET['code'])) {
	// Starts session to retrieve locally stored IDs.
	session_start();
	$user_ID = $_SESSION["id"];
	// Get the user's activation code
	$activation_code = 0;
	$stmt = $con->prepare('SELECT activation_code FROM authentication_tb WHERE fk_user_ID = ?');
	$stmt->bind_param('i',$user_ID);
	$stmt->execute();
	$stmt->bind_result($activation_code);
	$stmt->fetch();
	$stmt->close();

	// If the account has been activated
	if ($activation_code === "activated"){
		echo 'The account is active! :D You can now <a href="index.html">login</a>!';

	} else{
		echo "The account has not been activated :c";
		// Check if the authentication code is correct
		$code = $_GET['code'];
		$newCode = "activated";
		if ($activation_code = $code){
			// Activate the account
			$stmt = $con->prepare('UPDATE authentication_tb SET activation_code = ? WHERE fk_user_ID = ?');
			$stmt->bind_param('si', $newCode, $user_ID);
			$stmt->execute();
			$stmt->close();
		} else{
			echo "<br>This code is not correct. Please try again.";
		}
	}

}
?>