<?php
/**
 * Plugin Name: Clean
 * Description: Clean's out what WordPress adds to your page.
 * Version: 1.0.0
 * Author: Neil Sweeney
 * Website: https://wolfiezero.com/
 * License: GPLv2 or later
 */

remove_action('wp_head', 'feed_links_extra', 3);

add_action('wp_head', 'ob_start', 1, 0);
add_action('wp_head', function () {
    $pattern = '/.*' . preg_quote(esc_url(get_feed_link('comments_' . get_default_feed())), '/') . '.*[\r\n]+/';
    echo preg_replace($pattern, '', ob_get_clean());
}, 3, 0);

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head', 10);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

add_filter('style_loader_src', 'remove_version_number', 10, 2);
add_filter('script_loader_src', 'remove_version_number', 10, 2);


/**
 * Removes version numbers from enqued files (supresses query strings)
 *
 * @param   string  $src  Source URL
 * @return  string
 */
function remove_version_number($src)
{
    if (strpos($src, '?ver=') || strpos($src, '&ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
