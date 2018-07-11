<?php
// `development`
define('WP_DEBUG_DISPLAY', env('WP_DEBUG') ?: false);
define('SCRIPT_DEBUG', env('WP_DEBUG') ?: false);
define('WP_DEBUG', env('WP_DEBUG') ?: false);
/** Disable all file modifications including updates and update notifications */
define('DISALLOW_FILE_MODS', true);
