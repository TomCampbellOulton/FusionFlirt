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

			$stmt = $con->prepare('SELECT match_ID, fk_user1_ID, fk_user2_ID FROM matches_tb WHERE fk_user1_ID = ? OR fk_user2_ID = ? ORDER BY score ASC');
			$stmt->bind_param('ii', $user_ID, $user_ID);
			$stmt->execute();
			$stmt->bind_result($match_ID, $user1_ID, $user2_ID);
			

			$matches = array();
			// Store all the matches in an array
			while ($stmt->fetch()) {
				// Process each match
				// Store all the matches in a stack

				// Find the ID of the other user in the match
				if ($user1_ID === $user_ID){
					// Who the other person is
					$matches_ID = $user2_ID;
					// Which 'user' (1 or 2) is the user
					$u1Or2 = 'user1';
				}else {
					// Who the other person is
					$matches_ID = $user1_ID;
					// Which 'user' (1 or 2) is the user
					$u1Or2 = 'user2';
				}

				$matches[] = array('match_ID'=>$match_ID, 'users_ID'=>$matches_ID, 'user1OrUser2'=>$u1Or2);
			}
			$stmt->close();
			// Checks how many matches there were
			if (sizeof($matches) > 0){// If there is more than 1 match in the stack, start iterating through the stack
				$new_match = array_pop($matches);
				// Display this match
				$matches_user_ID = $new_match['users_ID'];
				echo 'users ID = '.$matches_user_ID;

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
				

				// Add the accept and reject buttons
				?>
				<form method='POST'>
					<button type='submit' value='reject' name='respond'>Reject</button>
					<button type='submit' value='accept' name='respond'>Accept</button>
				</form>




				<?php
				if (isset($_POST['respond'])){ // If the user has clicked on accept or decline

					// The match ID
					$match_ID = $new_match['match_ID'];

					// If the user accepted
					if ($_POST['respond'] === 'accept'){
						// User 1 or 2
						$user1Or2 = $new_match['user1OrUser2'];
						// Responded or not
						$responded = True;
						// Accept=Tr or decline
						$accept = True;

						if ($user1Or2 === 'user1'){ // If it's user 1
							$stmt = $con->prepare('UPDATE matches_tb  SET user1Responded = ?, accepted = ? WHERE fk_user1_ID = ?');
							echo $responded, $accept, $user_ID;
							// If the user has accepted, add this to the database
							$stmt->bind_param('iii', $responded, $accept, $user_ID);
							$stmt->execute();
							$stmt->close();

						}else{// Otherwise they're user 2
							$stmt = $con->prepare('UPDATE matches_tb  SET user2Responded = ?, accepted = ? WHERE fk_user2_ID = ?');

							// If the user has accepted, add this to the database
							$stmt->bind_param('iii', $responded, $accept, $user_ID);
							$stmt->execute();
							$stmt->close();
						}
						
					}else{ // The user declined
						$stmt = $con->prepare('DELETE FROM matches_tb  WHERE match_ID = ?');

						// If the user has accepted, add this to the database
						$stmt->bind_param('i', $match_ID);
						$stmt->execute();
						$stmt->close();
					}
				}
			} else{ // There are no matches
				echo "no matches :c";
			}
			
			?>
			</p>
		</div>
	</body>

	
				<?php
				if ($stmt = $con->prepare('SELECT survey_ID, surveyTopic FROM survey_tb')) {
					$stmt->execute();
				
					$result = $stmt->get_result();
					foreach ($result as $row){
					?><button name="survey" type="submit" value="<?php echo $row['survey_ID'] ?>"> 
					<?php echo $row['surveyTopic'] ?> 
					</button>
					<?php
					}
				}?>
			</form>
</html>