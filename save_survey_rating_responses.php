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
				<h1>Website Title</h1>
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
		The data being collected from you bae 😙
		<?php
		// The user's ID
		$user_ID = $_SESSION['id']; 
		// Temporary array to store responses
		$allResponses = array();
        // Create two arrays to be stored in the database
        $radio_inputs = array();
        $range_inputs = array();
		// Iterate the _POST data
		foreach ($_POST as $key=>$value){
			// In case of checkboxes instead of just radio buttons, check if the value is "on"
				?><br><?php

                
				// EITHER, the responses will be in ranges OR as a single value out of 7
                if (str_contains($key, "rating-") && $value !== ""){
                    echo "Question ID ".trim($key, "rating-")." With Value ".$value.";";
                    $q_ID = intval(trim($key, "rating-"));
                    $radio_inputs[$q_ID] = $value;

                }else if (str_contains($key, "lower_bound-") && $value !== ""){
                    echo "Question ID ".trim($key, "lower_bound-")." With Value ".$value.";";
                    $q_ID = intval(trim($key, "lower_bound-"));
                    $range_inputs[$q_ID]["lower_bound"] = $value;
                    
                }else if (str_contains($key, "median_bound-") && $value !== ""){
                    echo "Question ID ".trim($key, "median_bound-")." With Value ".$value.";";
                    $q_ID = intval(trim($key, "median_bound-"));
                    $range_inputs[$q_ID]["median_bound"] = $value;

                }else if (str_contains($key, "upper_bound-") && $value !== ""){
                    echo "Question ID ".trim($key, "upper_bound-")." With Value ".$value.";";
                    $q_ID = intval(trim($key, "upper_bound-"));
                    $range_inputs[$q_ID]["upper_bound"] = $value;

                }else {
                    echo "An error has occurred";
                }
				
        }
        // Access data from $radio_inputs
        foreach ($radio_inputs as $q_ID => $value) {
            echo "Radio Input - Question ID: $q_ID, Value: $value<br>";
            $stmt = $con->prepare('INSERT INTO users_wants_radio_tb (fk_user_ID, fk_answer_ID, answer_rating) VALUES (?, ?, ?)');
            $stmt->bind_param('iii', $user_ID, $q_ID, $value);
            $stmt->execute();
            $stmt->close();
        }

        // Access data from $range_inputs
        foreach ($range_inputs as $q_ID => $range_data) {
            echo "Range Input - Question ID: $q_ID, Lower Bound: " . $range_data['lower_bound'] . ", Median Bound: " . $range_data['median_bound'] . ", Upper Bound: " . $range_data['upper_bound'] . "<br>";
            $stmt = $con->prepare('INSERT INTO users_wants_ranges_tb (fk_user_ID, fk_answer_ID, upper_bound, lower_bound, median_bound) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('iiiii', $user_ID, $q_ID, $range_data['upper_bound'], $range_data['lower_bound'], $range_data['median_bound']);
            $stmt->execute();
            $stmt->close();
        }
			
			
		

		?>
	</p>



</html>