<?php
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
session_start();
// Function to create the key pair (private and public)
function generateKeyPair() {
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );
    // Create the private key
    $privateKey = openssl_pkey_new($config);
    // Check if the private key was created, if not then fail
    if (!$privateKey) {
        die('Key generation failed');
    }

    // Extract the private key
    openssl_pkey_export($privateKey, $privateKeyPEM);

    // Extract the public key
    $publicKeyDetails = openssl_pkey_get_details($privateKey);
    $publicKeyPEM = $publicKeyDetails["key"];
    // Return the keys in an array
    return array("public_key" => $publicKeyPEM, "private_key" => $privateKeyPEM);
}

// Generate the key pair for user 1
$keys1 = generateKeyPair();
// Generate the key pair for user 2
$keys2 = generateKeyPair();

// Store the key pairs
$stmt = $con->prepare('INSERT INTO keys_tb (public_key_1, private_key_1, public_key_2, private_key_2) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $keys1['public_key'], $keys1['private_key'], $keys2['public_key'], $keys2['private_key']);
$stmt->execute();
$stmt->close();

// Get the Key ID
$stmt = $con->prepare('SELECT LAST_INSERT_ID() AS last_id;');
$stmt->execute();
$stmt->bind_result($key_ID);
while ($stmt->fetch()){
    echo $key_ID;
}

$group_ID = $_SESSION['group_ID'];

// Link the group ID and the key ID
$stmt = $con->prepare('INSERT INTO group_key_link_tb (fk_key_ID, fk_group_ID) VALUES (?, ?)');
$stmt->bind_param('ii', $key_ID, $group_ID);
$stmt->execute();
$stmt->close();

header('Location: /dating_App/fusionflirt1.6/home.php');
exit();

?>
