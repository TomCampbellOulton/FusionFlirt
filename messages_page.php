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

// Function to decrypt the messages
function rsaDecrypt($ciphertext, $privateKey) {
    $decoded_message = base64_decode($ciphertext);
    openssl_private_decrypt($decoded_message, $decryptedMessage, $privateKey);
    return $decryptedMessage;
}

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

// Iterate through every chat
foreach ($chats as $chat){
    // Seperate the group ID and the user1_or_2
    $group_ID = $chat['group_ID'];
    $other_user_ID = $chat['other_user_ID'];

    // Get the key ID
    $stmt->$con->prepare("SELECT fk_key_ID FROM group_key_link_tb WHERE fk_group_ID = ?");
    $stmt->bind_param("i", $group_ID);
    $stmt->execute();
    $stmt->bind_result($key_ID);
    $stmt->close();

    // Check which private key the user uses
    if ($user_ID < $other_user_ID){
        // The private key we use is key 1
        $key1_or_key2 = 1;
        // Check if the private key has been downloaded yet
        $stmt -> $con->prepare("SELECT private_key_1 FROM keys_tb WHERE key_ID = ?");
    }else{
        // The private key we use is key 2
        $key1_or_key2 = 2;
        // Check if the private key has been downloaded yet
        $stmt -> $con->prepare("SELECT private_key_2 FROM keys_tb WHERE key_ID = ?");
    }
    $stmt->bind_param("i", $key_ID);
    $stmt->execute();
    $stmt->bind_result($private_key);
    $stmt->close();

    // Check what the private key is
    $downloaded = substr($private_key, 0, 13);
    if ($downloaded !== "Downloaded - "){
        // The key was not downloaded 
        // The filename for the private key
        $filename = "Private Key For Chat " . strval($group_ID) . ".txt"; 
        // Open the file
        $myfile = fopen($filename, "w") or die("Unable to open the file!");
        // Write the private key to the file
        fwrite($myfile, $private_key);
        // Close the file
        fclose($myfile);

        // Store an updated name for the private key
        $new_private_key = "Downloaded - " . $filename;

        // Now delete the key from the database
        // Check which private key to delete
        if ($key1_or_key2 == 1){// Private key 1 being deleted
            $stmt->$con->prepare("UPDATE keys_tb SET private_key_1 = ? WHERE key_ID = ?");
        }else {// Private key 2 being deleted
            $stmt->$con->prepare("UPDATE keys_tb SET private_key_2 = ? WHERE key_ID = ?");
        }
        $stmt->bind_param("si", $new_private_key, $key_ID);
        $stmt->execute();
        $stmt->close();
    }

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
                    
        // Create a button to access each chat
        foreach ($chats as $chat){
            // Get the other user's ID
            $other_user_ID = $chat['other_user_ID'];
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

            // Decrypt the last message    
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

            // If the senders ID is smaler than the user's ID
            if ($message_sender_ID < $user_ID){// Use the 2nd set of keys 
                $stmt = $con->prepare('SELECT private_key_2 FROM keys_tb WHERE key_ID = ?');
                $stmt->bind_param('i', $key_ID);
                $stmt->execute();
                $stmt->bind_result($key);
                $stmt->fetch();
                if (isset($key)) {
                    $private_key = $key;
                }
                $stmt->close();
            }else {// Use the 1st set of keys
                $stmt = $con->prepare('SELECT private_key_1 FROM keys_tb WHERE key_ID = ?');
                $stmt->bind_param('i', $key_ID);
                $stmt->execute();
                $stmt->bind_result($key);
                $stmt->fetch();
                if (isset($key)) {
                    $private_key = $key;
                }
                $stmt->close();
            }
            // If there are any messages in this chat
            if (isset($last_message['text'])){
                $encrypted_text = $last_message['text'];
                $text = rsaDecrypt($encrypted_text, $private_key);
            }else{// There aren't any messages in this chat
                $text = 'You haven\'t spoken yet!! Try chatting to each other :)';
            }

            
            ?>
            <p>
                Here is a chat with <?php echo $fname.' '.$sname.' - ('.$uname.')'; ?>
                <br>Last message in this chat was: <?php echo $text ?>
                <form action="individual_chats_page.php" method="post">
				<button name="group_ID" type="submit" value="<?php echo $group_ID; ?>"> 
					<!-- Text written here goes INSIDE the buttons box-->
					Open your chat with <?php echo $fname;?>
				</button>
                </form>
            <?php
        }