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
add_action( "wp_enqueue_scripts", "t158_enqueue_scripts" );
/**
 * end hooks
 */

function t158_enqueue_scripts() {
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';

    wp_enqueue_script('t158_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
}

