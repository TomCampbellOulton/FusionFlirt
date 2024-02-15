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
// The user's id
$user_ID = $_SESSION['id'];

// Find all the questions in this survey
$questions = array();
$survey_ID = $_SESSION['survey_ID'];
$stmt = $con->prepare('SELECT question_ID FROM questions_tb WHERE fk_survey_ID = ?');
$stmt->bind_param('i', $survey_ID);
$stmt->execute();
$stmt->bind_result($q_ID);
while ($stmt->fetch()){// Add the ID of all the questions in the survey to an array
	$questions[] = $q_ID;
}
$stmt->close();

// Find all the answers for each question
$answers = array();
foreach ($questions as $question){
	$stmt = $con->prepare('SELECT answer_ID FROM answers_tb WHERE fk_question_ID = ?');
	$stmt->bind_param('i', $question);
	$stmt->execute();
	$stmt->bind_result($a_ID);
	while ($stmt->fetch()){// Add the ID of all the answers in the question to an array
		// This saves each answer in an array of each question and the answer. 
		$answers[] = array('q_ID'=>$q_ID, 'a_ID'=>$a_ID);
	}
	$stmt->close();
}

// Create a search function
function in_my_array($needle, $haystack){
	// Assume not found
	$found = False;
	foreach($haystack as $row){// For each entry in the haystack
		if ($needle === $row['a_ID']){
			$found = True;
		}
	}
	return $found;
}

// Create an array of all the responses the user has currently provided for this survey
$users_answers = array();
$stmt = $con->prepare('SELECT usersResponse, fk_answer_ID, response_ID FROM users_response_tb WHERE fk_user_ID = ?');
$stmt->bind_param('i', $user_ID);
$stmt->execute();
$stmt->bind_result($response, $fk_answer_ID, $response_ID);
while ($stmt->fetch()){
	// If the response is from the survey we are saving
	if (in_my_array($fk_answer_ID, $answers)) {
		// If the response is not "off"
		if ($response !== 'off'){
			$users_answers[] = array('ans_ID'=>$fk_answer_ID, 'response_ID'=>$response_ID);
		}
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
	</body>
	<p>
		<?php
		// Record the responses
		$new_responses = array();
		// Iterate the _POST data
		foreach ($_POST as $question_ID=>$ans_ID){
			$new_responses[] = $ans_ID;
			// Check if the user has already responded to that question
			$stmt = $con->prepare('SELECT usersResponse FROM users_response_tb WHERE fk_user_ID = ? AND fk_answer_ID = ?');
			$stmt->bind_param('ii', $user_ID, $ans_ID);
			$stmt->execute();
			$stmt->store_result();
			// Stores the state to store
			$state = 'on';
			// If the user has already responded
			if ($stmt->num_rows > 0) {
				$stmt->close();
				// Update the users response
				$stmt = $con->prepare('UPDATE users_response_tb SET usersResponse = ? WHERE fk_user_ID = ? AND fk_answer_ID = ?');
				$stmt->bind_param('sii', $state, $user_ID, $ans_ID);
				$stmt->execute();
				$stmt->close();

			} else { // The user has not responded to that question
				$stmt->close();
				// Insert into the user's responses tb
				$stmt = $con->prepare('INSERT INTO users_response_tb (fk_user_ID, fk_answer_ID, usersResponse) VALUES (?, ?, ?)');
				$stmt->bind_param('iis', $user_ID, $ans_ID, $state);
				$stmt->execute();
				$stmt->close();
			}
				
		}
		// Check how many responses were not recorded this time
		foreach ($users_answers as $ans_ID){
			if (in_array($ans_ID['ans_ID'], $new_responses) === False){// If the old response is not being recorded anymore
				// Remove it from the database
				$stmt = $con->prepare('DELETE FROM users_response_tb WHERE response_ID = ?');
				$stmt->bind_param('i', $ans_ID['response_ID']);
				$stmt->execute();
				$stmt->close();
			}
		}
	
		?>
	</p>



</html>