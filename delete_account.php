<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'fusion_flirt_db';
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Delete the account
$user_ID = $_SESSION['id'];
$stmt = $con->prepare('DELETE FROM authentication_tb WHERE fk_user_ID = ?');
$stmt->bind_param('i', $user_ID);
$stmt->execute();
$stmt->close();
// Delete the account
$user_ID = $_SESSION['id'];
$stmt = $con->prepare('DELETE FROM users_tb WHERE user_ID = ?');
$stmt->bind_param('i', $user_ID);
$stmt->execute();
$stmt->close();
// Remove their session ID
$_SESSION['id'] = NULL;
// Return them to the homepage
header('Location: /dating_App/fusionflirt1.7/index.html');
exit();
?>
