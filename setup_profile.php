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
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Home Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>FusionFlirt</h1>
				<a href="home.php">Home</a>
				<a href="surveys_page.php">Surveys Page</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
        <form method="POST">Please create a biography!
            <input type='text' name='biography'/>
            <input type="submit" value="Submit">
        </form>
        <?php 
        // Check if the biography has been set or not
        if (isset($_POST['biography'])){
            // Gets the biography the user has entered
            $bio = $_POST['biography'];
            // Add the biography to the database
            $stmt = $con->prepare('INSERT INTO biography_tb (bio) VALUES (?)');
            $stmt->bind_param('s', $bio);
            $stmt->execute();
            // Find the ID of that biography - as it is an auto-increment primary key, I can use insert_id to find it
            $bio_ID = $con->insert_id;
            $stmt->close();

            // Add the bio to the profile
            $stmt = $con->prepare('INSERT INTO profile_tb (fk_bio_ID) VALUES (?)');
            $stmt->bind_param('i', $bio_ID);
            $stmt->execute();
            // Find the ID of that profile - as it is an autoincrement primary key, I can use insert_id to find it
            $profile_ID = $con->insert_id;
            $stmt->close();

            $user_ID = $_SESSION['id'];
            echo $user_ID, $profile_ID;
            // Add the profile to the user's personal settings
            $stmt = $con->prepare('UPDATE users_tb SET fk_profile_ID = ? WHERE user_ID = ?');
            $stmt->bind_param('ii', $profile_ID, $user_ID);
            $stmt->execute();
            $stmt->close();

            // Now that setup has been complete, take the user back to the profile page
            header('Location: /dating_App/fusionFlirt1.5/profile.php');
            exit();
        }