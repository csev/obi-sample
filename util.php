<?php 

  function curPageURL() {
    $pageURL = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
             ? 'http'
             : 'https';
    $pageURL .= "://";
    $pageURL .= $_SERVER['HTTP_HOST'];
    //$pageURL .= $_SERVER['REQUEST_URI'];
    $pageURL .= $_SERVER['PHP_SELF'];
    return $pageURL;
  }


  function urlGET($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    return $response;
  }


/**
  * extractBadgeInfo
  * @return mixed - If there is an error, a string is returned
  * If there is badge information that matches the key an array
  * that looks like 
  *
  *  [0] => openbadges
  *  [1] => http://localhost:8888/obi-sample/assert.php?id=a8be2ea26402b7a32441283979aec23714d118b5fe1538e162c9b4b4d3351603
  * 
  * is returned
  */

function extractBadgeInfo($png, $key='openbadges', $debug=false) {
    // Read the magic bytes and verify
    $retval = substr($png,0,8);
    $ipos = 8;
    if ($retval != "\x89PNG\x0d\x0a\x1a\x0a") {
        return 'This is not a valid PNG image';
    }

    if ( $debug ) echo("\nSearching for PNG chunks...\n");
    // Loop through the chunks. Byte 0-3 is length, Byte 4-7 is type
    $chunkHeader = substr($png,$ipos,8);
    $ipos = $ipos + 8;
    while ($chunkHeader) {
        // Extract length and type from binary data
        $chunk = @unpack('Nsize/a4type', $chunkHeader);
        if( $debug ) echo("\n".htmlentities($chunk['type'])." (".$chunk['size'].")\n");
        $skip = false;
        if ( $chunk['type'] == 'tEXt' ) {
            $data = substr($png,$ipos,$chunk['size']);
            $sections = explode("\0", $data);
            if ( $debug ) print_r($sections);
            if ( $sections[0] == $key ) return $sections;
        }

        // Extract the data and the CRC
        $data = substr($png,$ipos,$chunk['size']+4);
        $ipos = $ipos + $chunk['size'] + 4;
    
        // Read next chunk header
        $chunkHeader = substr($png,$ipos,8);
        $ipos = $ipos + 8;
    }
}

/* 
Dumping PNG Metadata

Retrieving http://localhost:8888/obi-sample/a8be2ea26402b7a32441283979aec23714d118b5fe1538e162c9b4b4d3351603.png
...
File length:23377

Searching for PNG chunks...

IHDR (13)

gAMA (4)

PLTE (768)

tRNS (52)

IDAT (8192)

IDAT (8192)

IDAT (5918)

tEXt (122)
Array
(
    [0] => openbadges
    [1] => http://localhost:8888/obi-sample/assert.php?id=a8be2ea26402b7a32441283979aec23714d118b5fe1538e162c9b4b4d3351603
)

IEND (0)
*/
