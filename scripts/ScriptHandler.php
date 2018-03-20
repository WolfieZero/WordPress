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

    /**
     * Initialisation process.
     *
     * @param Event $event
     * @return void
     */
    abstract public static function process(Event $event);

    /**
     * Setup the handler
     *
     * @param Event $event
     * @return void
     */
    protected static function setupHandler(Event $event)
    {
        self::$root = dirname(__FILE__, 2);
        self::$io = $event->getIO();
    }

    protected static function write(string $text)
    {
        self::$io->write($text);
    }

    protected static function writeError(string $error)
    {
        self::$io->writeError($error);
    }

    protected static function writeHeader(string $text)
    {
        self::$io->write('');
        self::$io->write('==============================');
        self::$io->write($text);
        self::$io->write('==============================');
        self::$io->write('');
    }

    protected static function  writeFooter(string $text = '')
    {
        if ($text) {
            self::$io->write($text);
        }

        self::$io->write('');
    }

    /**
     * Query
     *
     * @param string $ask
     * @param string $type
     * @param string $default
     * @return string
     */
    protected static function query(string $ask, string $type, $default = false) : string
    {
        switch ($type) {

            case 'confirm':
                return self::confirm(
                    $ask,
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

    protected static function confirm(string $ask, bool $default = false) : bool
    {
        $displayDefault = '[y/N]';

        if ($default) {
            $displayDefault = '[Y/n]';
        }

        return self::$io->askConfirmation(
            $ask . ': ' . $displayDefault . ' ',
            $default
        );
    }

}

