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
add_action( 'product_cat_add_form_fields', 't226_product_cat_add_form_fields', 100, 2);
add_action( 'product_cat_edit_form_fields', 't226_product_cat_edit_form_fields', 100, 2);

add_action( 'edited_product_cat', 't226_save_product_cat', 11, 2);
add_action( 'create_product_cat', 't226_save_product_cat', 11, 2);

add_filter( 't208_get_materials_cat', 't226_get_materials_cat' );
/**
 * end hooks
 */

function t226_product_cat_add_form_fields() {
    ?>
    <div class="form-field">
        <label for="attribute_display_category"><?php esc_html_e( 'Display in Retail Price Management?', 'woocommerce' ); ?></label>
        <input type="checkbox" name="attribute_display_category" id="attribute_display_category">
        <p class="description"><?php esc_html_e( '', 'woocommerce' ); ?></p>
    </div>
    <?php
}

function t226_product_cat_edit_form_fields($term) {

    global $wpdb;

    $term_id = $term->term_id;
    $table_name = "{$wpdb->prefix}woocommerce_termmeta";

    $attribute_display_category_in_retail_management = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE woocommerce_term_id = $term_id AND meta_key = '_attribute_display_category_in_retail_management'");

    if (is_null($attribute_display_category_in_retail_management)) $attribute_display_category_in_retail_management = 0;

    ?>
    <tr class="form-field form-required">
        <th scope="row" valign="top">
            <label for="attribute_display_category_in_retail_management"><?php esc_html_e( 'Display in Retail Price Management?', 'woocommerce' ); ?></label>
        </th>
        <td>
            <input name="attribute_display_category_in_retail_management" id="attribute_display_category_in_retail_management" type="checkbox" <?=checked($attribute_display_category_in_retail_management, 1)?> value="1" />
            <p class="description"><?php esc_html_e( '', 'woocommerce' ); ?></p>
        </td>
    </tr>
    <?php
}

function t226_save_product_cat($term_id) {
    global $wpdb;

    $table_name = "{$wpdb->prefix}woocommerce_termmeta";
    $meta_key = "_attribute_display_category_in_retail_management";
    $meta_value = isset($_POST['attribute_display_category_in_retail_management']) ? 1 : 0;

    $meta_id = $wpdb->get_var("SELECT meta_id FROM $table_name WHERE woocommerce_term_id = '$term_id' AND meta_key = '$meta_key'");

    if (is_null($meta_id)) {
        $wpdb->insert( $table_name,
            array( 'woocommerce_term_id' => $term_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value )
        );
    } else {
        $wpdb->update( $table_name,
            array( 'meta_key' => $meta_key, 'woocommerce_term_id' => $term_id, 'meta_value' => $meta_value ),
            array( 'meta_id' => $meta_id )
        );
    }
}

function t226_get_materials_cat($terms) {

    global $wpdb;
    $table_name = "{$wpdb->prefix}woocommerce_termmeta";
    $terms_db = $wpdb->get_col( "SELECT DISTINCT woocommerce_term_id FROM $table_name WHERE meta_value = 1 AND meta_key = '_attribute_display_category_in_retail_management'");
    $terms_new = [];
    if ($terms_db)
        $terms_new = array_intersect($terms_db, $terms);

    return $terms_new;
}