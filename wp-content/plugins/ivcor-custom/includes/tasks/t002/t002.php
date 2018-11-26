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
add_filter('wc_epo_get_element_for_display', 't002_wc_epo_get_element_for_display', 10, 1);
/**
 * end hooks
 */

function t002_wc_epo_get_element_for_display($element){

    $meta  = get_user_meta(get_current_user_id(), '_priority_price_list');

    $index = 0;
    if(isset($element) && isset($element['extra_multiple_choices']) && isset($element['extra_multiple_choices']['SKU']) && $element['extra_multiple_choices']['SKU']){
        foreach($element['rules_filtered'] as $item){

            $sku = $element['extra_multiple_choices']['SKU'][$index];
            // dont forget to add here the price list by user

            global $wpdb;

            $sqlquery = "SELECT * FROM `".$wpdb->prefix."p18a_pricelists` WHERE `product_sku` = '".$sku."' and `price_list_code` = '".$meta[0]."'"; // pay attention to syntax of sql query!

            $newprice = $wpdb->get_results($sqlquery);

            if($newprice) {
                $keys = array_keys($element['rules_filtered']);
                $element['rules_filtered'][$keys[$index]][0] = $newprice[0]->price_list_price;
                $element['price_rules_filtered'][$keys[$index]][0] = $newprice[0]->price_list_price;
                $element['original_rules_filtered'][$keys[$index]][0] = $newprice[0]->price_list_price;
                $element['price_per_currencies']['GBP'][0] = $newprice[0]->price_list_price;
                $element['price_per_currencies']['GBP'][$keys[$index]][0] = $newprice[0]->price_list_price;
                $element['builder']['multiple_selectbox_options_price'][0][0] = $newprice[0]->price_list_price;
                $element['rules'][$keys[$index]][0] = $newprice[0]->price_list_price;
                $element['price_rules'][$keys[$index]][0] = $newprice[0]->price_list_price;
            }
            $index ++;
        }
    }

    return $element;
}