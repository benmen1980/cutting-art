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
//add_action( 'woocommerce_after_my_account', 't208_woocommerce_after_my_account_hide_prices', 9 );
add_action( 'woocommerce_after_my_account', 't208_woocommerce_after_my_account_retail_price_by_category', 11 );
/**
 * t209
 */
add_action( 'woocommerce_after_my_account', 't208_woocommerce_after_my_account_retail_price_by_variation', 12 );
/**
 * /t209
 */

add_action( 'wp_enqueue_scripts', 't208_wp_enqueue_scripts' );

add_filter( 'woocommerce_order_amount_line_subtotal', 't208_woocommerce_order_formatted_line_subtotal', 10, 3);
add_filter( 'woocommerce_order_subtotal_to_display', 't208_woocommerce_order_subtotal_to_display', 10, 3);

add_action( 'woocommerce_checkout_create_order_line_item', 't208_woocommerce_checkout_create_order_line_item', 10, 4);
add_action( 'woocommerce_order_details_after_order_table', 't208_woocommerce_order_details_after_order_table', 10);
/**
 * end hooks
 */

function t208_woocommerce_order_subtotal_to_display(  $subtotal, $compound, $order ){
    $tax_display = '';
    $tax_display = $tax_display ? $tax_display : get_option( 'woocommerce_tax_display_cart' );
    $subtotal    = 0;

    if ( ! $compound ) {
        foreach ( $order->get_items() as $item ) {
            $subtotal += t208_get_price_with_add_proc($item->get_variation_id(), $item->get_product_id(), $item->get_subtotal());

            if ( 'incl' === $tax_display ) {
                $subtotal += $item->get_subtotal_tax();
            }
        }

        $subtotal = wc_price( $subtotal, array( 'currency' => $order->get_currency() ) );

        if ( 'excl' === $tax_display && $order->get_prices_include_tax() && wc_tax_enabled() ) {
            $subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
        }
    } else {
        if ( 'incl' === $tax_display ) {
            return '';
        }

        foreach ( $order->get_items() as $item ) {
            $subtotal += $item->get_subtotal();
        }

        // Add Shipping Costs.
        $subtotal += $order->get_shipping_total();

        // Remove non-compound taxes.
        foreach ( $order->get_taxes() as $tax ) {
            if ( $tax->is_compound() ) {
                continue;
            }
            $subtotal = $subtotal + $tax->get_tax_total() + $tax->get_shipping_tax_total();
        }

        // Remove discounts.
        $subtotal = $subtotal - $order->get_total_discount();
        $subtotal = wc_price( $subtotal, array( 'currency' => $order->get_currency() ) );
    }

    return $subtotal;
}

function t208_woocommerce_order_formatted_line_subtotal($price, $order, $item){

    $variation_id = $item->get_variation_id();
    $product_id = $item->get_product_id();

    return t208_get_price_with_add_proc($variation_id, $product_id, $price);
}

function t208_get_price_with_add_proc($variation_id, $product_id, $price){
    $price_display = get_user_meta(get_current_user_id(), '_price_display', true);
    if ($price_display === 'retail') {
        $product = new WC_Product($product_id);
        $categories = $product->get_category_ids();
        $user_id = get_current_user_id();
        $price_proc = get_user_meta($user_id, 'wcdpm_retail_price_proc', true);
        $price_proc = $price_proc ? $price_proc : 0;
        $price_out = $price;

        if ($categories && is_array($categories)) {
            $price_proc_by_cat = get_user_meta($user_id, '_retail_price_category', true);

            global $wpdb;
            $table_name = "{$wpdb->prefix}woocommerce_termmeta";

            foreach ($categories as $term_id) {

                $attribute_display_category_in_retail_management = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE woocommerce_term_id = $term_id AND meta_key = '_attribute_display_category_in_retail_management'");

                if ($attribute_display_category_in_retail_management === '1') {

                    $retail_price = get_user_meta($user_id, '_retail_price_addition_by_' . $term_id, true);
                    $new_price = get_user_meta($user_id, '_new_retail_price_by_' . $term_id, true);

                    if (isset($new_price[$variation_id]) && $new_price[$variation_id]) {
                        $price_out = $new_price[$variation_id];
                    } else if (isset($retail_price[$variation_id]) && $retail_price[$variation_id]) {
                        $price_out = floatval($price + $price * floatval($retail_price[$variation_id]) / 100);
                    } else if (isset($price_proc_by_cat[$term_id]) && $price_proc_by_cat[$term_id]) {
                        $price_out = floatval($price + $price * floatval($price_proc_by_cat[$term_id]) / 100);
                    }
                }
            }

            if ($price_out === $price)
                $price_out = floatval($price + $price * floatval($price_proc) / 100);
        }

        return $price_out;
    }else{
        return $price;
    }
}

