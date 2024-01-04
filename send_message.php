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

// The function for our encryption (uses RSA Encryption)
function rsaEncrypt($message, $publicKey) {
    openssl_public_encrypt($message, $ciphertext, $publicKey);
    return base64_encode($ciphertext);
}

// Get the user's ID
$user_ID = $_SESSION['id'];

$message = $_POST['users_message'];
$group_ID = $_POST['group_ID'];
$other_user_ID = $_POST['other_user_ID'];


// Encrypt the message
// First find the encryption key
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

// Checks which key to use: The user with the smallest ID uses the first 2 keys, the other user (with largest ID) uses the 2nd set of keys
if ($user_ID < $other_user_ID){// Use the 1st set of keys 
	$stmt = $con->prepare('SELECT public_key_1 FROM keys_tb WHERE key_ID = ?');
	$stmt->bind_param('i', $key_ID);
	$stmt->execute();
	$stmt->bind_result($key);
	$stmt->fetch();
	if (isset($key)) {
		$public_key = $key;
	}
	$stmt->close();
}else {// Use the 2nd set of keys
	$stmt = $con->prepare('SELECT public_key_2 FROM keys_tb WHERE key_ID = ?');
	$stmt->bind_param('i', $key_ID);
	$stmt->execute();
	$stmt->bind_result($key);
	$stmt->fetch();
	if (isset($key)) {
		$public_key = $key;
	}
	$stmt->close();
}
// Encrypt the message
$encryptedMessage = rsaEncrypt($message, $public_key);

// Insert into the messages table, the encrypted message, who sent it and which groupchat it was sent to
$stmt = $con->prepare('INSERT INTO messages_tb (message_text, message_sender_ID, fk_group_ID) VALUES (?, ?, ?)');
$stmt->bind_param('sis', $encryptedMessage, $user_ID, $group_ID);
$stmt->execute();
$stmt->close();
echo $key_ID;

header('Location: /dating_App/fusionflirt1.6/individual_chats_page.php');
exit();
?>