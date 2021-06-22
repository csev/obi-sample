<?php

// Encryption technique from
// http://stackoverflow.com/questions/6833448/how-should-i-encrypt-my-data-in-a-php-application

require_once "config.php";
require_once "baker-lib.php";
require_once "util.php";

$file = 'badge-baker.png';
// echo("<pre>\n");var_dump($_SERVER);echo("</pre>\n");
$url = $_SERVER['REQUEST_URI'];
$pieces = explode('/',$url);
// echo("<pre>\n");var_dump($pieces);echo("</pre>\n");
$encrypted = basename($pieces[2],'.png');
if ( $pieces[2] == $file ) {
    $png = file_get_contents($file);
    header('Content-Type: image/png');
    header('Content-Length: ' . strlen($png));
    echo($png);
    return;
}

// https://www.programmersought.com/article/50052172083/
// $encrypted = bin2hex(openssl_encrypt($_GET['email'], 'DES-EDE3-CBC', md5($PASSWORD), OPENSSL_RAW_DATA|OPENSSL_NO_PADDING, $iv));
// $decrypt = openssl_decrypt(base64_decode($b),'DES-EDE3-CBC',$key,OPENSSL_RAW_DATA|OPENSSL_NO_PADDING,$iv);
// $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), hex2bin($encrypted), MCRYPT_MODE_CBC, md5(md5($PASSWORD))), "\0");
$iv = '&11r2(*3';
$decrypted = openssl_decrypt(hex2bin($encrypted),'DES-EDE3-CBC',md5($PASSWORD),OPENSSL_RAW_DATA,$iv);

// Make the badge
$assert = get_assert();
$iv = '&11r2(*3';

$recepient = 'sha256$' . hash('sha256', $decrypted . $ASSERT_SALT);

header('Content-Type: application/json');

$json = get_assert();

$json->id = str_replace("baker.php", "assert.php", curPageUrl() ) . '?id=' . $encrypted;
$json->recipient->salt = $ASSERT_SALT;
$json->recipient->identity = $recepient;
$json->image = str_replace("baker.php", "badge-baker.png", curPageUrl() );
$json->evidence = str_replace("baker.php", "index.php", curPageUrl() );
$json->badge = str_replace("baker.php", "badge-info.php", curPageUrl() );

// echo("<pre>\n");var_dump($json);echo("</pre>\n");
$text = json_encode($json, JSON_PRETTY_PRINT);

$png = file_get_contents($file);
$png2 = addOrReplaceTextInPng($png,"openbadges",$text, 'iTXt');

header('Content-Type: image/png');
header('Content-Length: ' . strlen($png2));

echo($png2);
