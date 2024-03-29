<?php
require_once "config.php";
require("util.php");
require("baker-lib.php");

$encrypted = false;
$email = '';
$badge_url='';
$ver_url = '';
$ver_data = '';
$recepient = '';
$salt = '';
$ver_email = '';
$baseUrl = str_replace("index.php","",curPageUrl());
if ( isset($_GET['email']) ) {
    $email = $_GET['email'];
    // $encrypted = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($PASSWORD), $_GET['email'], MCRYPT_MODE_CBC, md5(md5($PASSWORD))));
    // https://www.programmersought.com/article/50052172083/
    // $b = base64_encode(openssl_encrypt($data,'DES-EDE3-CBC',$key,OPENSSL_RAW_DATA|OPENSSL_NO_PADDING,$iv));
    $iv = '&11r2(*3';
    // $encrypted = bin2hex(openssl_encrypt($_GET['email'], 'DES-EDE3-CBC', md5($PASSWORD), OPENSSL_RAW_DATA|OPENSSL_NO_PADDING, $iv));
    $encrypted = bin2hex(openssl_encrypt($_GET['email'], 'DES-EDE3-CBC', md5($PASSWORD), OPENSSL_RAW_DATA, $iv));
    $badge_url = $baseUrl . $encrypted . '.png';
}
?>
<html>
<head>
<title>The World's Easiest Open Badge Maker/Validator</title>
</head>
<body style="font-family:sans-serif;">
<h2>Welcome to the World's Easiest-To-Earn Open Badge and Badge Validator</h2>
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

$ver_json = false;
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
        $ver_data = $sections[1];
        echo("Badge assertion data:\n".$sections[1]."\n");
        $ver_json = json_decode($ver_data);
        if ( ! is_object($ver_json) ) {
            echo("Could not parse assertion JSON ".json_last_error());
        }
    }
}
if ( strlen($badge_url)<1 && isset($_GET['url']) ) $badge_url = $_GET['url'];

// Check the id of the assertion
if ( isset($ver_json->id) && is_string($ver_json->id) ) {
    $url = $ver_json->id;
    $assertion = urlGET($url);
    echo("Retrieved $url\nReceived ".strlen($assertion)." bytes\n");
    $json = json_decode($assertion);
    if ( is_object($json) ) {
        if ( isset($json->id) && is_string($json->id) && $json->id == $url) {        
            echo("Assertion id value matches id value in retrieved assertion\n");
            echo("\n");
        } else {
            echo("Assertion id mismatch:\n");
            echo("Embedded assertion id:  ".htmlentities($ver_json->id)."\n");
            echo("Retrieved assertion id: ".htmlentities($ver_json->id)."\n");
            echo(htmlentities($assertion));
            echo("\n");
        }
    } else {
        echo(htmlentities($assertion));
        echo("\n");
        echo("Could not parse issuer JSON ".json_last_error());
    }
}

// Retrieve any extra documents
if ( isset($ver_json->badge) && is_object($ver_json->badge) ) {
    echo("Found inline badge data\n");
} else if ( isset($ver_json->badge) && is_string($ver_json->badge) ) {
    $url = $ver_json->badge;
    $badge = urlGET($url);
    echo("Retrieved $url\nReceived ".strlen($badge)." bytes\n");
    echo(htmlentities($badge));
    echo("\n");
    $json = json_decode($badge);
    if ( is_object($json) ) {
        if ( isset($json->id) && is_string($json->id) && $json->id == $url) {        
            // Good.
        } else {
            echo("Badge id should be the same as its url\n");
        }
        $ver_json->badge = $json;
    } else {
        echo("Could not parse badge JSON ".json_last_error());
    }
}

if ( isset($ver_json->badge->issuer) && is_object($ver_json->badge->issuer) ) {
    echo("Found inline issuer data\n");
} else if ( isset($ver_json->badge->issuer) && is_string($ver_json->badge->issuer) ) {
    $url = $ver_json->badge->issuer;
    $issuer = urlGET($url);
    echo("Retrieved $url\nReceived ".strlen($issuer)." bytes\n");
    echo(htmlentities($issuer));
    echo("\n");
    $json = json_decode($issuer);
    if ( is_object($json) ) {
        if ( isset($json->id) && is_string($json->id) && $json->id == $url) {        
            // Good.
        } else {
            echo("Issuer id should be the same as its url\n");
        }
        $ver_json->badge->issuer = $json;
    } else {
        echo("Could not parse issuer JSON ".json_last_error());
    }
}

echo("</pre>\n");
?>


<p>
<form method="post" action="index.php" enctype="multipart/form-data">
Badge URL to Retrieve and Dump:<br/>
<input type="text" name="url" size="90"
value="<?= htmlentities($badge_url) ?>"><br/>
Badge File to Dump:
<input type="file" name="upload"><br/>
<input type="submit" value="Retrieve Badge Metadata">
</form>
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

if ( strlen($ver_data) > 0 ) {
    if ( strpos($ver_data, '{') !== 0 ) {
        $ver_url = $ver_data;
        echo('<p><a href="'.$ver_url.'" target="_blank">');
        echo("View Assertion Data</a> (new window)</p>\n");
    } else {
        $json  = json_decode($ver_data);
        if ( isset($json->id) ) $ver_url = $json->id;
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
<form method="post">
Assertion Data:<br/>
Recipient:
<input type="string" name="recepient" size="80"
value="<?= htmlentities($recepient) ?>"><br/>
Salt:
<input type="string" name="salt" size="80"
value="<?= htmlentities($salt) ?>"><br/>
Expected Email Address (required): 
<input type="string" name="ver_email" size="40"
value="<?= htmlentities($ver_email) ?>"><br/>
<input type="submit" name="validate" value="Validate Using Assertion Data">
</form>
</p>
<p>
Other validators:
<ul>
<li><a href="https://openbadgesvalidator.imsglobal.org" target="_blank">https://openbadgesvalidator.imsglobal.org</a></li>
<li><a href="https://badgecheck.io" target="_blank">https://badgecheck.io</a></li>
</ul>
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
<a href="https://www.imsglobal.org/sites/default/files/Badges/OBv2p0Final/index.html"
target="_blank">Open Badges 2.0 Specification</a>.
Here is the 
<a href="https://www.imsglobal.org/sites/default/files/Badges/OBv2p0Final/baking/index.html"
target="_blank">how to bake a badge document from IMS</a>.
<p>
<a href="http://www.dr-chuck.com/">Dr. Chuck</a>
Mon 21 Jun 2021 12:03:10 PM EDT
