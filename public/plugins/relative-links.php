<?php
/**
 * Plugin Name: Relative Links Zero
 * Description: Makes links relative using WordPress' `wp_make_link_relative()`
 * Version: 1.0.0
 * Author: Neil Sweeney
 * Website: https://wolfiezero.com/
 * License: GPLv2 or later
 * See: https://deluxeblogtips.com/relative-urls/
 */

add_action('template_redirect', ['RelativeLinksZero', 'init']);

class RelativeLinksZero
{
    public static function init()
    {
        // Don't do anything if:
        // - In feed
        // - In sitemap by WordPress SEO plugin
        if (is_feed() || get_query_var( 'sitemap' ))
            return;

        $filters = [
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

        foreach ($filters as $filter) {
            add_filter($filter, [self, 'make_relative']);
        }
    }

    public static function make_relative($url)
    {
        $length = strlen(WP_HOME);

        if (substr($url, 0, $length) === WP_HOME) {
            return str_replace(WP_HOME, '', $url);
        }

        return $url;
    }
}
