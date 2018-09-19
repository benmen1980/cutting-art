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
add_filter('woocommerce_before_add_to_cart_form', 't001_woocommerce_before_add_to_cart_form', 25);
add_filter('woocommerce_dropdown_variation_attribute_options_args', 't001_woocommerce_dropdown_variation_attribute_options_args');
add_action('wp_enqueue_scripts', 't001_wp_enqueue_scripts' );
/**
 * end hooks
 */

function t001_woocommerce_before_add_to_cart_form() {
    global $product;
    if($product->get_cross_sell_ids()) {

        $header = '<div>' . _e('Choose a Material', 'woocommerce') . '';
        /**
         * t206
         */
        $material_cat = get_term_by('slug', 'material', 'product_cat' );
        $materials_cat = get_term_children($material_cat->term_id, 'product_cat');

        $category_ids = $product->get_category_ids();

        $array_diff = array_intersect($materials_cat, $category_ids);

        if (!empty($array_diff)) {
            $cat_id = array_shift($array_diff);
            $title = get_term($cat_id, 'product_cat')->name;
        } else {
            $title = $product->get_title();
        }
        /**
         * end t206
         */

        echo $header;
        echo '<select name="forma" onchange="location = this.value;">';
        echo '<option disabled selected value>' . $title . ' </option>';

        foreach ($product->get_cross_sell_ids() as $cross_sell_id) {
            if (!is_array($cross_sell_id)) {
                $cross_product = new WC_Product($cross_sell_id);
                /**
                 * t206
                 */

                $category_ids = $cross_product->get_category_ids();

                $array_diff = array_intersect($materials_cat, $category_ids);

                if (!empty($array_diff)) {
                    $cat_id = array_shift($array_diff);
                    $title = get_term($cat_id, 'product_cat')->name;
                } else {
                    $title = $cross_product->get_title();
                }
                /**
                 * end t206
                 */
                $url = get_permalink($cross_sell_id);

                echo '<option value="' . $url . '">' . $title . '</option>';
            }

        };
        echo '</select></div>';
    }
}

function t001_woocommerce_dropdown_variation_attribute_options_args( $args ){
    $args['show_option_none'] = '';
    return $args;
}

function t001_wp_enqueue_scripts(){
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';
    if (is_product()) {
        wp_enqueue_script('t001_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
    }
}