<?php

require("util.php");

$raw = '{
  "@context": "http://w3id.org/openbadges/v1",
  "type": "Issuer",
  "id": "http://mydomain.org/issuer",
  "name": "An Awesome Badge Issuer",
  "image": "https://example.org/logo.png",
  "url": "https://example.org",
  "email": "steved@example.org",
  "revocationList": "https://example.org/revoked.json"
}';

$json = json_decode($raw);
if ( json_last_error() != JSON_ERROR_NONE ) {
    die(json_last_error_msg());
}

// Patch the JSON
$json->id = str_replace("badge-issuer.php", "", curPageUrl());
$json->url = str_replace("badge-issuer.php", "", curPageUrl());
$json->image = 'http://www.sakaiger.com/images/Sakaiger.png';
$json->revocationList = str_replace("badge-issuer.php", "revoked.json", curPageUrl() );

// echo("<pre>\n");var_dump($json);echo("</pre>\n");
echo(json_encode($json));

