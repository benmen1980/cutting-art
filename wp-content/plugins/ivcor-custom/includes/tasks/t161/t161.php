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

add_filter('woocommerce_catalog_orderby', 't161_woocommerce_catalog_orderby', 20 );
add_action('wp_loaded', 't161_wp_loaded');
/**
 * end hooks
 */

function t161_woocommerce_catalog_orderby($orderby) {
    $orderby['popularity'] = 'Best Sellers';
    $orderby['date'] = 'What’s New';
    $orderby['price'] = 'Price: Low to High';
    $orderby['price-desc'] = 'Price: High to Low';

    unset($orderby['rating']);
    return $orderby;
}

function t161_wp_loaded() {
    remove_action( 'woocommerce_after_shop_loop',        'storefront_sorting_wrapper',               9 );
    remove_action( 'woocommerce_after_shop_loop',        'woocommerce_catalog_ordering',             10 );
    remove_action( 'woocommerce_after_shop_loop',        'woocommerce_result_count',                 20 );
    /**
     * t191
     */
    //remove_action( 'woocommerce_after_shop_loop',        'woocommerce_pagination',                   30 );
    /**
     * end t191
     */
    remove_action( 'woocommerce_after_shop_loop',        'storefront_sorting_wrapper_close',         31 );
}