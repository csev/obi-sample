<pre>
Dumping PNG Metadata

<?php

$png = file_get_contents($_FILES['upload']['tmp_name']); 

echo("File length:".strlen($png)."\n");

// Read the magic bytes and verify
$retval = substr($png,0,8);
$ipos = 8;
if ($retval != "\x89PNG\x0d\x0a\x1a\x0a")
    die('This is not a valid PNG image');

echo("\nSearching for PNG chunks...\n");
// Loop through the chunks. Byte 0-3 is length, Byte 4-7 is type
$chunkHeader = substr($png,$ipos,8);
$ipos = $ipos + 8;
while ($chunkHeader) {
    // Extract length and type from binary data
    $chunk = @unpack('Nsize/a4type', $chunkHeader);
	    echo("\n".htmlentities($chunk['type'])." (".$chunk['size'].")\n");
    $skip = false;
    if ( $chunk['type'] == 'tEXt' ) {
        $data = substr($png,$ipos,$chunk['size']);
        $sections = explode("\0", $data);
        print_r($sections);
        // if ( $sections[0] == $key ) $skip = true;
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
