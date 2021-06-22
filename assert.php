<?php

// https://github.com/mozilla/openbadges/wiki/Assertions

require_once "config.php";
require_once "util.php";
require_once "baker-lib.php";

if ( !isset($_GET['id']) ) die("Missing id parameter");

$encrypted = $_GET['id'];

// https://www.programmersought.com/article/50052172083/
// $encrypted = bin2hex(openssl_encrypt($_GET['email'], 'DES-EDE3-CBC', md5($PASSWORD), OPENSSL_RAW_DATA|OPENSSL_NO_PADDING, $iv));
// $decrypt = openssl_decrypt(base64_decode($b),'DES-EDE3-CBC',$key,OPENSSL_RAW_DATA|OPENSSL_NO_PADDING,$iv);
// $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), hex2bin($encrypted), MCRYPT_MODE_CBC, md5(md5($PASSWORD))), "\0");
$iv = '&11r2(*3';
$decrypted = openssl_decrypt(hex2bin($encrypted),'DES-EDE3-CBC',md5($PASSWORD),OPENSSL_RAW_DATA,$iv);

$recepient = 'sha256$' . hash('sha256', $decrypted . $ASSERT_SALT);

header('Content-Type: application/json');

$json = get_assert();

$json->id = curPageUrl() . '?id=' . $encrypted;
$json->recipient->salt = $ASSERT_SALT;
$json->recipient->identity = $recepient;
$json->image = str_replace("assert.php", "badge-baker.png", curPageUrl() );
$json->evidence = str_replace("assert.php", "index.php", curPageUrl() );
$json->badge = str_replace("assert.php", "badge-info.php", curPageUrl() );

// echo("<pre>\n");var_dump($json);echo("</pre>\n");
echo(json_encode($json, JSON_PRETTY_PRINT));
