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
add_filter('woocommerce_account_menu_items', 't155_woocommerce_account_menu_items', 10, 1);
/**
 * end hooks
 */

function t155_woocommerce_account_menu_items( $items ) {
    unset($items['downloads']);

    $newItems['dashboard'] = $items['dashboard'];
    $newItems['orders'] = $items['orders'];
    $newItems['edit-account'] = $items['edit-account'];
    $newItems['edit-address'] = $items['edit-address'];
    $newItems['customer-logout'] = $items['customer-logout'];

    return $newItems;
}