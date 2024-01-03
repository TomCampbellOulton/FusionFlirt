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
				<a href="create_matches.php"></i>Make Me Matches Please c:</a>
				<a href="messages_page.php"></i>Messages ;)</a>
				<a href="surveys_page.php">Surveys Page</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
			<h2>Home Page</h2>
			<p>Welcome back, <?=$_SESSION['name']?>!</p>
			<p>Ello!</p>
			<p>Para</p>

			<p>
			<?php
			


			// Checks how many matches there were
			$stmt = $con->prepare('SELECT match_ID, fk_user1_ID, fk_user2_ID, user1Responded, user2Responded FROM matches_tb WHERE fk_user1_ID = ? OR fk_user2_ID = ? ORDER BY score ASC');
			$stmt->bind_param('ii', $user_ID, $user_ID);
			$stmt->execute();
			$stmt->bind_result($match_ID, $user1_ID, $user2_ID, $user1Responded, $user2Responded);

			// The user's ID
			$user_ID = $_SESSION["id"];
			// Check to see if the user has any matches
			$matches = array();
			// Store all the matches in an array
			while ($stmt->fetch()) {
				// Process each match
				// Assume the user has not responded
				$response = False;
				// Store all the matches in a stack

				// Find the ID of the other user in the match
				if ($user1_ID === $user_ID){
					// Who the other person is
					$matches_ID = $user2_ID;
					// Which 'user' (1 or 2) is the user
					$u1Or2 = 'user1';
					// Record the response
					$response = $user1Responded;
				}else {
					// Who the other person is
					$matches_ID = $user1_ID;
					// Which 'user' (1 or 2) is the user
					$u1Or2 = 'user2';
					// Record the response
					$response = $user2Responded;
				}

				if ($response === 0){ // If the user has not responded yet
					$matches[] = array('match_ID'=>$match_ID, 'users_ID'=>$matches_ID, 'user1OrUser2'=>$u1Or2);
				}
			}
			$stmt->close();

			if (sizeof($matches) > 0){// If there is more than 1 match in the stack, start iterating through the stack
				$new_match = array_pop($matches);
				// Display this match
				$matches_user_ID = $new_match['users_ID'];
				echo 'users ID = '.$matches_user_ID;
				// User 1 or User 2
				$u1Or2 = $new_match['user1OrUser2'];

				// Get their profile ID and other personal details
				$stmt = $con->prepare('SELECT username, firstname, surname, dateOfBirth, fk_contact_ID, fk_profile_ID FROM users_tb WHERE user_ID = ?');
				$stmt->bind_param('i', $matches_user_ID);
				$stmt->execute();
				$stmt->bind_result($uname, $fname, $sname, $dob, $contact_ID, $profile_ID);
				$stmt->fetch();
				$stmt->close();
				echo '<br>Username - '.$uname;
				echo '<br>Firstname - '.$fname;
				echo '<br>Surname - '.$sname;
				echo '<br>Date Of Birth - '.$dob;

				// Get their biography ID
				$stmt = $con->prepare('SELECT fk_bio_ID FROM profile_tb WHERE profile_ID = ?');
				$stmt->bind_param('i', $profile_ID);
				$stmt->execute();
				$stmt->bind_result($biography_ID);
				$stmt->fetch();
				$stmt->close();
				// Get their biography
				$stmt = $con->prepare('SELECT bio FROM biography_tb WHERE bio_ID = ?');
				$stmt->bind_param('i', $biography_ID);
				$stmt->execute();
				$stmt->bind_result($biography);
				$stmt->fetch();
				$stmt->close();	
				echo '<br>Biography - '.$biography;

				// Get their pictures


				// Show the user all their images
				$stmt = $con->prepare('SELECT fk_image_ID FROM images_profile_link_tb WHERE fk_profile_ID = ?');
				$stmt->bind_param('i', $profile_ID);
				$stmt->execute();
				$stmt->store_result();
				$stmt->bind_result($fk_image_ID);
				$image_IDs = ''; // Initialize the string

				while ($stmt->fetch()) {
					$image_IDs .= $fk_image_ID . ','; // Concatenate the image IDs
				}
				
				if ($stmt->num_rows > 0) {
					$stmt->close();
				
					// Remove the trailing comma from $image_IDs
					$image_IDs = rtrim($image_IDs, ',');
				
					// Show these images
					$stmt = $con->prepare('SELECT image, image_name, image_type FROM images_tb WHERE image_ID IN (' . $image_IDs . ')');
					$stmt->execute();
					$stmt->store_result();
					
					while ($stmt->fetch()) {
						// For each image, display it
						$stmt->bind_result($image, $image_name, $image_type);
						echo '<img src="display_image.php?image_ids=' . $image_IDs . '" alt="Image">';

						// Add a button next to it to remove it

					}
				
					$stmt->close();
				}
				?>

				<!-- Add the accept and reject buttons -->
				<form method='POST' action='reject.php'>
				<button type='submit' value='<?php echo $new_match['match_ID']; ?>' name='respond'>Reject</button>
				</form>
				<form method='POST' action='accept.php'>
				<button type='submit' value='<?php echo $new_match['match_ID']; ?>' name='respond'>Accept</button>
				<!-- Add a hidden input field to include the id attribute -->
				<input type='hidden' value='<?php echo $u1Or2; ?>' name='user1Or2'>
				<input type='hidden' value='<?php echo $matches_user_ID; ?>' name='other_user_ID'>
				</form>
				<?php

			} else{ // There are no matches
				echo "no matches :c";
			}
			
			?>
			</p>
		</div>
	</body>

</html>