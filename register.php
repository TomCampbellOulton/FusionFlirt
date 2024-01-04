<?php
session_start();

// Connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'fusion_flirt_db';
// Try and connect using the info above.			
$fname = $_POST["firstname"];
$sname = $_POST["surname"];
$dob = $_POST["dob"];
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
	// Could not get the data that should have been sent.
	exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Please complete the registration form');
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Email is not valid!');
}
if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
    exit('username is not valid!');
}
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Password must be between 5 and 20 characters long!');
}
// We need to check if the account with that username exists.
if ($stmt = $con->prepare('SELECT user_ID, hashedPassword FROM users_tb WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	$stmt->store_result();
	// Store the result so we can check if the account exists in the database.
	if ($stmt->num_rows > 0) {
		// username already exists
		echo 'username exists, please choose another!';
	} else {
		// username doesn't exists, insert new account
        if ($stmt = $con->prepare('INSERT INTO users_tb (username, hashedPassword) VALUES (?, ?)')) {
            // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $uniqid = uniqid();
            $stmt->bind_param('ss', $_POST['username'], $password);
			$bonus = ":c";
			if (password_verify($_POST['password'], $password)){
				$bonus = "c:";
			};
			$stmt->execute();


			// Find the user's ID
			$user_ID = 0;
			$stmt = $con->prepare('SELECT user_ID FROM users_tb WHERE username = ?');
			$stmt->bind_param('i', $_POST['username']);
			$stmt->execute();
			//$stmt->bind_result($user_ID);
			//$stmt->fetch();
			//$stmt->close();
			$result = $stmt->get_result();
			foreach ($result as $user){
				$user_ID = $user['user_ID'];
			}
			// Store the user's ID
			$_SESSION["id"] = $user_ID;

			echo $_POST['dob'];
			
			?><br><?php
			echo $user_ID;

			
			// Add the activation code
			$stmt = $con->prepare('INSERT INTO authentication_tb (fk_user_ID, activation_code) VALUES (?, ?)');
			$stmt->bind_param('is', $user_ID, $uniqid);
			$stmt->execute();
			$stmt->close();

			// Add the user's email
			$stmt = $con->prepare('INSERT INTO contact_details_tb (emailAddress, phoneNumber) VALUES (?, ?)');
			$stmt->bind_param('si', $_POST['email'], $_POST['phone_number']);
			$stmt->execute();
			$stmt->close();
			// Add the user's contact ID to the users table
			// Find the contact ID
			$stmt = $con->prepare('SELECT contact_ID FROM contact_details_tb WHERE emailAddress = ?');
			$stmt->bind_param('s', $_POST['email']);
			$stmt->execute();
			$stmt->bind_result($contact_ID);
			$stmt->fetch();
			$stmt->close();
			
			// Add the ID and user's name and dob to the users details
			$stmt = $con->prepare('UPDATE users_tb SET fk_contact_ID = ?, firstname = ?, surname = ?, dateOfBirth = ? WHERE user_ID = ?');
			$stmt->bind_param('isssi', $contact_ID, $fname, $sname, $dob, $user_ID);
			$stmt->execute();
			$stmt->close();





            $from    = 'fusionflirtauthentication@gmail.com';
            $subject = 'Account Activation Required';
            $headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
            // Update the activation variable below
            $activate_link = 'http://localhost/dating_App/fusionflirt1.2/activate.php?email=' . $_POST['email'] . '&code=' . $uniqid;
            $message = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . $activate_link . '</a>' . $bonus . '</p>';
            mail($_POST['email'], $subject, $message, $headers);
            echo 'Please check your email to activate your account!';
        } else {
            // Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all 3 fields.
            echo 'Could not prepare statement!';
        }
	}

} else {
	// Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all 3 fields.
	echo 'Could not prepare statement!';
}
$con->close();
?>

