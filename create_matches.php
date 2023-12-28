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
	$possible_matches[] = $all_user_IDs;
	$rating_of_possible_matches['$all_user_IDs'] = array(0,0,0,0,0,0,0);// Number of times meeting the ratings 1-7
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
			// Inside the loop, store values in the array
			$users_responses_array[] = array('fk_user_ID' => $fk_user_ID, 'users_response' => $users_response);

		}
		$stmt->close();	

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
					echo 'User was removed for requirements 1 met D:';
				}
			} else if ($row['answer_rating'] === 7){ // Checks if the user requires this response
				$users_who_responded[] = $user['fk_user_ID'];
				echo 'User was added for requirements 7 met:D';
				
			} else{
				$user_rating = $row['answer_rating'];
				// Increments the number of times each user has met requirement x by 1
				$rating_of_possible_matches['$all_user_IDs'][$user_rating]++;
				$users_who_responded[] = $user['fk_user_ID'];
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
	foreach ($possible_matches as $match){
		echo $match;
	}
	//print_r($possible_matches[0]);
}




?>

