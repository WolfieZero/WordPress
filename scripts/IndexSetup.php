<?php

namespace WordPress\Scripts;

use Composer\Script\Event;

class IndexSetup extends ScriptHandler
{

    /**
     * Starts the creation of the .env file.
     *
     * @param Event $event
     * @return void
     */
    public static function process(Event $event)
    {
        self::setupHandler($event);
        $wpFolder = 'wp';
        $publicFolder = 'public';
        $wpDirectory = '/' . $publicFolder . '/' . $wpFolder;

        copy(
            self::$root . $wpDirectory . '/index.php',
            self::$root . '/' . $publicFolder . '/index.php'
        );

        $indexContents = str_replace(
            '/wp-blog-header.php',
            '/' . $wpFolder . '/wp-blog-header.php',
            file_get_contents(self::$root . '/' . $publicFolder . '/index.php')
        );

        file_put_contents(
            self::$root . '/' . $publicFolder . '/index.php',
            $indexContents
        );
    }

}
