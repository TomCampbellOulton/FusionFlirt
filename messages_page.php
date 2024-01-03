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

// Get the user's ID
$user_ID = $_SESSION['id'];

// Check how many chats the user has
$stmt = $con->prepare('SELECT group_ID, fk_user1_ID, fk_user2_ID FROM message_groups_tb WHERE fk_user1_ID = ? OR fk_user2_ID = ?');
$stmt->bind_param('ii', $user_ID, $user_ID);
$stmt->execute();
$stmt->bind_result($group_ID, $fk_user1_ID, $fk_user2_ID);


// Store all the chats in an array
$chats = array();
while ($stmt->fetch()) {
    // Checks if the user is user1 or user2
    if ($user_ID === $fk_user1_ID){ // User is user 1
        $other_user_ID = $fk_user2_ID;
    } else if ($user_ID === $fk_user2_ID) { // User is user 2
        $other_user_ID = $fk_user1_ID;
    }else{ // An error
        exit();
    }
   
    $chats[] = array('group_ID'=>$group_ID, 'other_user_ID'=>$other_user_ID);

}
$stmt->close();


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
				<a href="create_matches.php"></i>Make Me Matches Please c:</a>
				<a href="messages_page.php"></i>Messages ;)</a>
				<a href="surveys_page.php">Surveys Page</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
			<h2>Your messages!</h2>
			<p>Welcome back, <?=$_SESSION['name']?>!</p>

        <?php
                    
        // Create a button to access each chat
        foreach ($chats as $chat){
            // Get their username
            $stmt = $con->prepare('SELECT username, firstname, surname FROM users_tb WHERE user_ID = ?');
            $stmt->bind_param('i', $other_user_ID);
            $stmt->execute();
            $stmt->bind_result($uname, $fname, $sname);
            $stmt->fetch();
            $stmt->close();

            // Get the messaging group ID
            $group_ID = $chat['group_ID'];
            // Store all the messages from the chat
            $chat_log = array();
            // Get the last message from the chat
            $last_message = array();
            $stmt = $con->prepare('SELECT message_ID, message_text, message_read, message_sender_ID, delivery_time FROM messages_tb WHERE fk_group_ID = ?');
            $stmt->bind_param('i', $group_ID);
            $stmt->execute();
            $stmt->bind_result($message_ID, $message_text, $message_read, $message_sender_ID, $delivery_time);
            while ($stmt->fetch()){
                $chat_log[$message_ID] = array('text'=>$message_text, 'read'=>$message_read, 'sender_ID'=>$message_sender_ID, 'delivery_time'=>$delivery_time);
                $last_message = array('text'=>$message_text, 'read'=>$message_read, 'sender_ID'=>$message_sender_ID, 'delivery_time'=>$delivery_time);
            }
            $stmt->close();
            
            ?>
            <p>
                Here is a chat with <?php echo $fname.' '.$sname.' - ('.$uname.')'; ?>
                <br>Last message in this chat was: <?php if (isset($last_message['text'])){echo $last_message['text'];}else{echo 'You haven\'t spoken yet!! Try chatting to each other :)';} ?>
                <form action="individual_chats_page.php" method="post">
				<button name="group_ID" type="submit" value="<?php echo $group_ID; ?>"> 
					<!-- Text written here goes INSIDE the buttons box-->
					Open your chat with <?php echo $fname;?>
				</button>
            <?php
        }