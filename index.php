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
<?php
require_once "config.php";

if ( ! isset($_GET['email']) ) return;

$encrypted = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), $_GET['email'], MCRYPT_MODE_CBC, md5(md5($PASSWORD))));  
?>
<p>Here is the badge baked especially for for <?php echo(htmlspecialchars($_GET['email'])); ?> <br/>
<a href="<?php echo($encrypted); ?>.png" target="_blank">
<img src="<?php echo($encrypted); ?>.png"></a>
<p>
<a href="#" onclick="OpenBadges.issue(
['http://www.dr-chuck.com/obi-sample/assert.php?id=<?php echo($encrypted); ?>'],
function(errors, successes) { });">Add this Badge to my Mozilla Badge Backpack</a>
</p>
<p>You can also download the baked badge image to your computer and display it on your 
web site or maually upload it to your
<a href="http://beta.openbadges.org" target="_blank">Mozilla Badge Backpack</a>.
You can also uploaded the image above to dump out the metadata in the image.
</p>
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
<a href="http://www.dr-chuck.com/">Dr. Chuck</a>
Tue Mar 19 23:41:50 EDT 2013
