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
add_action( 'wp_enqueue_scripts', 't217_admin_enqueue_scripts', 99 );
add_action( 'storefront_header', 't217_storefront_header', 9);

/**
 * end hooks
 */

function t217_admin_enqueue_scripts(){
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';

    wp_enqueue_script('t217_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
    wp_localize_script('t217_front_js', 't216', ['ajaxUrl' => admin_url('admin-ajax.php')]);

    $price_display = get_user_meta(get_current_user_id(), '_price_display', true);
    if (get_user_meta(get_current_user_id(), '_hide_prices', true) === '1' || $price_display === 'hide')
        wp_deregister_script('wm_variation_price_hints_script');
}

function t217_storefront_header(){

    $option = "";
    $price_display = get_user_meta(get_current_user_id(), '_price_display', true);
    if (get_user_meta(get_current_user_id(), '_hide_prices', true) === '1' || $price_display === 'hide')
        $option = 'hide';
    if ($price_display === 'regular') $option = 'regular';
    if ($price_display === 'retail') $option = 'retail';
echo "<pre hidden>";
print_r($option);
echo "</pre>";
?>
    <style>
        #t217_price_dropdown {
            float: right;
            margin-left: 20px;
            margin-bottom: 10px;
        }
    </style>
    <select id="t217_price_dropdown">
        <!--<option value="" <?=selected("", $option, 0)?>>Price Display</option>-->
        <option value="regular" <?=selected("regular", $option, 0)?>>Regular Prices (Costs)</option>
        <option value="hide" <?=selected("hide", $option, 0)?>>Hide Prices</option>
        <option value="retail" <?=selected("retail", $option, 0)?>>Retail Prices</option>
    </select>
<?php
}