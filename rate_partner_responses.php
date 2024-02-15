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
			<h2>Rate Partner Responses Page</h2>

		</div>
	</body>
	<p>
	<p>
		Time to provide your requriements for a partner :D
	</p>

	<?php
	if ($stmt = $con->prepare('SELECT question_ID, questionText FROM questions_tb WHERE fk_survey_ID = ?')) {
    // Bind parameters (s = string, i = int, b = blob, etc), in our case the question_ID is an integer so we use "i"
    $stmt->bind_param('i', $_SESSION['survey_ID']);
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
	?>
	<form action="save_survey_rating_responses.php" method="post">
	<?php
	$q_num = 0;
	// Iterates through the array quesArr to find each question
	foreach ($quesArr as $item){
		
		// Split the $item into two components: question and answers
		$question = $item['question'];
		$answers = $item['answers'];
		// Split up the components of that question :)
		$q_text = $question["questionText"];
		$q_num ++;
		$q_ID = $question["question_ID"];
		?>
		<!-- Creates the fieldset within the main form for that question -->
		<fieldset>

		<!-- Writes what the question is above the new fieldset -->
		<legend><?php echo $q_num?>. <?php echo $q_text?></legend>

		<?php
		// Write out all the answers to the question :)
		foreach ($answers as $ans){
			$a_text = $ans['answer'];
			$resp_type = $ans['response_type'];
			$ans_ID = $ans['answer_ID'];
			?>
			


            <!-- Checks if the response type for the input will be checkbox, radio or input -->
            <?php 
			if ($resp_type === "radio" or $resp_type === "checkbox"){?>
				<!-- Creates the appropraite response type for each response and adds them to the form -->
				<!-- For checkboxes or radio inputs, rate each response 1-7 -->
				<input type="input" id="rating-<?php echo $ans_ID?>" value="" name="rating-<?php echo $ans_ID?>" placeholder="rating of 7">
                <!-- Adds a label to tell the user what each box represents -->
                <label for="rating-<?php echo $ans_ID?>"> <?php echo $a_text?> </label>	
				<?php
                
			}else{?>
				<!-- Creates the appropraite response type for each response and adds them to the form -->
				<!-- For inputs, provide a lower, median and upper bound for acceptable responses -->
				<input type="input" id="lower_bound-<?php echo $ans_ID?>" value="" name="lower_bound-<?php echo $ans_ID?>" placeholder="lower-bound">
				<input type="input" id="median_bound-<?php echo $ans_ID?>" value="" name="median_bound-<?php echo $ans_ID?>" placeholder="median-bound">
				<input type="input" id="upper_bound-<?php echo $ans_ID?>" value="" name="upper_bound-<?php echo $ans_ID?>" placeholder="upper-bound">
                <!-- Adds a label to tell the user what each box represents -->
                <label for="lower_bound-<?php echo $ans_ID?>"> <?php echo $a_text?> </label>	
                <label for="median_bound-<?php echo $ans_ID?>"> <?php echo $a_text?> </label>	
                <label for="upper_bound-<?php echo $ans_ID?>"> <?php echo $a_text?> </label>	
				<?php
				}
			?><br><?php
		}
		?>
		
		</fieldset>
		<?php
	}
	// Now all the forms are complete, the final form needs to be closed
	?>

	<!-- Submit Button -->
	<input type="submit" value="Submit">
	</form>
