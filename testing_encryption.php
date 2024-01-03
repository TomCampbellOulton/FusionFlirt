<?php
function generateKeyPair() {
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    // Create a new private key
    $privateKey = openssl_pkey_new($config);

    if (!$privateKey) {
        die('Key generation failed');
    }

    // Extract the private key
    openssl_pkey_export($privateKey, $privateKeyPEM);

    // Extract the public key
    $publicKeyDetails = openssl_pkey_get_details($privateKey);
    $publicKeyPEM = $publicKeyDetails["key"];

    return array("public_key" => $publicKeyPEM, "private_key" => $privateKeyPEM);
}


function rsaEncrypt($message, $publicKey) {
    openssl_public_encrypt($message, $ciphertext, $publicKey);
    return $ciphertext;
}

function rsaDecrypt($ciphertext, $privateKey) {
    openssl_private_decrypt($ciphertext, $decryptedMessage, $privateKey);
    return $decryptedMessage;
}

// Example usage:
$keys = generateKeyPair();

// Message from Alice to Bob
$plaintextMessage = 'Hello, Bob!';

// Alice encrypts the message using Bob's public key
$encryptedMessage = rsaEncrypt($plaintextMessage, $keys['public_key']);

// Bob decrypts the message using his private key
$decryptedMessage = rsaDecrypt($encryptedMessage, $keys['private_key']);

// Displaying the results
echo "<br>Original message:" . $plaintextMessage;
echo "<br>Encrypted message: " . base64_encode($encryptedMessage);
echo "<br>Decrypted message by Bob: $decryptedMessage";

$keys['public_key'] = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA2Hez/K7bTT7eZmQiVDmQ
1/R1UaBbdfyPEt+V29U0VA9FaR4jock1/GBxv1zHnphBagy+t8ft+9nBx6dEkJNb
LCXuxwLe4y+uW5b9dV9GgLrRlyJwKrFkSTD9Ueo11SPh/9PlyA8A6EjQl2fOLiYa
m4Z8Bv9pgtfVPTz36gs/RafnhkIFUTElgXpWPLtsWO4UJqau4MM3KuTJji9gre6m
2N4T908J9W0XnsrR9nG3nv/hwe3eZgepFnjcBSeDAuNuhQqaaPmfaXeWqQAAEObS
Srim4ZmSVMKHpZz8FIcmiZQ+vTmSW12c+pMyyq/XA2/wtfRsSCUeuzj9bgl65fM3
aQIDAQAB
-----END PUBLIC KEY-----
";
$keys['private_key'] = "-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC2CPYuzh6B/uEl
Fwk7of1/q3N2fpWKAsaSk4nAD4rOY2TbHUA5kU7Zr5exE1EeE2tD9G6tkEWb+9me
sIERa50nFx4IK9b2huDZBvQ9OCjhOy+qDijxLYWuuFVl7vIHx837rVH5/COZPYHq
56g8U+zn+yMiYZKaQ33Xsv95Yh+GA/Ww/4bzBxkjnBBXBWWS66N3yIi0EExJ6MI/
NS0g8TL829/cGDrEkwa4gGUGE91Aqa/eitPuUF432h/TN5Eom+RumIgUF2Z81T5K
gvXrztGGva5zyRTBaUvqwRKWcl6zmZcEYgO16+0X6EjJoPOaZrKvt7baPp2C6QjJ
eSzlA60XAgMBAAECggEAVch7Wys/LTuTlgr5CuUXtSZyzxBwIA2WzlRAwgWRABnO
2YM7VOWpJuScs5HK1EWKcwepcJlgdFWHBEVhTXhNIrK+MFOYhayiISQpzP++JAXk
PFtX5+/v45pyhArKe+gopcXTr96mV/yoUK7ClUtnhRrQP8+DarcLgq6TvfwDpv9C
FpTBfIrM6rUvrWbzqoGTqiQcFnrQJK9ovox7ZO/Fccu9gS7SwlZb6kp10FMd6vLA
S0C2hIzIAvACkugQGqxHIEryc/VtR0zDO1J9xi5W9sOMCLzgPhHX1ls7WD4xawiY
N2I81m3I31Mfml1SKCACOzzLC+93n77VsLmCRx4IgQKBgQDwKz8kmSosA+28YepX
e//iQ3aBKVkOH6WOEidMVDyiOiSqW5RfrZNHBxmGhZQNp+1wEdyKUR/PScz8eUWF
VBXoBxL8rtxhgfjah9bwURcLN1eIWElLnDs5W8AeMCJ7uZWGTT7hu8jJP1lYyVbC
QR2seP2oZjEoKOoEcMf3SHzgMQKBgQDCCLqD1AIuj9TV+aBVqXYqFIZ/WOyR0pnU
VoXJ1/xDS2BP4wURBotRedhlQmPGdiXTQyPYX+wvcSa45TafJ3ozVvWfkES5WCVY
hQ2gAf+24iDLuI2akb/pT3viXfGkf/vhxCW3kqr0QeViiz2F0bDqqsgx/ankw2mz
v0Bagl0XxwKBgQDoud98BXeaI0xx6af9kkkI5jqlOn3zc7MnkmvGSTJgOVtiQU/r
zvUSNM0FT9eBzCIHISfGWIosgeVDGfjGFA8OQhyiAofHbPzKNiuPv+RGmCAYHQUZ
YZD6KfBm0Fn57oH4HE48y4zrhpl7sc6CejrY8H1Me4pS0iLCns8GzpVDYQKBgBpR
RsMEqXsZRRv0rLuRrZZNjGSPch7hOr4jMihLZErYWWrcdt51TcYySZiKZbAQbFb4
P09ky0swBLmFVWBG4Xs/KzeGQNwGyaH1AgEW5FEw4JPhJ0u0wxvpDPuKFHFkboyT
py379bYFjhBMpH6XD3D00wx8xGNj4d1gbmEYV3oXAoGBANLUa+nha6tzAf46m/Fu
y8hPWNrKQEhhex2vo1ox8AEh5qDYudJvE8mJdki7P0tZg631FlK6A0uCNPtOHhSf
aPV9wlJwZDmVVLVlTvPovGQdAFqKnPClILjoO+Db/3pATw4/JCsdgIKt7XxHfcxz
avVUjup3ZXp1FveOmNDv6BRQ
-----END PRIVATE KEY-----
";

$plaintextMessage = 'Hello, Bob!';
$encryptedMessage = rsaEncrypt($plaintextMessage, $keys['public_key']);

$decryptedMessage = rsaDecrypt($encryptedMessage, $keys['private_key']);

echo '<br> Decrypted ='. $decryptedMessage;