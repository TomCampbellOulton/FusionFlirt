<?php
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

if (isset($_GET['image_ids'])) {
    // Get the array of image IDs from the query parameter
    $image_IDs = explode(',', $_GET['image_ids']);

    // Output each image
    foreach ($image_IDs as $image_ID) {
        // Get the data of the image(s)
        $stmt = $con->prepare('SELECT image, image_type FROM images_tb WHERE image_ID = ?');
        $stmt->bind_param('i', $image_ID);
        $stmt->execute();
        $stmt->store_result();

        // If any images are found, show them
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($image, $image_type);
            $stmt->fetch();
            // Display the image
            header("Content-type: $image_type");
            echo $image;
            echo '<br>'; // Add a line break for multiple images
        } else {
            // Image not found for the current ID
            echo "Image with ID $image_ID not found<br>";
        }

        $stmt->close();
    }
} else {
    // The image IDs are missing
    echo 'The image IDs are missing';
}
?>
