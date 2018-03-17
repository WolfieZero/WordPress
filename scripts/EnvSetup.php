<?php

namespace WordPress\Scripts;

use Composer\Script\Event;

class EnvSetup
{

    /**
     * Root of this WordPress project (created in `self::process()`).
     *
     * @var string
     */
    private static $root;

    /**
     * Default database host.
     *
     * @var string
     */
    private static $defaultDbHost = 'localhost';

    /**
     * Default database name.
     *
     * @var string
     */
    private static $defaultDbName = 'wordpress';

    /**
     * Default database user.
     *
     * @var string
     */
    private static $defaultDbUser = 'root';

    /**
     * Default database password.
     *
     * @var string
     */
    private static $defaultDbPass = 'root';

    /**
     * Default WordPress Environment.
     *
     * @var string
     */
    private static $defaultWpEnv = 'production';

    /**
     * Default WordPress debug setting.
     *
     * @var boolean
     */
    private static $defaultWpDebug = false;

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
    private static function generateFile(string $dbHost, string $dbName, string $dbUser, string $dbPass, string $wpEnv, bool $wpDebug) : bool
    {
        $envFile = self::$root . '/.env';
        copy(self::$root . '/.env.example', $envFile);

        $envContents = file_get_contents($envFile);

        $configs = [
            'WP_ENV' => $wpEnv,
            'WP_DEBUG' => $wpDebug ? 'true' : 'false',

            'DB_HOST' => $dbHost,
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPass,

            'AUTH_KEY' => '\'' . self::makeRandomKey() . '\'',
            'SECURE_AUTH_KEY' => '\'' . self::makeRandomKey() . '\'',
            'LOGGED_IN_KEY' => '\'' . self::makeRandomKey() . '\'',
            'NONCE_KEY' => '\'' . self::makeRandomKey() . '\'',
            'AUTH_SALT' => '\'' . self::makeRandomKey() . '\'',
            'SECURE_AUTH_SALT' => '\'' . self::makeRandomKey() . '\'',
            'LOGGED_IN_SALT' => '\'' . self::makeRandomKey() . '\'',
            'NONCE_SALT' => '\'' . self::makeRandomKey() . '\'',
        ];

        $pattern = '(.*)';

        foreach ($configs as $config => $value) {
            $envContents = preg_replace(
                '/' . $config . '=' . $pattern . '/i',
                $config . '=' . $value,
                $envContents
            );
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
        self::$root = dirname(__FILE__, 2);

        if (file_exists(self::$root . '/.env')) {
            return;
        }

        $io = $event->getIO();
        $io->write('');
        $io->write('==============================');
        $io->write('Create .env File');
        $io->write('==============================');
        $io->write('');

        if (!$io->askConfirmation('Press return if you want to populate the .env (Y/n)? ', true)) {
            return;
        }

        $dbHost = $io->ask('Database Host: ', self::$defaultDbHost);
        $dbName = $io->ask('Database Name: ', self::$defaultDbName);
        $dbUser = $io->ask('Database User: ', self::$defaultDbUser);
        $dbPass = $io->askAndHideAnswer('Database Pass: ', self::$defaultDbPass);

        $io->write('');

        $wpEnv = $io->ask('Environment name (default `' . self::$defaultWpEnv . '`): ', self::$defaultWpEnv);
        $wpDebug = $io->askConfirmation('Turn on debugging (y/N)? ', self::$defaultWpDebug);

        $io->write('');

        $io->write('Building .env file...');

        if (!self::generateFile($dbHost, $dbName, $dbUser, $dbPass, $wpEnv, $wpDebug)) {
            $io->writeError('Failed to create .env file');
        }

        $io->write('.env created at `' . self::$root . '/.env`');
    }

}
