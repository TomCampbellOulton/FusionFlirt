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
	// If the user has a profile, then $fk_profile_ID won't be NULL
	if ($fk_profile_ID === Null){// Create a profile
		echo "noice";

		// First create a biography
		header('Location: /dating_App/fusionflirt1.6/setup_profile.php');
		exit();

	}

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

	// Get the user's biography
	// To get this we first need the bio_ID
	$stmt = $con->prepare('SELECT fk_bio_ID FROM profile_tb WHERE profile_ID = ?');
	$stmt->bind_param('i', $fk_profile_ID);
	$stmt->execute();
	$stmt->bind_result($bio_ID);
	$stmt->fetch();
	$stmt->close();
	// Now get the biography
	$stmt = $con->prepare('SELECT bio FROM biography_tb WHERE bio_ID = ?');
	$stmt->bind_param('i', $bio_ID);
	$stmt->execute();
	$stmt->bind_result($bio);
	$stmt->fetch();
	$stmt->close();

	// Convert the sql datetime into just date
	$phpdate = strtotime( $dob );
	$dob = date( 'd-M-Y', $phpdate );

	// Store the IDs
	// Store the profile_ID
	$_SESSION['profile_ID'] = $fk_profile_ID;
	// Store the bio ID
	$_SESSION['bio_ID'] = $bio_ID;



	?>
	<div class="content">

	<p>
		Your personal details are as follows:
		<br>
		<?php
		echo "Username: ".$uname."<br> Firstname: ".$fname."<br> Surname: ".$sname."<br> Date Of Birth: ".$dob."<br> Address_ID: ".$fk_add_ID."<br> Contact_ID: ".$fk_cont_ID."<br> Phone Number: ".$phone_num."<br> Email: ".$email;
		?>
	</p>

	<form action="change_personal_details.php" method="post">
		Would you like to change your personal details?
		<button name="changePersonalDetails" type="submit" value="<?php echo $user_ID; ?>">
			Change Details
		</button>
	</form>

	<p>
		Add some images? :)</p>
		<form method='POST' name='upload_images' enctype='multipart/form-data'>
			<input type="file" name="image" />
			<input type="submit" name="submit" value="Upload" />
		</form>
		<?php 
		// Check if an image was uploaded
		if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
			$name = $_FILES['image']['name'];
			$type = $_FILES['image']['type'];
			$data = file_get_contents($_FILES['image']['tmp_name']);			
			// Insert the image into the database
			$stmt = $con->prepare('INSERT INTO images_tb (image, image_name, image_type) VALUES (?, ?, ?)');
			$stmt->bind_param('sss', $data, $name, $type);
			$stmt->execute();
			$image_ID = $con->insert_id;
			$stmt->close();   

			// Link this file to the user who uploaded it
			$stmt = $con->prepare('INSERT INTO images_profile_link_tb (fk_image_ID, fk_profile_ID) VALUES (?, ?)');
			$stmt->bind_param('ii', $image_ID, $fk_profile_ID);
			$stmt->execute();
			$stmt->close();  
			

		}

		// Show the user all their images
		$stmt = $con->prepare('SELECT fk_image_ID FROM images_profile_link_tb WHERE fk_profile_ID = ?');
		$stmt->bind_param('i', $fk_profile_ID);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($fk_image_ID);
		$image_IDs = ''; // Initialize the string

		while ($stmt->fetch()) {
			$image_IDs .= $fk_image_ID . ','; // Concatenate the image IDs
		}
		
		if ($stmt->num_rows > 0) {
			$stmt->close();
		
			// Remove the trailing comma from $image_IDs
			$image_IDs = rtrim($image_IDs, ',');
		
			// Show these images
			$stmt = $con->prepare('SELECT image, image_name, image_type FROM images_tb WHERE image_ID IN (' . $image_IDs . ')');
			$stmt->execute();
			$stmt->store_result();
			
			while ($stmt->fetch()) {
				// For each image, display it
				$stmt->bind_result($image, $image_name, $image_type);
				echo '<img src="display_image.php?image_ids=' . $image_IDs . '" alt="Image">';

				// Add a button next to it to remove it

			}
		
			$stmt->close();
		}
		

	
		?>
	
	<form action="change_bio.php" method="post">
		<br>Your Biography
		<textarea name="users_bio" rows="5" cols="113"><?php echo $bio?></textarea> 
		<button name="changeBio" type="submit" value="changeBio">
			Change biography
		</button>
	</form>

	
	<form action="delete_account.php" method="post">
		<br>Delete Your Account!
		<button name="deleteAccount" type="submit" value="<?php echo $user_ID; ?>">
			<p>Delete Your Account</p>
		</button>
	</form>


	</div>	
	</body>
</html>	