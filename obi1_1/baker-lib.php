<?php

// Adapted from
// http://stackoverflow.com/questions/8842387/php-add-itxt-comment-to-a-png-image

    function addOrReplaceTextInPng($png,$key,$text) {
        $png = removeTextChunks($key, $png);
        $chunk = phpTextChunk($key,$text);
        $png2 = addPngChunk($chunk,$png);
        return $png2;
    }

    // Strip out any existing text chunks with a particular key
    function removeTextChunks($key,$png) {
        // Read the magic bytes and verify
        if ( strlen($png) < 8 ) {
            throw new Exception('No data retrieved');
        }
        $retval = substr($png,0,8);
        $ipos = 8;
        if ($retval != "\x89PNG\x0d\x0a\x1a\x0a")
            throw new Exception('Not a valid PNG image');

        // Loop through the chunks. Byte 0-3 is length, Byte 4-7 is type
        $chunkHeader = substr($png,$ipos,8);
        $ipos = $ipos + 8;
        while ($chunkHeader) {
            // Extract length and type from binary data
            $chunk = @unpack('Nsize/a4type', $chunkHeader);
            $skip = false;
            if ( $chunk['type'] == 'tEXt' ) {
                $data = substr($png,$ipos,$chunk['size']);
                $sections = explode("\0", $data);
                print_r($sections);
                if ( $sections[0] == $key ) $skip = true;
            }

            // Extract the data and the CRC
            $data = substr($png,$ipos,$chunk['size']+4);
            $ipos = $ipos + $chunk['size'] + 4;

            // Add in the header, data, and CRC
            if ( ! $skip ) $retval = $retval . $chunkHeader . $data;

            // Read next chunk header
            $chunkHeader = substr($png,$ipos,8);
            $ipos = $ipos + 8;
        }
        return $retval;
    }

    // creates a tEXt chunk with given key and text (iso8859-1)
    // ToDo: check that key length is less than 79 and that neither includes null bytes
    function phpTextChunk($key,$text) {
        $chunktype = "tEXt";
        $chunkdata = $key . "\0" . $text;
        $crc = pack("N", crc32($chunktype . $chunkdata));
        $len = pack("N",strlen($chunkdata));
        return $len .  $chunktype  . $chunkdata . $crc;
    }

    // inserts chunk before IEND chunk (last 12 bytes)
    function addPngChunk($chunk,$png) {
        $len = strlen($png);
        return substr($png,0,$len-12) . $chunk . substr($png,$len-12,12);
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
    if ( strlen($png) < 8 ) {
        return 'No data retrieved';
    }
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
