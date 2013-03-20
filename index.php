<html>
<head>
<title>The World's Easiest Mozilla Open Badge Maker</title>
</head>
<body style="font-family:sans-serif;">
<h2>Welcome to the World's Easiest-To-Earn Mozilla Open Badge</h2>
<form>
<p>Enter E-Mail Address and Press Submit</p>
<input type="text" name="email" size="40">
<input type="submit" value="Create Badge">
</form>
<form method="post" action="dump.php" enctype="multipart/form-data">
<p>Upload an OBI (PNG-only) to examine the Metadata</p>
<input type="file" name="upload">
<input type="submit">
</form>
<?php
require_once "config.php";

if ( ! isset($_GET['email']) ) return;

$encrypted = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), $_GET['email'], MCRYPT_MODE_CBC, md5(md5($PASSWORD))));  
// $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), hex2bin($encrypted), MCRYPT_MODE_CBC, md5(md5($PASSWORD))), "\0");
// echo($decrypted);
?>
<p>Here is the badge baked especially for for <?php echo(htmlspecialchars($_GET['email'])); ?> <br/>
<a href="<?php echo($encrypted); ?>.png" target="_blank">
<img src="<?php echo($encrypted); ?>.png"></a>
<p>You can download the baked badge and then upload it to see 
the metadata that was put in during the baking process.</p>
