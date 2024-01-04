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
		</div>


	<?php
	$user_ID = $_SESSION["id"];

    // Check if the user has assembled a profile yet
	$stmt = $con->prepare('SELECT fk_profile_ID FROM users_tb WHERE user_ID = ?');
	$stmt->bind_param('i', $user_ID);
	$stmt->execute();
	$stmt->bind_result($fk_profile_ID);
	$stmt->fetch();
	$stmt->close();	

    // Get the user's details
	$stmt = $con->prepare('SELECT username, firstname, surname, dateOfBirth, fk_address_ID, fk_contact_ID, fk_profile_ID FROM users_tb WHERE user_ID = ?');
	$stmt->bind_param('i', $user_ID);
	$stmt->execute();
	$stmt->bind_result($uname, $fname, $sname, $dob, $fk_add_ID, $fk_cont_ID, $fk_profile_ID);
	$stmt->fetch();
	$stmt->close();	

	// Get the user's contact details
	$stmt = $con->prepare('SELECT phoneNumber, emailAddress FROM contact_details_tb WHERE contact_ID = ?');
	$stmt->bind_param('i', $fk_cont_ID);
	$stmt->execute();
	$stmt->bind_result($phone_num, $email);
	$stmt->fetch();
	$stmt->close();

    // Convert the sql datetime into just date
    $phpdate = strtotime( $dob );
    $dob = date( 'Y-m-d', $phpdate );


    // Calculate the most recent year allowed for a user (of 18+)
    $age = date('Y-m-d', strtotime('-18 year'));

    // Now create text boxes with these answers as the defaults
    ?>
    <div class="content">
    <form action="save_personal_details.php" method="post">
        <br>Your Firstname:
        <textarea name="firstname" rows="1" cols="30"><?php echo $fname?></textarea> 

        <br>Your Surname:
        <textarea name="surname" rows="1" cols="30"><?php echo $sname?></textarea> 

        <br>Your Date Of Birth:
        <input type='date' name='date_of_birth' id='date' value='<?php echo $dob;?>' min='1900-01-01' max='<?php echo $age;?>'> 

        <br>Your Phone Number:
        <textarea name="phone_number" rows="1" cols="30"><?php echo $phone_num?></textarea> 

        <br>Your Email Address:
        <textarea name="email_address" rows="1" cols="30"><?php echo $email?></textarea> 


        <button name="submitChanges" type="submit" value="<?php echo $users_ID; ?>">
            Submit Changes
        </button>
    </form>
    </div>