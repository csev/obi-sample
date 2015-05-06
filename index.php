<?php
require_once "config.php";
require("util.php");
require("baker-lib.php");

$encrypted = false;
$email = '';
$badge_url='';
$ver_url = '';
$recepient = '';
$salt = '';
$ver_email = '';
$baseUrl = str_replace("index.php","",curPageUrl());
if ( isset($_GET['email']) ) {
    $email = $_GET['email'];
    $encrypted = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), $_GET['email'], MCRYPT_MODE_CBC, md5(md5($PASSWORD))));  
    $badge_url = $baseUrl . $encrypted . '.png';
}
?>
<html>
<head>
<title>The World's Easiest Mozilla Open Badge Maker (Beta)</title>
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
<?php } 

if ( isset($_POST['url']) || isset($_FILES['upload']) ) {
    echo("<pre>\n");
    if ( isset($_FILES['upload']) && isset($_FILES['upload']['size']) &&
        $_FILES['upload']['size'] > 0 ) {
        $png = file_get_contents($_FILES['upload']['tmp_name']);
        echo("Uploaded File length:".strlen($png)."\n");
    } else {
        $badge_url=$_POST['url'];
        echo("Retrieving ".$_POST['url']."\n...\n");
        $png = urlGET($_POST['url']);
        echo("Retrieved ".strlen($png)." bytes\n");
    }

    $sections = extractBadgeInfo($png);

    if ( is_string($sections) ) {
        echo('Error in badge metadata: '.$sections."\n");
    } else {
        $ver_url = $sections[1];
        echo("Badge assertion url:\n".$sections[1]."\n");
    }
    echo("</pre>\n");
}
?>


<p>
<form method="post" enctype="multipart/form-data">
Badge URL to Retrieve and Dump:<br/>
<input type="text" name="url" size="90"
value="<?= htmlentities($badge_url) ?>"><br/>
Badge File to Dump:
<input type="file" name="upload"><br/>
<input type="submit" value="Retrieve Badge Metadata">
</form>
<p>
Once you dump the badge metadata, you will see an assertion URL, 
which can be used as an assertion URL below.
<p>
<?php
if ( isset($_POST['ver_url']) && isset($_POST['ver_email']) ) {
    $ver_url = $_POST['ver_url'];
    $ver_email = $_POST['ver_email'];
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
    $val_check = 'sha256$' . hash('sha256', $ver_email . $salt);
    echo('Validate Badge Contents: ');
    if ( $recepient == $val_check ) {
        echo(' <span style="color: green">Validated!</span>');
    } else if ( strlen(trim($ver_email)) == 0 ) {
        echo(' <span style="color: red">Missing Email Address</span>');
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
?>
<p>
<form method="post">
Verify A Badge Using Assertion URL:<br/>
<input type="string" name="ver_url" size="80"
value="<?= htmlentities($ver_url) ?>"><br/>
Expected Email Address (required): 
<input type="string" name="ver_email" size="40"
value="<?= htmlentities($ver_email) ?>"><br/>
<input type="submit" name="validate" value="Retrieve and Validate Assertion URL">
</form>
</p>
<?php

if ( isset($_POST['recepient']) && isset($_POST['salt']) &&
    isset($_POST['ver_email']) ) {
    $recepient = $_POST['recepient'];
    $salt = $_POST['salt'];
    $ver_email = $_POST['ver_email'];
    $val_check = 'sha256$' . hash('sha256', $ver_email . $salt);
    echo('Validate Badge Contents: ');
    if ( $recepient == $val_check ) {
        echo(' <span style="color: green">Validated!</span>');
    } else if ( strlen(trim($ver_email)) == 0 ) {
        echo(' <span style="color: red">Missing Email Address</span>');
    } else {
        echo(' <span style="color: red">Validation Failure</span>');
    }
    echo("<br>\n");
    echo("Computed SHA: ".$val_check);
    echo("<br>\n");
}
?>
<p>
Assertion Data:
<form method="post">
Recipient: 
<input type="string" name="recepient" size="80"
value="<?= htmlentities($recepient) ?>"><br/>
Salt: 
<input type="string" name="salt" size="80"
value="<?= htmlentities($salt) ?>"><br/>
Expected Email Address (required): 
<input type="string" name="ver_email" size="40"
value="<?= htmlentities($ver_email) ?>"><br/>
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
These badges meet the 
<a href="https://github.com/mozilla/openbadges-specification/blob/master/Assertion/latest.md" 
target="_blank">Open Badges 1.0 Specification</a>.
The <a href="http://specification.openbadges.org/history/1.1.html"
target="_blank">Open Badges 1.1 Specification</a> 
extends the badge metadata formats using JSON-LD.
<p>
<a href="http://www.dr-chuck.com/">Dr. Chuck</a>
Wed May  6 05:54:18 EDT 2015
