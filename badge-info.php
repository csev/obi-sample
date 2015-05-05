<?php

require("util.php");

$raw = '{
  "name": "Simple Baking Badge",
  "description": "The world\'s easiest badge to earn.",
  "image": "https://example.org/robotics-badge.png",
  "criteria": "https://example.org/robotics-badge.html",
  "tags": ["simple", "awesome", "open source"],
  "issuer": "https://example.org/organization.json"
}';

$json = json_decode($raw);
if ( json_last_error() != JSON_ERROR_NONE ) {
    die(json_last_error_msg());
}

// Patch the JSON
$json->issuer = str_replace("badge-info.php", "", curPageUrl());
$json->image = str_replace("badge-info.php", "badge-baker.png", curPageUrl() );
$json->criteria = str_replace("badge-info.php", "index.php", curPageUrl() );

echo("<pre>\n");var_dump($json);echo("</pre>\n");

