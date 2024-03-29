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
// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
// Get the questions
if ($stmt = $con->prepare('SELECT question_ID, questionText FROM questions_tb WHERE fk_survey_ID = ?')) {
    // Bind parameters (s = string, i = int, b = blob, etc), in our case the question_ID is an integer so we use "i"
    $stmt->bind_param('i', $_POST['survey']);
    $stmt->execute();
	$result = $stmt->get_result();
    if ($result->num_rows > 0) {
        //$stmt->bind_result($q_ID, $q_num, $q_letter, $resp_type, $q_text);
		$quesArr = array();
		foreach ($result as $question){
			// Find which questions need answering
			// Get the answers for each question
			if ($ansStmt = $con->prepare('SELECT answer_ID, answer, questionAnswerLetter, response_type FROM answers_tb WHERE fk_question_ID = ?')) {
				// Bind parameters (s = string, i = int, b = blob, etc), in our case the question_ID is an integer so we use "i"
				$ansStmt->bind_param('i', $question['question_ID']);
				$ansStmt->execute();
				$ansResult = $ansStmt->get_result();
				if ($ansResult->num_rows > 0) {
					//$stmt->bind_result($q_ID, $q_num, $q_letter, $resp_type, $q_text);
					$ansArr = array();
					if ($ansResult->num_rows > 0){
						foreach ($ansResult as $answer){
							$ansArr[] = $answer;
						}
					}
				}
					
			}
			$quesArr[] = array('question'=>$question,'answers'=>$ansArr);
		}
	}
        
}

session_regenerate_id();
$_SESSION['survey_ID'] = $_POST['survey'];
$users_ID = $_SESSION['id'];
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
			<!-- Display the topic of the survey !-->
			<?php
			$survey_ID = $_POST['survey'];
			$survey_topic = $_POST[$survey_ID];?>
			<h2><?php echo $survey_topic ?></h2>
			
				<a href="rate_partner_responses.php"><i class="fas fa-sign-out-alt"></i>rate_partner_responses</a>
				<form action="save_survey_responses.php" method="post">
				<?php
				// Default question number is 1 for the first question
				$q_num = 1;
				// Iterates through the array quesArr to find each question
				foreach ($quesArr as $item){
					
					// Split the $item into two components: question and answers
					$question = $item['question'];
					$answers = $item['answers'];
					// Split up the components of that question :)
					$q_text = $question["questionText"];
					$q_ID = $question["question_ID"];
					?>
					<!-- Creates the fieldset within the main form for that question -->
					<fieldset>

					<!-- Writes what the question is above the new fieldset -->
					<legend><?php echo $q_num?>. <?php echo $q_text?></legend>

					<?php
					// Write out all the answers to the question :)
					foreach ($answers as $ans){
						$default = False;
						$response = '';
						// Get the response text and the response type
						$a_text = $ans['answer'];
						$resp_type = $ans['response_type'];
						$ans_ID = $ans['answer_ID'];
						// Get the users response to the question (if it exists)
						// Check if the user has responded or not
						$stmt = $con->prepare('SELECT usersResponse FROM users_response_tb WHERE fk_user_ID = ? AND fk_answer_ID = ?');
						$stmt->bind_param('ii',$users_ID, $ans_ID);
						$stmt->execute();
						$stmt->bind_result($response);
						$stmt->fetch();
						// Check if there was a response
						if (isset($response) && $response !== ''){// If there was a response, save it
							// If it's a radio or checkbox input, default is 1
							if ($resp_type === "checkbox" or $resp_type === "radio"){
								$default = True;
							}else{// otherwise record what's stored in teh database
								$default = $response;
							}
						} else {// If there wasn't a response, set default to off
							$default = False;
						}
						
						// Close the statement
						$stmt->close();

						?>
						
						<!-- Checks if the response type for the input will be checkbox, radio or input -->
						<?php 
						if ($resp_type === "radio"){?>
							<!-- Creates the appropraite response type for each response and adds them to the form -->
							<!-- For checkboxes or radio inputs, don't record the "response text" just the ID of the response -->
							<!-- IF Checked, check otherwise don't -->
							
							<input type="<?php echo $resp_type?>" id="<?php echo $ans_ID?>" value="<?php echo $ans_ID?>" name="<?php echo $q_ID?>" <?php if($default){echo 'checked';} ?>>
							<?php
						}else if ($resp_type === "checkbox"){?>
							<input type="<?php echo $resp_type?>" id="<?php echo $ans_ID?>" value="<?php echo $ans_ID?>" name="<?php echo $q_ID?>" <?php if($default){echo 'checked';} ?>>
							<?php
						}else{?>
							<!-- Creates the appropraite response type for each response and adds them to the form -->
							<!-- For inputs, record the "response text". Default Value will be empty -->
							<input type="<?php echo $resp_type?>" id="<?php echo $ans_ID?>" value="<?php echo $default; ?>" name="<?php echo $q_ID?>">
							    <!-- Add a hidden input field to include the id attribute -->
							<input type="hidden" name="<?php echo $q_ID?>" value="<?php echo $ans_ID?>">
							<?php
							}
						?>	
						<!-- Adds a label to tell the user what each box represents -->
						<label for="<?php echo $ans_ID?>"> <?php echo $a_text?> </label><br>	
						<?php

					}
					// Increase the question number counter
					$q_num ++;
					?>
					
					</fieldset>
					<?php
				}
				?>
			
				<!-- Submit Button -->
				<input type="submit" value="Submit">
				</form>
                
		</div>
	</body>
</html>
