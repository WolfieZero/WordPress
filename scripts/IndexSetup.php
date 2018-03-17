<?php

namespace WordPress\Scripts;

use Composer\Script\Event;

class IndexSetup
{

    /**
     * Starts the creation of the .env file.
     *
     * @param Event $event
     * @return void
     */
    public static function process(Event $event)
    {
        $root = dirname(__FILE__, 2);

        $wpFolder = 'wp';
        $publicFolder = 'public';
        $wpDirectory = '/' . $publicFolder . '/' . $wpFolder;

        copy(
            $root . $wpDirectory . '/index.php',
            $root . '/' . $publicFolder . '/index.php'
        );

        $indexContents = str_replace(
            '/wp-blog-header.php',
            '/' . $wpFolder . '/wp-blog-header.php',
            file_get_contents($root . '/' . $publicFolder . '/index.php')
        );

        file_put_contents(
            $root . '/' . $publicFolder . '/index.php',
            $indexContents
        );

    }

}
