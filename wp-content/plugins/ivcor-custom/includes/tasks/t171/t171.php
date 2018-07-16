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
add_action( "wp_enqueue_scripts", "t171_enqueue_scripts" );
//add_action('woocommerce_checkout_fields', 't171_woocommerce_checkout_fields');
/**
 * end hooks
 */

function t171_enqueue_scripts() {
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';

    if (is_checkout()) {
        wp_enqueue_script('t171_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
        wp_enqueue_style('t171_front_css', $path_assets . 'css/front.css', $ver);
    }
}

function t171_woocommerce_checkout_fields( $fields ) {
    unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_email']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    unset($fields['order']['order_comments']);
    return $fields;
}