/**
 * recalculate total
 * @param $order
 */
function t208_woocommerce_order_details_after_order_table($order){
    $order->calculate_taxes();
    $order->calculate_totals( false );
}

/**
 * update subtotal, total
 * @param $item
 * @param $cart_item_key
 * @param $values
 * @param $order
 */
function t208_woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order){
    $variation_id = $item->get_variation_id();
    $variable_product = wc_get_product($variation_id);
    $total = $variable_product->get_regular_price();
    $sku = $variable_product->get_sku();

    /**
     * t217
     */
    if (get_user_meta(get_current_user_id(), '_price_display', true) !== '') {
    /**
     * end t217
     */
        $user_id = get_current_user_id();
        $blog_id = get_current_blog_id();
        $list = get_user_meta($user_id, '_priority_price_list', true);
        $list = $list ? esc_sql($list) : '';
        if ($list) {
            global $wpdb;
            $query = "SELECT price_list_price FROM {$wpdb->prefix}p18a_pricelists WHERE price_list_code = '{$list}' AND product_sku = '{$sku}' AND blog_id = {$blog_id}";
            $total_db = $wpdb->get_var($query);
            $total = $total_db ? floatval($total_db) : $total;
        }
    /**
     * t217
     */
    }
    /**
     * end t217
     */

    $item->set_props(
        array(
            'quantity'     => $values['quantity'],
            'variation'    => $values['variation'],
            'subtotal'     => $total,
            'total'        => $total
        )
    );
}

