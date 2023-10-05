<?php
$pword = password_hash("123456", PASSWORD_DEFAULT);
echo $pword;
if (password_verify("123456",$pword)){
    echo "IT WORKS :D";
}
?>