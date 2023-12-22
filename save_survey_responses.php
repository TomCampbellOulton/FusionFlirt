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
		// Iterate the _POST data
		foreach ($_POST as $key=>$value){
			// In case of checkboxes instead of just radio buttons, check if the value is "on"
				?><br><?php
				echo "Key:".$key."- Value:".$value.";"; 

				// Insert into the user's responses tb
				$stmt = $con->prepare('INSERT INTO users_response_tb (fk_user_ID, fk_answer_ID, users_response) VALUES (?, ?, ?)');
				$stmt->bind_param('iis', $user_ID, $key, $value);
				$stmt->execute();
				$stmt->close();
				
			
			
			
			
		}
		echo "Provided responses: " . implode(", ", $allResponses);

		?>
	</p>



</html>