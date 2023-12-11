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
$DATABASE_NAME = 'dating_app_db';
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
if ($stmt = $con->prepare('SELECT Question_ID, Question_Number, Question_Section_Letter, Response_Type, Question_Text FROM questions_tb WHERE fk_Question_Group_ID = ?')) {
    // Bind parameters (s = string, i = int, b = blob, etc), in our case the question_ID is an integer so we use "i"
    $stmt->bind_param('i', $_POST['survey']);
    $stmt->execute();
	$result = $stmt->get_result();
	echo $result->num_rows;
    if ($result->num_rows > 0) {
        //$stmt->bind_result($q_ID, $q_num, $q_letter, $resp_type, $q_text);
		$quesArr = array();
		foreach ($result as $question){
			$quesArr[] = $question;
		}
	}
        
}
session_regenerate_id();
$_SESSION['survey_ID'] = $_POST['survey'];
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
            <form action="save_survey_responses.php" method="post">
                <!-- First Input -->
                <label for="fname">First Name:</label>
                <input type="text" id="fname" name="fname"><br><br>

                <!-- Second Input -->
                <label for="lname">Last name:</label>
                <input type="text" id="lname" name="lname"><br><br>

                <!-- Iterates through all questions -->
                
				<?php
				// Iterates through the array quesArr to find each question
				foreach ($quesArr as $question){
					// Split up the components of that question :)
					$q_text = $question["Question_Text"];
					$q_letter = $question["Question_Section_Letter"];
					$resp_type = $question["Response_Type"];
					$q_num = $question["Question_Number"];
					$q_ID = $question["Question_ID"];
					// If this is a new question
					if ($q_letter == null){
						?>
						<?php 
						// If this is not the first question, the form needs to be closed
						if ($q_num > 1){
							?> </form> <?php
						}
						?>
						
						<!-- Writes what the question is above the new form -->
						<p><?php echo $q_text?></p>
						<form>
						<?php
					}else{// Otherwise this is a response
						?>
						<!-- Creates the appropraite response type for each response and adds them to the form -->
						<input type="<?php echo $resp_type?>" id="<?php echo $q_ID?>" name="<?php echo $q_num?>" value="<?php echo $q_text?>">
						<!-- Adds a label to tell the user what each box represents -->
						<label for="<?php echo $q_ID?>"> <?php echo $q_text?> </label><br>
						<?php
					}

				}
				// Now all the forms are complete, the final form needs to be closed
				?>
				</form>
                <!-- Submit Button -->
                <input type="submit" value="Submit">
		</div>
	</body>
</html>
