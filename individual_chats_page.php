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
// Create a heap sort function to order the messages
function heapSort(&$array, $attribute, $min_val = 0) {
    $length = count($array);

    // Build max heap
    for ($i = (int)($length / 2) - 1; $i >= 0; $i--) {
        heapify($array, $length, $i, $attribute);
    }

    // Extract elements from the heap one by one
    for ($i = $length - 1; $i > 0; $i--) {
        // Check if these messages exist (Won't exist if they were deleted)
        if (isset($array[500+0]) && isset($array[500+$i])){
            // Swap the root (maximum element) with the last element
            list($array[500+0], $array[500+$i]) = array($array[500+$i], $array[500+0]);
            
            // Reduce the heap size and heapify the root
            heapify($array, $i, 0, $attribute);
        }
    }
}

function heapify(&$array, $n, $i, $attribute) {
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    // Compare with left child
    if ($left < $n && isset($array[$left][$attribute]) && $array[$left][$attribute] > $array[$largest][$attribute]) {
        $largest = $left;
    }

    // Compare with right child
    if ($right < $n && isset($array[$right][$attribute]) && $array[$right][$attribute] > $array[$largest][$attribute]) {
        $largest = $right;
    }

    // If the largest is not the root
    if ($largest != $i) {
        // Swap the root with the largest element
        list($array[$i], $array[$largest]) = array($array[$largest], $array[$i]);

        // Recursively heapify the affected sub-tree
        heapify($array, $n, $largest, $attribute);
    }
}



// Get the chat log
$chat_log = array();
$min_val = -1;
$stmt = $con->prepare('SELECT message_ID, message_text, message_read, message_sender_ID, delivery_time FROM messages_tb WHERE fk_group_ID = ?');
$stmt->bind_param('i', $group_ID);
$stmt->execute();
$stmt->bind_result($message_ID, $message_text, $message_read, $message_sender_ID, $delivery_time);
while ($stmt->fetch()){
    // Find the smallest ID
    if ($min_val === -1){// If min value not defined yet
        // Define it
        $min_val = $message_ID;
    }else if ($min_val > $message_ID) { // If this message is smaller than min value
        // Change min value
        $min_val = $message_ID;
    }
    $chat_log[$message_ID] = array('text'=>$message_text, 'read'=>$message_read, 'sender_ID'=>$message_sender_ID, 'delivery_time'=>$delivery_time);
}
$stmt->close();
// Create a ciruclar queue
class circularQueue {
    private $maxSize;
    private $queue;
    private $front_pointer;
    private $back_pointer;

    
    // Create the circular queue upon being called
    public function __construct ($maxSize = 500) { // Default max size of 500
        $this->maxSize = $maxSize; 
        // Fill the circular queue with value "null"
        $this->queue = array_fill(0, $maxSize, null);
        $this->front_pointer = $this->back_pointer -1;
    }

    public function isEmpty () {
        // If the queue is empty, return the front pointer as -1
        return $this->front_pointer == -1;
    }

    public function isFull () {
        // Either the back pointer is the length of the list -1 or the back pointer is the front_pointer pointer -1
        return ($this->front_pointer == 0 && $this->back_pointer == $this->maxSize -1) || ($this->front_pointer == $this->back_pointer + 1);
    }

    public function enqueue ($item) {
        // If the queue is already full
        if ($this->isFull()){
            echo "Queue is full. Cannot enqueue $item.";
            return;
        }
        // If the queue is empty
        if ($this->isEmpty()) {
            // Arrange the back pointer to where the new item is located (at position 0)
            $this->front_pointer = $this->back_pointer = 0;
        } else { // If the queue is not empty
            // Arrange the back pointer to where the new item is located
            $this->back_pointer = ($this->back_pointer + 1) % $this->maxSize;
        }
        // Add the item to the queue
        $this->queue[$this->back_pointer] = $item;
        
    }

    public function dequeue() {
        // If the queue is empty
        if ($this->isEmpty()) {
            echo "Queue is empty. Cannot dequeue.\n";
            return null;
        }
        // Finds the item at the front of the queue
        $item = $this->queue[$this->front_pointer];
        // If the item at the front is the only item in the queue
        if ($this->front_pointer == $this->back_pointer) {
            // Set both the front and back pointers to -1
            $this->front_pointer = $this->back_pointer = -1;
        } else { // Otherwise there are other items in the queue
            // So move the front pointer to the next item in the queue
            $this->front_pointer = ($this->front_pointer + 1) % $this->maxSize;
        }
        // Return the item
        return $item;
    }

}

// Order the chat log according to when each message was sent
heapSort($chat_log, 'delivery_time',  $min_val );
// Enter the chat log into a circular queue
$chat_queue = new circularQueue();
// Store the length of the chat
$chat_length = 0;
foreach ($chat_log as $chat){
    $chat_queue->enqueue($chat);
    $chat_length ++;
}

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
}else { // The user is chatting with themself, this is not allowed currenlty
    exit();
}
// Get and store the other user's name 
$stmt = $con->prepare('SELECT firstname, surname FROM users_tb WHERE user_ID = ?');
$stmt->bind_param('i', $other_user_ID);
$stmt->execute();
$stmt->bind_result($fname, $sname);
$stmt->fetch();
$stmt->close();
$_SESSION['other_users_name'] = $fname.' '.$sname;
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
			<h2>Your messages with <?php echo $_SESSION['other_users_name'];?>!</h2>
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
            for ($i=0; $i<$chat_length; ++$i){
                // Get the message from the circular queue
                $chat = $chat_queue->dequeue();

                // If the chat exists
                if ($chat !== null){
                    // Decrypt this message
                    
                    // If the senders ID is the smaller value
                    if ($chat['sender_ID'] < max($other_user_ID, $user_ID)){// Use the 1st set of keys 
                        $stmt = $con->prepare('SELECT private_key_1 FROM keys_tb WHERE key_ID = ?');
                        $stmt->bind_param('i', $key_ID);
                        $stmt->execute();
                        $stmt->bind_result($key);
                        $stmt->fetch();
                        if (isset($key)) {
                            $private_key_filename = $key;
                        }
                        $stmt->close();
                    }else {// Use the 2nd set of keys
                        $stmt = $con->prepare('SELECT private_key_2 FROM keys_tb WHERE key_ID = ?');
                        $stmt->bind_param('i', $key_ID);
                        $stmt->execute();
                        $stmt->bind_result($key);
                        $stmt->fetch();
                        if (isset($key)) {
                            $private_key_filename = $key;
                        }
                        $stmt->close();
                    }

                    // Get the private key
                    $myfile = fopen($private_key_filename, "r") or die("Unable to open the file!");
                    $private_key = fread($myfile, filesize($myfile));
                    fclose($myfile);

                    $message = $chat['text'];
                    $decryptedMessage = rsaDecrypt($message, $private_key);


                    // Check who is texting, if it's the user or the other person.
                    if ($chat['sender_ID'] === $user_ID){// The user's message
                        // Allign it along the right side of the page
                        echo '<p style="text-align:right;">'.$decryptedMessage.'</p>';
                    } else if ($chat['sender_ID'] === $other_user_ID){// The other persons message
                        // Allign it along the left side of the page
                        echo '<p style="text-align:left;">'.$decryptedMessage.'</p>';
                    } 
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

             