<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// Change this to your connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'fusion_flirt_db';
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}
session_regenerate_id();
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
			<p>
				For Admins Only!!
				Change/add/remove questions!
				<form action="select_survey_to_edit.php" method="post">
				<button name="select_survey_to_edit" type="submit" value="ello"> 
					<!-- Text written here goes INSIDE the buttons box-->
					Select A Survey To Alter
				</button>
				</form>
					
			</p>
			<form action="survey.php" method="post">
				<?php
				if ($stmt = $con->prepare('SELECT survey_ID, surveyTopic FROM survey_tb')) {
					$stmt->execute();
				
					$result = $stmt->get_result();
					foreach ($result as $row){
					?><button name="survey" type="submit" value="<?php echo $row['survey_ID'] ?>"> 
					<?php echo $row['surveyTopic'] ?> 
					</button>
					<?php
					}
				}?>
			</form>
		</div>
	</body>
</html>