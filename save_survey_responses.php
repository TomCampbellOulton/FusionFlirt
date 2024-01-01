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
		// Iterate the _POST data
		foreach ($_POST as $question_ID=>$ans_ID){
			echo "<br>";
			echo "QID = ".$question_ID." - AID = ".$ans_ID;

			// Check if the user has already responded to that question
			$stmt = $con->prepare('SELECT users_response FROM users_response_tb WHERE fk_user_ID = ? AND fk_answer_ID = ?');
			$stmt->bind_param('ii', $user_ID, $ans_ID);
			$stmt->execute();
			$stmt->bind_result($response);
			$stmt->fetch();
			// Stores the state to store
			$state = 'on';
			// If the user has already responded
			if ($stmt->num_rows > 0) {
				$stmt->close();
				// Update the users response
				$stmt = $con->prepare('UPDATE users_response_tb SET users_response = ? WHERE fk_user_ID = ? AND fk_answer_ID = ?)');
				$stmt->bind_param('sii', $state, $user_ID, $ans_ID);
				$stmt->execute();
				$stmt->close();

			} else { // The user has not responded to that question
				$stmt->close();
				// Insert into the user's responses tb
				$stmt = $con->prepare('INSERT INTO users_response_tb (fk_user_ID, fk_answer_ID, users_response) VALUES (?, ?, ?)');
				$stmt->bind_param('iis', $user_ID, $ans_ID, $state);
				$stmt->execute();
				$stmt->close();
			}
				
		}

		?>
	</p>



</html>