<?php
require_once "config.php";
require("util.php");

$encrypted = false;
$email = '';
if ( isset($_GET['email']) ) {
    $email = $_GET['email'];
    $encrypted = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), $_GET['email'], MCRYPT_MODE_CBC, md5(md5($PASSWORD))));  
}
?>
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
want to put the badge be validatable and can be placed in 
a badge store that is based on your email address.
<input type="text" name="email" size="40">
<input type="submit" value="Bake My Badge">
</form>
<?php if ( $encrypted ) { ?>
<p>Here is the badge baked especially for 
for <?php echo(htmlspecialchars($_GET['email'])); ?> 
<a href="<?php echo($encrypted); ?>.png" target="_blank">
<img style="vertical-align: middle" width="90" src="<?php echo($encrypted); ?>.png"></a>
<p>You can download the baked badge image to your computer and 
display it on your 
web site or maually upload it to a badge store.
</p>
<?php } ?>
<p>
<form method="post" action="dump.php" 
    enctype="multipart/form-data" target="_blank">
Badge URL to Retrieve and Dump:<br/>
<input type="text" name="url" size="90"><br>
Badge File to Dump:
<input type="file" name="upload"><br/>
<input type="submit" value="Retrieve Badge Metadata">
</form>
<p>
Once you dump the badge metadata, you will see an assertion URL, 
which can be used as an assertion URL below.
<p>
<?php
$ver_url = '';
$recepient = '';
$salt = '';
$val_email = '';
if ( isset($_POST['ver_url']) && isset($_POST['val_email']) ) {
    $ver_url = $_POST['ver_url'];
    $val_email = $_POST['val_email'];
    $response = urlGET($ver_url);
    $json = json_decode($response);
    if ( json_last_error() != JSON_ERROR_NONE ) {
        echo("<br/>Error retrieving ".$ver_url);
        echo("<pre>\n");echo($response);echo("</pre>\n");
        die('JSON Parse Error: '.json_last_error_msg());
    }
    // echo("\n<pre>\n");var_dump($json);echo("</pre>\n");
    $recepient = $json->recipient->identity;
    $salt = $json->recipient->salt;
    $val_check = 'sha256$' . hash('sha256', $val_email . $salt);
    echo('Validate Badge Contents: ');
    if ( $recepient == $val_check ) {
        echo(' <span style="color: green">Validated!</span>');
    } else {
        echo(' <span style="color: red">Validation Failure</span>');
    }
    echo("<br>\n");
    echo("Computed SHA: ".$val_check);
    echo("<br>\n");
    if ( $recepient != $val_check ) {
        echo("<pre>\n");echo($response);echo("</pre>\n");
    }
}

if ( isset($_POST['recepient']) && isset($_POST['salt']) &&
    isset($_POST['val_email']) ) {
    $recepient = $_POST['recepient'];
    $salt = $_POST['salt'];
    $val_email = $_POST['val_email'];
    $val_check = 'sha256$' . hash('sha256', $val_email . $salt);
    echo('Validate Badge Contents: ');
    if ( $recepient == $val_check ) {
        echo(' <span style="color: green">Validated!</span>');
    }
    echo("<br>\n");
    echo("Computed SHA: ".$val_check);
    echo("<br>\n");
}
?>
<p>
<form method="post">
Verify A Badge Using Assertion URL:<br/>
<input type="string" name="ver_url" size="80"
value="<?= htmlentities($ver_url) ?>"><br/>
Expected Email: 
<input type="string" name="val_email" size="80"
value="<?= htmlentities($val_email) ?>"><br/>
<input type="submit" name="validate" value="Retrieve and Validate Assertion URL">
</form>
</p>
<p>
Assertion Data:
<form method="post">
Recipient: 
<input type="string" name="recepient" size="80"
value="<?= htmlentities($recepient) ?>"><br/>
Salt: 
<input type="string" name="salt" size="80"
value="<?= htmlentities($salt) ?>"><br/>
Expected Email: 
<input type="string" name="val_email" size="80"
value="<?= htmlentities($val_email) ?>"><br/>
<input type="submit" name="validate" value="Validate Using Asserton Data">
</form>
</p>
<p>
Note: This site uses a badge-baking technique that does not 
require any permanent storage of any of your data.  This 
site does not even have a database.  A typical implementation would
likely store some data in a database - but the goal 
of this application is to be as simple as possible. Take a look 
at the source code to see how it is done using no storage at all.
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
