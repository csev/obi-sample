<html>
<head>
<title>The World's Easiest Mozilla Open Badge Maker (Beta)</title>
<!-- https://github.com/mozilla/openbadges/wiki/Issuer-API -->
<script src="http://beta.openbadges.org/issuer.js"></script>
</head>
<body style="font-family:sans-serif;">
<h2>Welcome to the World's Easiest-To-Earn Mozilla Open Badge (Beta)</h2>
<form>
<p>Enter E-Mail Address and Press Submit.  You need to use 
a real E-Mail address if you 
want to put the badge be validatable and can be placed in your Mozilla backpack.</p>
<input type="text" name="email" size="40">
<input type="submit" value="Bake My Badge">
</form>
<form method="post" action="dump.php" enctype="multipart/form-data">
<p>Upload one of your OBI Images (PNG-only) to scan through the Metadata 
in the badge and find the assertion behind the badge. 
You can upload any OBI badge and look at its meta-data 
- not just badges created on this site.
</p>
<input type="file" name="upload">
<input type="submit">
</form>
<p>
Validate Badge Contents:
<?php
$recepient = '';
$salt = '';
$val_email = '';
if ( isset($_POST['recepient']) && isset($_POST['salt']) &&
    isset($_POST['val_email']) ) {
    $recepient = $_POST['recepient'];
    $salt = $_POST['salt'];
    $val_email = $_POST['val_email'];
    $val_check = 'sha256$' . hash('sha256', $val_email . $salt);
    if ( $recepient == $val_check ) {
        echo(' <span style="color: green">Validated!</span>');
    }
    echo("<br>\n");
    echo("Computed SHA: ".$val_check);
    echo("<br>\n");
}
?>
<form method="post">
Recipient: 
<input type="string" name="recepient" size="80"
value="<?= htmlentities($recepient) ?>"><br/>
Salt: 
<input type="string" name="salt" size="80"
value="<?= htmlentities($salt) ?>"><br/>
Email: 
<input type="string" name="val_email" size="80"
value="<?= htmlentities($val_email) ?>"><br/>
<input type="submit" name="validate" value="Validate">
</form>
</p>
<?php
require_once "config.php";

if ( isset($_GET['email']) ) {

$encrypted = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), $_GET['email'], MCRYPT_MODE_CBC, md5(md5($PASSWORD))));  
?>
<p>Here is the badge baked especially for for <?php echo(htmlspecialchars($_GET['email'])); ?> 
<a href="<?php echo($encrypted); ?>.png" target="_blank">
<img style="vertical-align: middle" width="90" src="<?php echo($encrypted); ?>.png"></a>
<p>You can download the baked badge image to your computer and display it on your 
web site or maually upload it to your
<a href="http://beta.openbadges.org" target="_blank">Mozilla Badge Backpack</a>.
You can also uploaded the image above to dump out the metadata in the image.
</p>
<?php } ?>
<p>
Note: This site uses a badge-baking technique that does not require any permanent storage
of any of your data.  This site does not even have a database.  Take a look at the source code 
to see how it is done using no storage at all.
</p>
<p>
The source code to this project is at 
<a href="https://github.com/csev/obi-sample" target="_blank">https://github.com/csev/obi-sample</a>.
Comments welcome.
</p>
<p>
The OBE 1.0 Specification
<a href="https://github.com/mozilla/openbadges-specification/blob/master/Assertion/latest.md" 
target="_blank">is here</a>.
<p>
<a href="http://www.dr-chuck.com/">Dr. Chuck</a>
Tue Mar 19 23:41:50 EDT 2013
