<?php
// =============================================================================
// WordPress Config
// =============================================================================

// Get the composer files
require_once(__DIR__ . '/../vendor/autoload.php');

// Detect the environment
$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();
$dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST']);

// General Configs
// =============================================================================

// Set the home url to the current domain
define('WP_HOME', env('WP_HOME', host()));

// Directory where WordPress will be located
define('WP_DIRECTORY', env('WP_DIRECTORY', 'wp'));

// Custom WordPress directory
define('WP_SITEURL', env('WP_SITEURL', WP_HOME . '/' . WP_DIRECTORY));

// Custom content directory
define('WP_CONTENT_DIR', env('WP_CONTENT_DIR', __DIR__));
define('WP_CONTENT_URL', env('WP_CONTENT_URL', WP_HOME));

// Set the trash to less days to optimize WordPress
define('EMPTY_TRASH_DAYS', env('EMPTY_TRASH_DAYS', 7));

// Set the default WordPress theme
define('WP_DEFAULT_THEME', env('WP_THEME', 'app'));

// Specify the Number of Post Revisions
define('WP_POST_REVISIONS', env('WP_POST_REVISIONS', 2));

// WordPress environment
define('WP_ENV', env('WP_ENV', 'production'));

// Cleanup image edits
define('IMAGE_EDIT_OVERWRITE', env('IMAGE_EDIT_OVERWRITE', true));

// Prevent file edit from the dashboard
define('DISALLOW_FILE_EDIT', env('DISALLOW_FILE_EDIT', true));

// WordPess Database Connection Details
// =============================================================================

define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASSWORD', env('DB_PASSWORD'));
define('DB_HOST', env('DB_HOST'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8'));
define('DB_COLLATE', env('DB_COLLATE', ''));

// WordPress database table prefix
$table_prefix = env('TABLE_PREFIX', 'wp_');

// Authentication Unique Keys and Salts
// =============================================================================
// Change these to different unique phrases! It should be handled by the `.env`
// file when generated but you can generate these using the following link
// - https://api.wordpress.org/secret-key/1.1/salt/
//
// You can change these at any point in time to invalidate all existing cookies.
// This will force all users to have to log in again.

define('AUTH_KEY', env('AUTH_KEY'));
define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
define('NONCE_KEY', env('NONCE_KEY'));
define('AUTH_SALT', env('AUTH_SALT'));
define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
define('NONCE_SALT', env('NONCE_SALT'));

// For developers: WordPress debugging mode
// =============================================================================
// Change this to true to enable the display of notices during development.
// It is strongly recommended that plugin and theme developers use WP_DEBUG
// in their development environments.

define('WP_DEBUG', env('WP_DEBUG', false));
define('WP_DEBUG_DISPLAY', env('WP_DEBUG', false));
define('SCRIPT_DEBUG', env('WP_DEBUG', false));

// Absolute path to the WordPress directory
// =============================================================================

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/' . WP_DIRECTORY);
}

require_once(ABSPATH . 'wp-settings.php');

// Functions
// =============================================================================

/**
 * Returns env value or default value if none avaliable.
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function env(string $key, string $default = null) : string {
    $value = getenv($key);
    if (!$value) {
        return $default;
    }
    return $value;
}

/**
 * Returns `true` if the site is serverd via a secure port.
 *
 * @return boolean
 */
function isSecure() : bool {
    $isSecure = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        return true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        return true;
    }
    return false;
}

/**
 * Returns the full host name including protocol.
 *
 * @return string
 */
function host() : string {
    $host = 'http://localhost';
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = 'http';

        if (isSecure()) {
            $host .= 's';
        }

        $host = '://' . $_SERVER['HTTP_HOST'];
    }

    return $host;
}
