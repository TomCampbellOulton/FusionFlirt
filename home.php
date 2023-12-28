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
				<a href="showprofile.php"></i>Testing</a>
				<a href="create_matches.php"></i>Make Me Matches Please c:</a>
			</div>
		</nav>
		<div class="content">
			<h2>Home Page</h2>
			<p>Welcome back, <?=$_SESSION['name']?>!</p>
			<p>Ello!</p>
			<p>Para</p>

			<p>
			<?php
			// The user's ID
			$user_ID = $_SESSION["id"];
			// Check to see if the user has any matches

			$stmt = $con->prepare('SELECT match_ID, fk_user1_ID, fk_user2_ID FROM matches_tb WHERE fk_user1_ID = ? OR fk_user2_ID = ?');
			$stmt->bind_param('ii', $user_ID, $user_ID);
			$stmt->execute();
			$stmt->bind_result($match_ID, $user1_ID, $user2_ID);
			

			$matches = array();
			// Store all the matches in an array
			while ($stmt->fetch()) {
				// Process each match
				$matches[] = array('match_ID'=>$match_ID, 'user1_ID'=>$user1_ID, 'user2_ID'=>$user2_ID);
			}
			$stmt->close();
			// Checks how many matches there were
			if (sizeof($matches) > 0){
				foreach ($matches as $match) {
					echo "Match ID: " . $match['match_ID'] . ", User 1 ID: " . $match['user1_ID'] . ", User 2 ID: " . $match['user2_ID'] . "<br>";
				}
			} else{ // There are no matches
				echo "no matches :c";
			}
			
			?>
			</p>
		</div>
	</body>


</html>