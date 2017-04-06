<?php
// =============================================================================
// Copy and Update the Index File
// =============================================================================
// If we need to make changes to `index.php` in future then this file can manage
// those changes if needs be.


$public = __DIR__ . '/../../public/';

copy(
    $public . 'wordpress/index.php',
    $public . 'index.php'
);

$indexContents = str_replace(
    '/wp-blog-header.php',
    '/wordpress/wp-blog-header.php',
    file_get_contents($public . 'index.php')
);

file_put_contents(
    $public . 'index.php',
    $indexContents
);
