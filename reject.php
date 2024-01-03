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

// Get the user's ID
$user_ID = $_SESSION['id'];




if (isset($_POST['respond'])){ // If the user has clicked on decline
					
    // The match ID
    $match_ID = $_POST['respond'];
    echo 'REJECTED!';
    $stmt = $con->prepare('DELETE FROM matches_tb  WHERE match_ID = ?');
    // If the user has accepted, add this to the database
    $stmt->bind_param('i', $match_ID);
    $stmt->execute();
    $stmt->close();
    
}
header('Location: /dating_App/fusionFlirt1.5/home.php');
exit();


?>