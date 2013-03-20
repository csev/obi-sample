<?php

// Encryption technique from
// http://stackoverflow.com/questions/6833448/how-should-i-encrypt-my-data-in-a-php-application

require_once "config.php";
require_once "baker-lib.php";

$url = $_SERVER['SCRIPT_URL'];
$pieces = explode('/',$url);
$encrypted = basename($pieces[2],'.png');
$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), hex2bin($encrypted), MCRYPT_MODE_CBC, md5(md5($PASSWORD))), "\0");

// Make the badge
$file = 'badge-baker.png';
$png = file_get_contents($file);
$png2 = addOrReplaceTextInPng($png,"openbadges","http://www.dr-chuck.com/obi-sample/assert.php?id=".$encrypted);

header('Content-Type: image/png');
header('Content-Length: ' . strlen($png2));

echo($png2);
