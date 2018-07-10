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
add_action( "admin_enqueue_scripts", "t173_enqueue_scripts" );
add_action( "wp_ajax_t173_update_user_meta", "t173_update_user_meta");
/**
 * end hooks
 */

function t173_enqueue_scripts() {
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';

    $screen = get_current_screen();

    if ($screen->base === 'users') {
        wp_enqueue_script('t173_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
        wp_localize_script('t173_front_js', 't173', ['ajaxUrl' => admin_url('admin-ajax.php')]);
    }
}

function t173_update_user_meta() {
    $user_id = $_POST['user_id'];
    $priority_customer_number = $_POST['priority_customer_number'];
    wp_die(json_encode(update_user_meta($user_id, '_priority_customer_number',$priority_customer_number)));
}