<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$option = get_option('ivcor_custom_functions');
$task = str_replace('.php', '', basename(__FILE__));

if ( !isset($option) || !isset($option[$task]) || !$option[$task] ) {
    return;
}

/**
 * hooks
 */
add_action('wp_enqueue_scripts', 't204_wp_enqueue_scripts' );
/**
 * end hooks
 */

function t204_wp_enqueue_scripts() {

    $tm_meta = get_post_meta(get_the_ID(), 'tm_meta', true);
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';
    if (is_product()) {
        wp_enqueue_script('t204_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
        if (isset($tm_meta['tmfbuilder']) && isset($tm_meta['tmfbuilder']['selectbox_header_title']))
            wp_localize_script('t204_front_js', 't204', ['tmMeta' => $tm_meta['tmfbuilder']['selectbox_header_title']]);
        else
            wp_localize_script('t204_front_js', 't204', ['tmMeta' => '']);
    }
}