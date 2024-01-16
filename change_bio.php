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
    <?php echo $_POST['users_bio'];
    $bio = $_POST['users_bio'];
    $bio_ID = $_SESSION['bio_ID'];
    $user_ID = $_SESSION['id'];
    $profile_ID = $_SESSION['profile_ID'];

    
    // Checks if the user already has a bio or not
    $stmt = $con->prepare("SELECT * FROM biography_tb WHERE bio_ID = ?");
    $stmt->bind_param('i', $bio_ID);
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if any rows are returned
    if ($result->num_rows > 0) {
        // There is already an entry so edit that entry
        $stmt = $con->prepare('UPDATE biography_tb  SET bio = ? WHERE bio_ID = ?');
        $stmt->bind_param('si', $bio, $bio_ID);
        $stmt->execute();
        $stmt->close();
        echo '<br>trying to change the bio';
        echo '<br>users bio - '.$bio;
        echo '<br>users bio_ID - '.$bio_ID;

    }else{ // There are no entries with that User ID so make one
        $stmt = $con->prepare('INSERT INTO biography_tb (bio) VALUES (?)');
        $stmt->bind_param('s', $bio);
        $stmt->execute();
        $stmt->close();   
        echo 'trying to add the bio';

    }

    ?>
    </body>
</html>
<!-- Now the Bio has been saved, return to the profile page -->
<?php    
header("Location: " . $_SERVER["HTTP_REFERER"]);
exit();
?> 
    