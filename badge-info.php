<?php

require("util.php");

$raw = '{
  "@context": "http://w3id.org/openbadges/v1",
  "type": "BadgeClass",
  "id": "http://mydomain.org/badges/1",
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
$json->id = str_replace("badge-info.php", "1", curPageUrl());
$json->issuer = str_replace("badge-info.php", "badge-issuer.php", curPageUrl());
$json->image = str_replace("badge-info.php", "badge-baker.png", curPageUrl() );
$json->criteria = str_replace("badge-info.php", "index.php", curPageUrl() );

// echo("<pre>\n");var_dump($json);echo("</pre>\n");

echo(json_encode($json));

