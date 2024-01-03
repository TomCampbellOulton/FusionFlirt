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

// Group ID is stored in Session IF NOT from post
if (isset($_POST['group_ID'])){
    $group_ID = $_POST['group_ID'];
    $_SESSION['group_ID'] = $group_ID;
}else if (isset($_SESSION['group_ID'])){
    $group_ID = $_SESSION['group_ID'];
}

// Get the chat log
$chat_log = array();
$stmt = $con->prepare('SELECT message_ID, message_text, message_read, message_sender_ID, delivery_time FROM messages_tb WHERE fk_group_ID = ?');
$stmt->bind_param('i', $group_ID);
$stmt->execute();
$stmt->bind_result($message_ID, $message_text, $message_read, $message_sender_ID, $delivery_time);
while ($stmt->fetch()){
    $chat_log[$message_ID] = array('text'=>$message_text, 'read'=>$message_read, 'sender_ID'=>$message_sender_ID, 'delivery_time'=>$delivery_time);
}
$stmt->close();

// Get the user's in the chat
$chat_users = array();
$stmt = $con->prepare('SELECT fk_user1_ID, fk_user2_ID FROM message_groups_tb WHERE group_ID = ?');
$stmt->bind_param('i', $group_ID);
$stmt->execute();
$stmt->bind_result($fk_user1_ID, $fk_user2_ID);
$stmt->fetch();
$stmt->close();
// Check which user is which
if ($fk_user1_ID === $user_ID){// If user1 is the user
    $other_user_ID = $fk_user2_ID;
}else if ($fk_user2_ID === $user_ID){// If user2 is the user
    $other_user_ID = $fk_user1_ID;
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


            // First find the decryption key
            $key_IDs = array();
            $stmt = $con->prepare('SELECT fk_key_ID FROM group_key_link_tb WHERE fk_group_ID = ?');
            $stmt->bind_param('i', $group_ID);
            $stmt->execute();
            $stmt->bind_result($key_ID);
            while ($stmt->fetch()){
                $key_IDs[] = $key_ID;
            }
            $stmt->close();
            // Take the last (newest) key ID
            $key_ID = end($key_IDs);

            // Define the RSA Decryption Method
            function rsaDecrypt($ciphertext, $privateKey) {
                $decoded_message = base64_decode($ciphertext);
                openssl_private_decrypt($decoded_message, $decryptedMessage, $privateKey);
                return $decryptedMessage;
            }


            
            // Display all the messages
            foreach ($chat_log as $chat){
                // Decrypt this message

                // If the senders ID is the smaller value
                if ($chat['sender_ID'] < max($other_user_ID, $user_ID)){// Use the 1st set of keys 
                    $stmt = $con->prepare('SELECT private_key_1 FROM keys_tb WHERE key_ID = ?');
                    $stmt->bind_param('i', $key_ID);
                    $stmt->execute();
                    $stmt->bind_result($key);
                    $stmt->fetch();
                    if (isset($key)) {
                        $private_key = $key;
                        echo 'first key';
                    }
                    $stmt->close();
                }else {// Use the 2nd set of keys
                    $stmt = $con->prepare('SELECT private_key_2 FROM keys_tb WHERE key_ID = ?');
                    $stmt->bind_param('i', $key_ID);
                    $stmt->execute();
                    $stmt->bind_result($key);
                    $stmt->fetch();
                    if (isset($key)) {
                        $private_key = $key;
                        echo 'second key';
                    }
                    $stmt->close();
                }

                $message = $chat['text'];
                $decryptedMessage = rsaDecrypt($message, $private_key);
                echo $decryptedMessage;

                // Check who is texting, if it's the user or the other person.
                if ($chat['sender_ID'] === $user_ID){// The user's message
                    // Allign it along the right side of the page
                    echo '<p style="text-align:right;">'.$decryptedMessage.'</p>';
                } else if ($chat['sender_ID'] === $other_user_ID){// The other persons message
                    // Allign it along the left side of the page
                    echo '<p style="text-align:left;">'.$decryptedMessage.'</p>';
                } 
            }
            // Allow the user to send more messages
            ?>
            <form action='send_message.php' method='POST'>
                <input type='text' id='users_message' name='users_message'>
                <input type='submit' name='submit_button' value='Submit :)'>
                <input type='hidden' name='group_ID' value='<?php echo $group_ID;?>'>
                <input type='hidden' name='other_user_ID' value='<?php echo $other_user_ID;?>'>
            </form>

             