<?php

namespace WordPress\Scripts;

use Composer\Script\Event;

abstract class ScriptHandler
{
    /**
     * Root of this WordPress project (created in `self::process()`).
     *
     * @var string
     */
    protected static $root;

    /**
     * Input/Output object.
     *
     * @var object
     */
    protected static $io;

    abstract public static function process(Event $event);

    protected static function setupHandler(Event $event)
    {
        self::$root = dirname(__FILE__, 2);
        self::$io = $event->getIO();
    }

    protected static function printHeader(string $text)
    {
        self::$io->write('');
        self::$io->write('==============================');
        self::$io->write($text);
        self::$io->write('==============================');
        self::$io->write('');
    }

    protected static function query(array $params) : string
    {
        $ask =  $params['ask'];
        $default = $params['default'];
        $type = $params['type'];

        switch ($type) {

            case 'confirm':
                $displayDefault = '[y/N]';
                if ($default) {
                    $displayDefault = '[Y/n]';
                }
                return self::$io->askConfirmation(
                    $ask . ': ' . $displayDefault . ' ',
                    $default
                ) ? 'true' : 'false';

            case 'secure':
                return self::$io->askAndHideAnswer(
                    $ask . ': [' . $default . '] ',
                    $default
                );

            case 'text':
            default:
                return self::$io->ask(
                    $ask . ': [' . $default . '] ',
                    $default
                );
        }
    }

}

