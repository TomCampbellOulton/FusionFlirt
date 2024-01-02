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
				<h1>Website Title</h1>
				<a href="home.php">Home</a>
				<a href="surveys_page.php">Surveys Page</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
			<h2>Home Page</h2>
			<p>Welcome back, <?=$_SESSION['name']?>!</p>
			<p>Ello!</p>
		</div>


	<?php
	$user_ID = $_SESSION["id"];
	// Get the user's details
	$stmt = $con->prepare('SELECT username, firstname, surname, dateOfBirth, fk_address_ID, fk_contact_ID, fk_profile_ID FROM users_tb WHERE user_ID = ?');
	$stmt->bind_param('i', $user_ID);
	$stmt->execute();
	$stmt->bind_result($uname, $fname, $sname, $dob, $fk_add_ID, $fk_cont_ID, $fk_profile_ID);
	$stmt->fetch();
	$stmt->close();	

	// Get the user's contact details
	$stmt = $con->prepare('SELECT phoneNumber, emailAddress FROM contact_details_tb WHERE contact_ID = ?');
	$stmt->bind_param('i', $fk_cont_ID);
	$stmt->execute();
	$stmt->bind_result($phone_num, $email);
	$stmt->fetch();
	$stmt->close();



	// Get the user's biography
	// To get this we first need the bio_ID
	$stmt = $con->prepare('SELECT fk_bio_ID FROM profile_tb WHERE fk_profile_ID = ?');
	$stmt->bind_param('i', $fk_profile_ID);
	$stmt->execute();
	$stmt->bind_result($bio_ID);
	$stmt->fetch();
	$stmt->close();
	// Now get the biography
	$stmt = $con->prepare('SELECT bio FROM biography_tb WHERE bio_ID = ?');
	$stmt->bind_param('i', $bio_ID);
	$stmt->execute();
	$stmt->bind_result($bio);
	$stmt->fetch();
	$stmt->close();

	?>
	<div class="content">
		<p>Ello!</p>

	<p>
		<?php
		echo "Username: ".$uname."<br> Firstname: ".$fname."<br> Surname: ".$sname."<br> Date Of Birth: ".$dob."<br> Address_ID: ".$fk_add_ID."<br> Contact_ID: ".$fk_cont_ID."<br> Phone Number: ".$phone_num."<br> Email: ".$email;
		?>
	</p>
	<form action="change_bio.php" method="post">
	<p>
		Biography
		<textarea name="users_bio" rows="10" cols="30"><?php echo $bio?></textarea> 

	</p>

		<button name="changeBio" type="submit" value="changeBio">
			Change biography
		</button>
	</form>
	</div>	
	
	</body>
</html>	