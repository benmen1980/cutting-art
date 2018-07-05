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
add_action( "wp_enqueue_scripts", "t157_enqueue_scripts" );
/**
 * end hooks
 */

function t157_enqueue_scripts() {
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';

    wp_enqueue_script('t157_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
    wp_enqueue_style('t171_front_css', $path_assets . 'css/front.css', $ver);
}

