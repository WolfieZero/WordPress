<?php

namespace WordPress\Scripts;

use Composer\Script\Event;

class DatabaseSetup extends ScriptHandler
{
    private static $options = [
        'title' => [
            'ask' => 'WordPress Site Title?',
            'default' => '',
            'type' => 'text',
        ],
        'admin_email' => [
            'ask' => 'Admin Email?',
            'default' => '',
            'type' => 'text',
        ],
        'admin_user' => [
            'ask' => 'Admin User?',
            'default' => '',
            'type' => 'text',
        ],
        'admin_password' => [
            'ask' => 'Admin Password?',
            'default' => '',
            'type' => 'text',
        ],
    ];

    private static $requiredEnvFields = [
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
    ];

    /**
     * Starts the creation of the .env file.
     *
     * @param Event $event
     * @return void
     */
    public static function process(Event $event)
    {
        self::setupHandler($event);
        self::writeHeader('Install WordPress DB');

        if (! file_exists(self::$root . '/.env')) {
            self::writeError('No .env file');
            self::writeFooter();
            return;
        }

        $dotenv = new \Dotenv\Dotenv(__DIR__ . '/..');
        $dotenv->load();
        $dotenv->required(self::$requiredEnvFields);

        $wpcli = 'php wp-cli.phar ';
        $wpcliAffix = '';

        foreach (self::$options as $key => $option) {
            $value = self::query($option['ask'], $option['type']);
            if ($key === 'title') {
                // $value = '"' .$value . '"';
            }
            $wpcliAffix .= ' --' . $key . '="' . $value . '"';
        }

        $url = getenv('WP_HOME');
        $url = str_replace(['http://', 'https://'], '', $url);
        $wpcliAffix .= ' --url="' . $url . '"';

        $isDroppingDb = self::confirm('Drop database?', false);

        self::write('');

        if ($isDroppingDb) {
            echo exec($wpcli . 'db drop --yes');
            self::write('');
        }

        echo exec($wpcli . 'db create');
        self::write('');
        echo exec($wpcli . 'core install ' . $wpcliAffix);
        self::writeFooter();
    }
}
