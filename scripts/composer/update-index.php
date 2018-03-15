<?php
// =============================================================================
// Copy and Update the Index File
// =============================================================================
// If we need to make changes to `index.php` in future then this file can manage
// those changes if needs be.

$root = __DIR__ . '/../..';
$composer = json_decode(file_get_contents($root . '/composer.json'));
$wpDirectory = $composer->extra->{'wordpress-install-dir'};
$wpPublicDirectory = str_replace('public/', '', $wpDirectory);

copy(
    $root . '/' . $wpDirectory . '/index.php',
    $root . '/public/index.php'
);

$indexContents = str_replace(
    '/wp-blog-header.php',
    '/' . $wpPublicDirectory . '/wp-blog-header.php',
    file_get_contents($root . '/public/index.php')
);

file_put_contents(
    $root . '/public/index.php',
    $indexContents
);
