<pre>
Dumping PNG Metadata

<?php
require("util.php");

if ( isset($_POST['url']) ) {
    echo("Retrieving ".$_POST['url']."\n...\n");
    $png = urlGET($_POST['url']); 
} else {
    $png = file_get_contents($_FILES['upload']['tmp_name']); 
}

echo("File length:".strlen($png)."\n");

$sections = extractBadgeInfo($png, 'openbadges', true);

if ( is_string($sections) ) {
    echo($sections."\n");
} else {
    echo("Badge url: ".$sections[1]."\n");
}