function t208_wp_enqueue_scripts(){
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';
    if (is_account_page()) {
        wp_enqueue_script('t208_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
        wp_localize_script('t208_front_js', 't208', ['ajaxUrl' => admin_url('admin-ajax.php')]);
    }
    if (get_user_meta(get_current_user_id(), '_hide_prices', true))
        wp_enqueue_style('t208_front_css', $path_assets . 'css/front.css', [], $ver);

}

function t208_woocommerce_after_my_account_hide_prices() {
    if (current_user_can('customer') || current_user_can('administrator') || current_user_can('shop_manager')) {
        if (isset($_GET['retail_price_by_cat']) && get_term(intval($_GET['retail_price_by_cat']))) {
            echo '<style>.wc_dpm_woocommerce_after_my_account {display: none;}</style>';
            $url = wc_get_page_permalink( 'myaccount' );
            echo "<a href='{$url}' style='cursor: pointer; padding: 0 10px;' class='woocommerce-button button'>Back</a>";
            remove_action('woocommerce_after_my_account', 't208_woocommerce_after_my_account_retail_price_by_category', 11);
        }else {
            echo "<div class='row'>";
            echo "<h3>Retail Price</h3>";
            $checked = checked(get_user_meta(get_current_user_id(), '_hide_prices', true), 1, false);
            echo "<input type='checkbox' {$checked}/> Hide Prices";
            echo " <a style='cursor: pointer; padding: 0 10px;' class='woocommerce-button button input-hide-price-for-user-save'>Save</a>";
            echo "</div><br>";
        }
    }
}

function t208_woocommerce_after_my_account_retail_price_by_category() {
    if (current_user_can('customer') || current_user_can('administrator') || current_user_can('shop_manager')) {

        $material_cat = get_term_by('name', 'Jewelry Type & Material', 'product_cat' );
        $materials_cat = apply_filters('t208_get_materials_cat', get_term_children($material_cat->term_id, 'product_cat'), $material_cat);

        $update = get_user_meta(get_current_user_id(), '_retail_price_category', true);
        $update = $update ? $update : [];

        echo "<div class='row'>";
        echo "<h3>Retail Price by Category</h3>";
        echo "<table style='max-height: 300px; display: block; overflow-y: scroll'>";
            echo "<thead><th>Update by Category?</th><th>Category</th><th>Retail Price Addition</th></thead>";
            echo "<tbody>";
                foreach ($materials_cat as $term_id) {
                    $term = get_term($term_id);
                    echo "<tr>";
                        echo "<td><input class='update_category' term_id='{$term_id}' type='checkbox'/></td>";
                        echo "<td>{$term->name}</td>";
                        $proc = isset($update[$term_id]) ? $update[$term_id] : 0;
                        echo "<td class='retail_price_addition' term_id='{$term_id}' proc='{$proc}'>{$proc}%</td>";
                    echo "</tr>";
                }
            echo "</tbody>";
        echo "</table>";
        echo " <a style='cursor: pointer; padding: 0 10px;' class='woocommerce-button button table-retail-price-category-for-user-save'>Save</a>";
        echo "</div>";
    }
}

/**
 * t209
 */
function t208_woocommerce_after_my_account_retail_price_by_variation(){
    if (current_user_can('customer') || current_user_can('administrator') || current_user_can('shop_manager')) {
        if (isset($_GET['retail_price_by_cat']) && $term = get_term(intval($_GET['retail_price_by_cat']))) {

            global $wpdb;
            $table_name = "{$wpdb->prefix}woocommerce_termmeta";
            $term_id = $term->term_id;

            $attribute_display_category_in_retail_management = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE woocommerce_term_id = $term_id AND meta_key = '_attribute_display_category_in_retail_management'");

            if ($attribute_display_category_in_retail_management === '1') {

                $args = [
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [[
                        'taxonomy' => 'product_cat',
                        'fields' => 'term_id',
                        'terms' => $term->term_id,
                        'operator' => 'IN'
                    ]]
                ];

                $products = new WP_Query($args);
                $products = $products->get_posts();

                $retail_price = get_user_meta(get_current_user_id(), '_retail_price_addition_by_' . $term->term_id, true);
                $retail_price = $retail_price ? $retail_price : [];

                $new_price = get_user_meta(get_current_user_id(), '_new_retail_price_by_' . $term->term_id, true);
                $new_price = $new_price ? $new_price : [];

                echo "<div class='row'>";
                echo "<h3>Retail Price by Category - {$term->name}</h3>";
                echo "<div class='table-container' style='overflow: auto; margin-bottom: 20px'>";
                echo '<style>.table-container th, .table-container td {max-width: 120px; min-width: 120px; padding: 10px}</style>';
                echo "<table>";
                echo "<thead style='display: block'>";
                echo "<th>Product Image</th>";
                echo "<th>SKU</th>";
                echo "<th>Product Code</th>";
                echo "<th>Product Description</th>";
                echo "<th>Price (by price list)</th>";
                echo "<th>Current Retail Price Addition (%)</th>";
                echo "<th>Update Price by Percents?</th>";
                echo "<th>Retail Price Addition (%)</th>";
                echo "<th>Update Price Manually?</th>";
                echo "<th>New Retail Price</th>";
                echo "<th>Currency</th>";
                echo "</thead>";
                echo "<tbody style='display: block; overflow: auto; height: 500px;'>";
                $meta = get_user_meta(get_current_user_id(), '_priority_price_list', true);
                foreach ($products as $product) {
                    $product = new WC_Product_Variable($product);
                    $available_variations = $product->get_available_variations();

                    foreach ($available_variations as $available_variation) {
                        $list = empty($meta) ? 'no-selected' : $meta;

                        $data_price_list = $GLOBALS['wpdb']->get_results('
                        SELECT *
                        FROM ' . $GLOBALS['wpdb']->prefix . 'p18a_pricelists
                        WHERE product_sku = "' . $available_variation['sku'] . '"' .
                            (($list != 'no-selected') ? ('AND price_list_code = "' . esc_sql($list) . '"') : ''),
                            ARRAY_A
                        );

                        $data_price_list = $data_price_list ? $data_price_list[0] : [];
                        echo "<tr>";
                        echo "<td>{$product->get_image()}</td>";
                        echo "<td>{$available_variation['sku']}</td>";
                        $product_code = get_post_meta($available_variation['variation_id'], 'product_code', true);
                        echo "<td>{$product_code}</td>";
                        echo "<td>{$product->get_name()}</td>";
                        if ($list != 'no-selected') {
                            $price_list_price = $data_price_list ? $data_price_list['price_list_price'] : '';
                        } else {
                            $price_list_price = floatval($product->get_price());
                        }
                        echo "<td>{$price_list_price}</td>";
                        $price_proc_by_cat = get_user_meta(get_current_user_id(), '_retail_price_category', true);
                        if ($price_proc_by_cat && isset($price_proc_by_cat[$term->term_id]) && $price_proc_by_cat[$term->term_id]) {
                            $price_proc = floatval($price_proc_by_cat[$term->term_id]);
                        } else {
                            $price_proc = get_user_meta(get_current_user_id(), 'wcdpm_retail_price_proc', true);
                        }
                        echo "<td>{$price_proc}%</td>";
                        echo "<td><input class='update_price_by_percents' type='checkbox' variation_id='{$available_variation['variation_id']}'></td>";
                        $proc = isset($retail_price[$available_variation['variation_id']]) ? $retail_price[$available_variation['variation_id']] : 0;
                        $price = isset($new_price[$available_variation['variation_id']]) ? $new_price[$available_variation['variation_id']] : 0;
                        echo "<td class='retail_price_addition' variation_id='{$available_variation['variation_id']}' proc='{$proc}'>{$proc}%</td>";
                        echo "<td><input class='update_price_manually' type='checkbox' variation_id='{$available_variation['variation_id']}'></td>";
                        echo "<td class='new_retail_price' variation_id='{$available_variation['variation_id']}' price='{$price}'>{$price}</td>";
                        $price_list_currency = $data_price_list ? $data_price_list['price_list_currency'] : '';
                        echo "<td>{$price_list_currency}</td>";
                        echo "</tr>";

                    }
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo " <a term-id='{$term->term_id}' style='cursor: pointer; padding: 0 10px;' class='woocommerce-button button table-retail-price-category-any-for-user-save'>Save</a>";
                echo "</div>";
            }else{
                $material_cat = get_term_by('name', 'Jewelry Type & Material', 'product_cat');
                $materials_cat = apply_filters('t208_get_materials_cat', get_term_children($material_cat->term_id, 'product_cat'), $material_cat);

                echo "<div class='row'>";
                echo "<h3>Retail Price by Category</h3>";
                echo "<table style='width: 250px; margin: 0 auto; max-height: 300px; display: block; overflow-y: scroll'>";
                echo "<thead><th>Category</th></thead>";
                echo "<tbody>";
                foreach ($materials_cat as $term_id) {
                    $term = get_term($term_id);

                    echo "<tr>";
                    echo "<td><a href='?retail_price_by_cat={$term_id}'>{$term->name}</a></td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            }
        }else{
            $material_cat = get_term_by('name', 'Jewelry Type & Material', 'product_cat');
            $materials_cat = apply_filters('t208_get_materials_cat', get_term_children($material_cat->term_id, 'product_cat'), $material_cat);

            echo "<div class='row'>";
            echo "<h3>Retail Price by Category</h3>";
            echo "<table style='width: 250px; margin: 0 auto; max-height: 300px; display: block; overflow-y: scroll'>";
            echo "<thead><th>Category</th></thead>";
            echo "<tbody>";
            foreach ($materials_cat as $term_id) {
                $term = get_term($term_id);

                echo "<tr>";
                echo "<td><a href='?retail_price_by_cat={$term_id}'>{$term->name}</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        }
    }
}
/**
 * /t209
 */