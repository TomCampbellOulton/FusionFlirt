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

// Create a function to perform merge sort
function mergeSort(&$array, $attribute) {
    $length = count($array);

    if ($length <= 1) {
        return;
    }

    $mid = (int)($length / 2);

    // Divide the array into two halves
    $left = array_slice($array, 0, $mid);
    $right = array_slice($array, $mid);

    // Recursively sort the two halves
    mergeSort($left, $attribute);
    mergeSort($right, $attribute);

    // Merge the sorted halves
    merge($array, $left, $right, $attribute);
	// Return the array
	return $array;
}

function merge(&$array, $left, $right, $attribute) {
    $i = $j = $k = 0;

    while ($i < count($left) && $j < count($right)) {
        if ($left[$i]->$attribute <= $right[$j]->$attribute) {
            $array[$k] = $left[$i];
            $i++;
        } else {
            $array[$k] = $right[$j];
            $j++;
        }
        $k++;
    }

    // Copy the remaining elements of $left, if any
    while ($i < count($left)) {
        $array[$k] = $left[$i];
        $i++;
        $k++;
    }

    // Copy the remaining elements of $right, if any
    while ($j < count($right)) {
        $array[$k] = $right[$j];
        $j++;
        $k++;
    }
}


// Create a class for the matches
class UserMatch {
	// Define the public attributes
	public $match_ID;
	public $user_ID;
	public $score;
	public $user1_or_user2;

	// Create a class to set all the values upon class being called
	public function __construct($match_ID, $user_ID, $score, $user1_or_user2){
		$this->match_ID = $match_ID;
		$this->user_ID = $user_ID;
		$this->score = $score;
		$this->user1_or_user2 = $user1_or_user2;
	}

	// Create a public function to set the match_ID
	public function set_match_ID($match_ID){
		// Set the score of this user
		$this->match_ID = $match_ID;
	}
	// Create a public function to get match ID
	public function get_match_ID(){
		// Return the score of this user
		return $this->match_ID;
	}

	// Create a public function to set the ID of the user
	public function set_user_ID($user_ID){
		// Set the score of this user
		$this->user_ID = $user_ID;
	}
	// Create a public function to get the ID of the user
	public function get_user_ID(){
		// Return the score of this user
		return $this->user_ID;
	}

	// Create a public function to set the score of the user
	public function set_score($score){
		// Set the score of this user
		$this->score = $score;
	}
	// Create a public function to get the score of the user
	public function get_score(){
		// Return the score of this user
		return $this->score;
	}

	// Create a public function to set if user 1 or 2
	public function set_user1_or_user2($user1_or_user2){
		// Set the score of this user
		$this->user1_or_user2 = $user1_or_user2;
	}
	// Create a public function to get if user 1 or 2
	public function get_user1_or_user2(){
		// Return the score of this user
		return $this->user1_or_user2;
	}
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
			

			<p>
			<?php
			


			// Checks how many matches there were
			$stmt = $con->prepare('SELECT match_ID, fk_user1_ID, fk_user2_ID, user1Responded, user2Responded, score FROM matches_tb WHERE fk_user1_ID = ? OR fk_user2_ID = ?');
			$stmt->bind_param('ii', $user_ID, $user_ID);
			$stmt->execute();
			$stmt->bind_result($match_ID, $user1_ID, $user2_ID, $user1Responded, $user2Responded, $score);

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
					$matches[] = new UserMatch($match_ID, $matches_ID, $score, $u1Or2);
				}
			}
			$stmt->close();

			// Sort the matches with the highest score at the top of the stack
			mergeSort($matches, 'score');

			if (sizeof($matches) > 0){// If there is more than 1 match in the stack, start iterating through the stack
				$new_match = array_pop($matches);
				// Display this match
				$matches_user_ID = $new_match->get_user_ID();
				echo 'users ID = '.$matches_user_ID;
				// User 1 or User 2
				$u1Or2 = $new_match->get_user1_or_user2();

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
				<button type='submit' value='<?php echo $new_match->match_ID; ?>' name='respond'>Reject</button>
				</form>
				<form method='POST' action='accept.php'>
				<button type='submit' value='<?php echo $new_match->match_ID; ?>' name='respond'>Accept</button>
				<!-- Add a hidden input field to include the id attribute -->
				<input type='hidden' value='<?php echo $u1Or2; ?>' name='user1Or2'>
				<input type='hidden' value='<?php echo $matches_user_ID; ?>' name='other_user_ID'>
				</form>
				<?php

			} else{ // There are no matches
				echo "You currently have no matches.";
			}
			
			?>
			</p>
		</div>
	</body>

</html>