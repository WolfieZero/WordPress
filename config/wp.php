<?php

/**
 * @var string Directory containing all of the site's files
 */
$rootDir = dirname(__DIR__);

/**
 * @var string Document Root
 */
$webRootDir = $rootDir . '/public';

/**
 * Expose global env() function from oscarotero/env
 * =============================================================================
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 * =============================================================================
 */
$dotenv = new Dotenv\Dotenv($rootDir);
if (file_exists($rootDir . '/.env')) {
    $dotenv->load();
    $dotenv->required([
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
        'WP_HOME',
        'WP_SITEURL',
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT'
    ]);
}

/**
 * Set up our global environment constant and load its config first
 * =============================================================================
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

$envConfig = __DIR__ . '/environments/' . WP_ENV . '.php';
if (file_exists($envConfig)) {
    require_once $envConfig;
}

if (! defined('WP_DEBUG') || ! WP_DEBUG) {
    ini_set('display_errors', 0);
}

/**
 * Theme
 * =============================================================================
 */
define('WP_DEFAULT_THEME', env('WP_THEME') ?: 'lumberjack');

/**
 * URLs
 * =============================================================================
 */
define('WP_HOME', env('WP_HOME'));
define('WP_SITEURL', env('WP_SITEURL'));

/**
 * Custom Content Directory
 * =============================================================================
 */
define('WP_ROOT', $rootDir);
define('CONTENT_DIR', '');
define('WP_CONTENT_DIR', $webRootDir . CONTENT_DIR);
define('WP_CONTENT_URL', WP_HOME . CONTENT_DIR);

/**
 * DB settings
 * =============================================================================
 */
define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASSWORD', env('DB_PASSWORD'));
define('DB_HOST', env('DB_HOST') ?: 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

/**
 * WP Mail SMTP
 * =============================================================================
 */
define('WPMS_ON', env('WPMS_ON') ?: true);
define('WPMS_SMTP_PASS', env('WPMS_SMTP_PASS') ?: '');

/**
 * Authentication Unique Keys and Salts
 * =============================================================================
 */
define('AUTH_KEY', env('AUTH_KEY'));
define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
define('NONCE_KEY', env('NONCE_KEY'));
define('AUTH_SALT', env('AUTH_SALT'));
define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 * =============================================================================
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
define('DISALLOW_FILE_EDIT', true);

/**
 * Bootstrap WordPress
 * =============================================================================
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webRootDir . '/wp/');
}
