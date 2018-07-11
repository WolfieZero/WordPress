<?php
namespace WordPress\MuPlugins;

/*
Plugin Name:  ACF JSON Repoint
Description:  Points all ACF JSON syncing to a folder outside the theme.
Version:      1.0.0
Author:       Neil Sweeney
Author URI:   https://wolfiezero.com/
License:      MIT License
*/

class RepointAcfJsonFiles
{
    /**
     * Directory where ACF JSON files will be saved and loaded from.
     *
     * @var string
     */
    protected static $acfJsonDirectory = WP_ROOT . '/acf-json';

    /**
     * Initialise the class.
     *
     * @return void
     */
    public static function init()
    {
        add_filter('acf/settings/save_json', [get_called_class(), 'acfJsonSavePoint']);
        add_filter('acf/settings/load_json', [get_called_class(), 'acfJsonLoadPoint']);
    }

    /**
     * Update the ACF JSON save point
     *
     * @param string $path
     * @return string
     */
    public static function acfJsonSavePoint(string $path): string
    {
        return self::$acfJsonDirectory;
    }

    /**
     * Update the ACF JSON load point
     *
     * @param array $paths
     * @return array
     */
    public static function acfJsonLoadPoint(array $paths): array
    {
        unset($paths[0]);
        $paths[] = WP_ROOT . '/acf-json';
        return $paths;
    }
}

\WordPress\MuPlugins\RepointAcfJsonFiles::init();
