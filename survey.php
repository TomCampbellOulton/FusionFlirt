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
if ($stmt = $con->prepare('SELECT Question_ID, Question_Number, Question_Section_Letter, fk_Question_Group_ID, Response_Type FROM questions_tb WHERE fk_Question_Group_ID = ?')) {
    // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
    $stmt->bind_param('s', $_POST['survey']);
    $stmt->execute();
    // Store the result so we can check if the account exists in the database.
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($q_num, $q_letter, $resp_type);
        $stmt->fetch();
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
				if ($stmt = $con->prepare('SELECT Question_ID, Question_Number, Question_Section_Letter, Response_Type FROM questions_tb WHERE fk_Question_Group_ID = ?')) {
                    $stmt->bind_param('i', $_SESSION['survey_ID']);
					$stmt->execute();
				
				$result = $stmt->get_result();
				foreach ($result as $row){
                    echo $row;
                }
                    //<!-- Iterates through all answers -->
            }
                ?>
                <!-- Submit Button -->
                <input type="submit" value="Submit">
		</div>
	</body>
</html>
