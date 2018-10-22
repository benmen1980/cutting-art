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
add_action( 'woocommerce_variation_prices', 't218_woocommerce_variation_prices', 10, 2 );
/**
 * end hooks
 */

/**
 * @param $transient_cached_prices_array
 * @param $product
 * @return array
 */
function t218_woocommerce_variation_prices($transient_cached_prices_array, $product){

    $product_id = $product->get_id();
    $transient_cached_prices_array_pricelist = [];
    $transient_cached_prices_array_new = [];

    foreach ($transient_cached_prices_array as $key => $prices) {
        foreach ($prices as $variation_id => $price) {
            $transient_cached_prices_array_pricelist[$key][$variation_id] = t218_filter_price($variation_id, $price);
        }
    }

    foreach ($transient_cached_prices_array_pricelist as $key => $prices) {
        foreach ($prices as $variation_id => $price) {
            $transient_cached_prices_array_new[$key][$variation_id] = t208_get_price_with_add_proc($variation_id, $product_id, $price);
        }
    }

    return $transient_cached_prices_array_new;
}

/**
 * @param $variation_id
 * @return float
 */
function t218_filter_price($variation_id, $price){

    $variation = new WC_Product_Variation($variation_id);
    $sku = $variation->get_sku();

    $user_id = get_current_user_id();
    $blog_id = get_current_blog_id();
    $list = get_user_meta($user_id, '_priority_price_list', true);
    $list = $list ? esc_sql($list) : '';
    if ($list) {
        global $wpdb;
        $query = "SELECT price_list_price FROM {$wpdb->prefix}p18a_pricelists WHERE price_list_code = '{$list}' AND product_sku = '{$sku}' AND blog_id = {$blog_id}";
        $price_db = $wpdb->get_var($query);
        $price = $price_db ? floatval($price_db) : $price;
    }

    return $price;
}