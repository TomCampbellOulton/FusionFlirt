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

// Default length of 10
function generatePassword ($length = 10){
    // Characters to use in the password
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $pword = '';
    for ($i = 0; $i < $length; $i++) {
        $pword .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $pword;
}

$username = $_POST['username'];
// Make a new password
$password = generatePassword();

// Find the user's ID
$stmt = $con->prepare('SELECT user_ID, contact_ID FROM users_tb WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($user_ID, $contact_ID);
$stmt->fetch();
$stmt->close();

// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
$hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Insert this new password into the database
$stmt = $con->prepare('UPDATE users_tb SET hashedPassword = ? WHERE user_ID = ?');
$stmt->bind_param('si', $hashedPassword, $user_ID);
$stmt->execute();
$stmt->close();

// Get their email
$stmt = $con->prepare('SELECT emailAddress FROM contact_details_tb WHERE contact_ID = ?');
$stmt->bind_param('i', $contact_ID);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();


// Email the person their new password
$from    = 'fusionflirtauthentication@gmail.com';
$subject = 'You reset your password. Here is your new password';
$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
$message = '<p> Here is your new password ="' . $password . '</p>';
mail($email, $subject, $message, $headers);
echo 'Please check your email to find your new password.';

?>
