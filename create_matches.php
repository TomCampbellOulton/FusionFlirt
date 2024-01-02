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

$user_ID = $_SESSION["id"];

// Get the user's requirements
$users_wants_radio = array();
$users_wants_ranges = array();

// from tables users_wants_radio_tb and users_wants_ranges_tb
$stmt = $con->prepare('SELECT fk_answer_ID, answer_rating FROM users_wants_radio_tb WHERE fk_user_ID = ?');
$stmt->bind_param('i', $user_ID);
$stmt->execute();
$stmt->bind_result($fk_ans_ID_radio, $answer_rating);
while ($stmt->fetch()) {
    // Inside the loop, store values in the array
    $users_wants_radio[] = array('fk_ans_ID_radio' => $fk_ans_ID_radio, 'answer_rating' => $answer_rating);
}
$stmt->close();

$stmt = $con->prepare('SELECT fk_answer_ID, upper_bound, lower_bound, median_bound FROM users_wants_ranges_tb WHERE fk_user_ID = ?');
$stmt->bind_param('i', $user_ID);
$stmt->execute();
$stmt->bind_result($fk_ans_ID_ranges, $upper_bound, $lower_bound, $median_bound);
$stmt->fetch();
while ($stmt->fetch()) {
    // Inside the loop, store values in the array
    $users_wants_ranges[] = array('fk_ans_ID_ranges' => $fk_ans_ID_ranges, 'upper_bound' => $upper_bound, 'lower_bound' => $lower_bound, 'median_bound' => $median_bound);
}
$stmt->close();	

// Create a list of all possible matches
$possible_matches = array();
$rating_of_possible_matches = array();

$stmt = $con->prepare('SELECT user_ID FROM users_tb');
$stmt->execute();
$stmt->bind_result($all_user_IDs);
while ($stmt->fetch()){
	// Make sure the possible match isn't themself as they can't match with themself
	if ($all_user_IDs !== $user_ID){
		$possible_matches[] = $all_user_IDs;
		$rating_of_possible_matches['$all_user_IDs'] = array(0,0,0,0,0,0,0);// Number of times meeting the ratings 1-7
	}
}
$stmt->close();

