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

if (isset($_POST['submitChanges'])){
    $user_ID = $_SESSION['id'];
    $fname = $_POST['firstname'];
    $sname = $_POST['surname'];
    $dob = $_POST['date_of_birth'];
    $phone_num = $_POST['phone_number'];
    $email = $_POST['email_address'];
    $contact_ID = $_SESSION["contact_ID"];
    
    // Update the user's email
    $stmt = $con->prepare('UPDATE contact_details_tb SET emailAddress = ?, phoneNumber = ? WHERE contact_ID = ?');
    $stmt->bind_param('sii', $email, $phone_num, $contact_ID);
    $stmt->execute();
    $stmt->close();
    
    // Update the user's name and dob 
    $stmt = $con->prepare('UPDATE users_tb SET firstname = ?, surname = ?, dateOfBirth = ? WHERE user_ID = ?');
    $stmt->bind_param('sssi', $fname, $sname, $dob, $user_ID);
    $stmt->execute();
    $stmt->close();


}
header('Location: /dating_App/fusionflirt1.7/profile.php');
exit();

?>