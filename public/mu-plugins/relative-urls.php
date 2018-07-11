<?php
namespace WordPress\MuPlugins;

/*
Plugin Name:  Relative URLs
Description:  Makes URLs created by WordPress relative.
Version:      1.0.0
Author:       Neil Sweeney
Author URI:   https://wolfiezero.com/
License:      MIT License
*/

class RelativeUrls
{
    /**
     * List of filter to process.
     *
     * @var array
     */
    protected static $filters = [
        'post_link',
        'post_type_link',
        'page_link',
        'attachment_link',
        'get_shortlink',
        'post_type_archive_link',
        'get_pagenum_link',
        'get_comments_pagenum_link',
        'term_link',
        'search_link',
        'day_link',
        'month_link',
        'year_link',
        'get_stylesheet_uri',
        'script_loader_src',
        'style_loader_src',
    ];

    /**
     * Initialise the class.
     *
     * @return void
     */
    public static function init()
    {
        add_action('template_redirect', [get_called_class(), 'process']);
    }

    /**
     * Loops through the given filters to filter URLs within those.
     *
     * @return void
     */
    public static function process()
    {
        // Don't do anything if:
        // - In feed
        // - In sitemap by WordPress SEO plugin
        if (is_feed() || get_query_var('sitemap')) {
            return;
        }

        foreach (sefl::$filters as $filter) {
            add_filter($filter, [get_called_class(), 'makeRelative']);
        }
    }

    /**
     * Update given strings to be relative.
     *
     * @param string $url
     * @return string
     */
    public static function makeRelative(string $url): string
    {
        $updatedUrl = $url;
        $domain = explode('//', WP_HOME)[1];
        if (strpos($url, $domain)) {
            $updatedUrl = explode($domain, $url)[1];
        }
        return $updatedUrl;
    }
}

\WordPress\MuPlugins\RelativeUrls::init();