// Start off with the radio inputs
// Checks how many there are 
$user_scores = array();
if (sizeof($users_wants_radio) > 0){
	foreach ($users_wants_radio as $row){// Checks each rating the user has provided
		// To record which users have responded to the question, to veto any who havent
		$users_who_responded = array();
		// Create an array to store the responses for the users
		$users_responses_array = array();
		// Check each user's response to that question
		$stmt = $con->prepare('SELECT fk_user_ID, users_response FROM users_response_tb WHERE fk_answer_ID = ?');
		$stmt->bind_param('i', $row['fk_ans_ID_radio']);
		$stmt->execute();
		$stmt->bind_result($fk_user_ID, $users_response);


		while ($stmt->fetch()) {
			// Make sure the user isn't themself
			if ($fk_user_ID!==$user_ID){
				// Check if this user has been rejected already
				if (in_array($fk_user_ID, $possible_matches)){
					// Inside the loop, store values in the array
					$users_responses_array[] = array('fk_user_ID' => $fk_user_ID, 'users_response' => $users_response);
				}
			}
		}
		$numberOfResponses = $stmt->num_rows();
		$stmt->close();	


		// Only remove IF there are responses
		if ($numberOfResponses > 0){
			foreach ($users_responses_array as $user){
				// Checks if the user will reject people if they have it
				if ($row['answer_rating'] === 1){ // Remove the user
					// Find the key of the user ID to remove
					$key = array_search($user['fk_user_ID'], $possible_matches);
					// Check if the user ID was found in the array
					if ($key !== false) {
						// Remove the user ID from the array
						unset($possible_matches[$key]);
						// Reindex the array after removing an element
						$possible_matches = array_values($possible_matches);
					}
				} else if ($row['answer_rating'] === 7){ // Checks if the user requires this response
					$users_who_responded[] = $user['fk_user_ID'];
					
				} else{
					$user_rating = $row['answer_rating'];
					// Increments the number of times each user has met requirement x by 1
					$rating_of_possible_matches['$all_user_IDs'][$user_rating - 1]++;
					$users_who_responded[] = $user['fk_user_ID'];
				}
			}
			// Removes anyone who didn't meet the requirements
			foreach ($possible_matches as $user){
				if (in_array($user, $users_who_responded) === False){
					// Remove the user
					// Find the key of the user ID to remove
					$key = array_search($user, $possible_matches);
					// Check if the user ID was found in the array
					if ($key !== false) {
						// Remove the user ID from the array
						unset($possible_matches[$key]);
						// Reindex the array after removing an element
						$possible_matches = array_values($possible_matches);
					}
				}
			}
		}
		}
		

	echo '<br>';

	// Create an array to store the number of times that the user meets each rating
	$users_rating = array(0,0,0,0,0,0,0);// Number of times meeting the ratings 1-7
	// Create a variable to check if the user has been rejected yet
	$rejected = False;
	// Here are all the people who meet the user's requirements
	foreach ($possible_matches as $match){
		echo '<br>We have found you a match! Their ID is: '.$match;
		// Now to check if the user meets their requirements


		// Get all of their requirements
		// from tables users_wants_radio_tb and users_wants_ranges_tb
		$stmt = $con->prepare('SELECT fk_answer_ID, answer_rating FROM users_wants_radio_tb WHERE fk_user_ID = ?');
		$stmt->bind_param('i', $match);
		$stmt->execute();
		$stmt->bind_result($fk_ans_ID_radio, $answer_rating);
		while ($stmt->fetch()) {
			// Inside the loop, store values in the array
			$users_wants_radio[] = array('fk_ans_ID_radio' => $fk_ans_ID_radio, 'answer_rating' => $answer_rating);
		}
		$stmt->close();

		$stmt = $con->prepare('SELECT fk_answer_ID, upper_bound, lower_bound, median_bound FROM users_wants_ranges_tb WHERE fk_user_ID = ?');
		$stmt->bind_param('i', $match);
		$stmt->execute();
		$stmt->bind_result($fk_ans_ID_ranges, $upper_bound, $lower_bound, $median_bound);
		$stmt->fetch();
		while ($stmt->fetch()) {
			// Inside the loop, store values in the array
			$users_wants_ranges[] = array('fk_ans_ID_ranges' => $fk_ans_ID_ranges, 'upper_bound' => $upper_bound, 'lower_bound' => $lower_bound, 'median_bound' => $median_bound);
		}
		$stmt->close();	

		// Variable to keep the loop runnning whilst the user's responses are being checked
		$completed = False;
		// Now get the user's responses to all of these questions
		//Starting wth the radio inputs
		// Only run whilst the user has NOT been rejected AND they haven't met all requirements
		while (($rejected === False) && ($completed === False) ){
			foreach ($users_wants_radio as $req){
				$ans_ID = $req['fk_ans_ID_radio'];
				$rating = $req['answer_rating'];
				
				// Get the user's response
				$stmt = $con->prepare('SELECT users_response FROM users_response_tb WHERE fk_answer_ID = ? AND fk_user_ID = ?');
				$stmt->bind_param('ii', $ans_ID, $user_ID);
				$stmt->execute();
				$stmt->bind_result($users_response);
				
				// If the rating is 7 and the user didn't respond, they will be rejected
				// Variable to record if the user has responded
				$responded = False;
				// There should only be one response here
				while ($stmt->fetch()) {
					// Checks the user's response (on means it's ticked, off means unticked/removed)
					if ($users_response === 'on'){
						// Mark responded as true to signify the user responding
						$responded = True;

						// Check the requirement the user must meet
						if ($rating === 1){// If the user has responded they should be rejected
							$rejected = True;
						}else{
							// Increment the user's count for number of times meeting that requirement
							$users_rating[$rating - 1] ++;
						}
					}
				}
				// If the user didn't respond but a 7 was the requirement, the user shall be rejected
				if ($rating === 7 && $responded === False){
					$rejected = True;
				}

				$stmt->close();
			}
			// Now that all the users radio requirements have bene checked, the check can be marked as completed
			$completed = True;
		}
		if ($rejected === False){
			echo '<br>YOU ARE A MATCH!!!';
			// Check if they have already "matched"
			// Assume they haven't matched yet
			$matched = False;
			$stmt = $con->prepare('SELECT user1Responded, user2Responded, accepted FROM matches_tb WHERE fk_user1_ID = ? AND fk_user2_ID = ?');
			$stmt->bind_param('ii', $match, $user_ID);
			$stmt->execute();
			$stmt->bind_result($match_responded, $user_reponded, $accepted);
			while ($stmt->fetch()) {
				$matched = True;
			}
			// Now check to see if they have matched with the other user having initiated the matching process
			$stmt = $con->prepare('SELECT user1Responded, user2Responded, accepted FROM matches_tb WHERE fk_user1_ID = ? AND fk_user2_ID = ?');
			$stmt->bind_param('ii', $user_ID, $match);
			$stmt->execute();
			$stmt->bind_result($user_responded, $match_responded, $accepted);
			while ($stmt->fetch()) {
				$matched = True;
			}

			if ($matched !== True){
				// As neither user has responded to this match yet, set it to default of False
				$default_response = False;
				$default_response_for_acceptance = Null;
				// Add the match to the matches table
				$stmt = $con->prepare('INSERT INTO matches_tb (fk_user1_ID, fk_user2_ID, user1Responded, user2Responded, accepted) VALUES (?, ?, ?, ?, ?)');
				$stmt->bind_param('iibbb', $user_ID, $match, $default_response, $default_response, $default_response_for_acceptance);
				$stmt->execute();
				$stmt->close();
			}else{
				// Check if both of them have already responded
				if ($user_responded === True && $match_responded === True){ // They have both responded
					echo 'You have both already matched with this person. ';
					// Check if they've accepted or declined
					if ($accepted === True){
						echo 'You have both accepted so can now communicate :D';
					}else {
						echo 'One of you have declined this match and are therefore incompatible. My apologies.';
					}
				}else{// Only one or neither has responded
					// Check who hasn't responded yet
					if ($user_reponded === False){// The user hasn't responded yet
						echo '<br>You need to respond to this match please.';
					}else{// The other person hasn't responded yet
						echo '<br>Please be patient whilst you wait for your matches.';
					}
				}
			}
		} else{
			echo '<br>You were rejected';
		}

	}
	//print_r($possible_matches[0]);
}

header('Location: /dating_App/fusionflirt1.3/home.php');
exit();


?>

