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
if (isset($_POST['respond'])){ // If the user has clicked on accept or decline
					
    // The match ID
    $match_ID = $_POST['respond'];

    // User 1 or 2
    $user1Or2 = $_POST['user1Or2'];

    // Other user's ID
    $other_user_ID = $_POST['other_user_ID'];

    // Responded or not
    $responded = True;
    // Accept=True or decline=delete
    $accept = True;

    if ($user1Or2 === 'user1'){ // If it's user 1
        // Record user 1 as having responded in this match
        $stmt = $con->prepare('UPDATE matches_tb  SET user1Responded = ?, accepted = ? WHERE match_ID = ?');
        // If the user has accepted, add this to the database
        $stmt->bind_param('iii', $responded, $accept, $match_ID);
        $stmt->execute();
        $stmt->close();
        // Order which user is 1 and which user is 2
        $user_1_ID = $user_ID;
        $user_2_ID = $other_user_ID;

    }else{// Otherwise they're user 2
        // Record user 1 as having responded in this match
        $stmt = $con->prepare('UPDATE matches_tb  SET user2Responded = ?, accepted = ? WHERE match_ID = ?');
        // If the user has accepted, add this to the database
        $stmt->bind_param('iii', $responded, $accept, $match_ID);
        $stmt->execute();
        $stmt->close();
        // Order which user is 1 and which user is 2
        $user_1_ID = $other_user_ID;
        $user_2_ID = $user_ID;
    }
    
    // Now check to see if they have matched with the other user having initiated the matching process
    $stmt = $con->prepare('SELECT user1Responded, user2Responded, accepted FROM matches_tb WHERE fk_user1_ID = ? AND fk_user2_ID = ?');
    $stmt->bind_param('ii', $user_1_ID, $user_2_ID);
    $stmt->execute();
    $stmt->bind_result($user_responded, $match_responded, $accepted);
    while ($stmt->fetch()) {
        $matched = True;
    }
    // Check if both of them have already responded
    echo $user_ID, $other_user_ID;
    echo $user_responded,$match_responded;
    if ($user_responded === 1 && $match_responded === 1){ // They have both responded
        echo 'You have both already matched with this person. ';
        // Check if they've accepted or declined
        if ($accepted === 1){
            echo 'You have both accepted so can now communicate :D';
            // Now create a chat between the two users
            $stmt = $con->prepare('INSERT INTO message_groups_tb (fk_user1_ID, fk_user2_ID) VALUES (?, ?)');
            $stmt->bind_param('ii', $user_ID, $other_user_ID);
            $stmt->execute();
            $stmt->close();
            // Get the group ID
            $stmt = $con->prepare('SELECT LAST_INSERT_ID() AS g_ID;');
            $stmt->execute();
            $stmt->bind_result($group_ID);
            while ($stmt->fetch()){
                // Store their group_ID
                $_SESSION['group_ID'] = $group_ID;
            }
            // Now generate their keys
            header('Location: /dating_App/fusionflirt1.7/generate_keys.php');
            exit();

        }else {
            echo 'One of you have declined this match and are therefore incompatible. My apologies.';
        }
    }else{// Only one or neither has responded
        // Check who hasn't responded yet
        if ($user_responded === False){// The user hasn't responded yet
            echo '<br>You need to respond to this match please.';
        }else{// The other person hasn't responded yet
            echo '<br>Please be patient whilst you wait for your matches.';
        }
    }
}
//header('Location: /dating_App/fusionflirt1.7/home.php');
//exit();
?>