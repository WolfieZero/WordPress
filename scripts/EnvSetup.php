<?php

namespace WordPress\Scripts;

use Composer\Script\Event;

class EnvSetup extends ScriptHandler
{
    private static $options = [
        'WP_ENV' => [
            'ask' => 'Environment name?',
            'default' => 'production',
            'type' => 'text',
        ],
        'WP_HOME' => [
            'ask' => 'WordPress home URL?',
            'default' => '',
            'type' => 'text',
        ],
        'WP_DEBUG' => [
            'ask' => 'Debug mode on?',
            'default' => false,
            'type' => 'confirm',
        ],
        'DB_HOST' => [
            'ask' => 'Database host?',
            'default' => '127.0.0.1',
            'type' => 'text',
        ],
        'DB_NAME' => [
            'ask' => 'Database name?',
            'default' => 'wordpress',
            'type' => 'text',
        ],
        'DB_USER' => [
            'ask' => 'Database user?',
            'default' => 'root',
            'type' => 'text',
        ],
        'DB_PASSWORD' => [
            'ask' => 'Database password',
            'default' => 'root',
            'type' => 'text',
        ]
    ];

    /**
     * Makes a random key... simple.
     *
     * @param integer $length
     * @return string
     */
    private static function makeRandomKey(int $length = 64) : string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
        $size = strlen($characters);
        $key = '';

        for ($i = 0; $i < $length; $i++) {
            $key .= $characters[rand(0, $size - 1)];
        }

        return $key;
    }

    /**
     * Generates the files passing in the required values.
     *
     * @param string $dbHost
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPass
     * @param string $wpEnv
     * @param boolean $wpDebug
     * @return boolean
     */
    private static function generateFile(array $values) : bool
    {
        $envFile = self::$root . '/.env';
        copy(self::$root . '/.env.example', $envFile);

        $envContents = file_get_contents($envFile);

        $keys = [
            'AUTH_KEY' => '\'' . self::makeRandomKey() . '\'',
            'SECURE_AUTH_KEY' => '\'' . self::makeRandomKey() . '\'',
            'LOGGED_IN_KEY' => '\'' . self::makeRandomKey() . '\'',
            'NONCE_KEY' => '\'' . self::makeRandomKey() . '\'',
            'AUTH_SALT' => '\'' . self::makeRandomKey() . '\'',
            'SECURE_AUTH_SALT' => '\'' . self::makeRandomKey() . '\'',
            'LOGGED_IN_SALT' => '\'' . self::makeRandomKey() . '\'',
            'NONCE_SALT' => '\'' . self::makeRandomKey() . '\'',
        ];

        $configs = array_merge($values, $keys);

        $pattern = '(.*)';

        foreach ($configs as $config => $value) {
            $fullPattern = '/' . $config . '=' . $pattern . '/i';
            $content = $config . '=' . $value;

            if(preg_match($fullPattern, $envContents) > 0) {
                $envContents = preg_replace(
                    $fullPattern,
                    $content,
                    $envContents
                );
            } else {
                $envContents .= "\n" . $content;
            }
        }

        return file_put_contents($envFile, $envContents) ? true : false;
    }

    /**
     * Starts the creation of the .env file.
     *
     * @param Event $event
     * @return void
     */
    public static function process(Event $event)
    {
        self::setupHandler($event);

        if (file_exists(self::$root . '/.env')) {
            return;
        }

        self::printHeader('Create .env File');

        if (!self::$io->askConfirmation('Do you want to populate the .env? [Y/n] ', true)) {
            return;
        }

        $values = [];

        foreach (self::$options as $key => $option) {
            $values[$key] = self::query($option);
        }

        self::$io->write('');
        self::$io->write('Building .env file...');

        if (! self::generateFile($values)) {
            self::$io->writeError('Failed to create .env file');
        } else {
            self::$io->write('.env created at `' . self::$root . '/.env`');
        }

        self::$io->write('');
    }

}
