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
add_action('woocommerce_variation_options', 't198_woocommerce_variation_options', 10, 3);
add_action('woocommerce_before_order_itemmeta', 't198_woocommerce_before_order_itemmeta', 10, 3);
add_action('woocommerce_save_product_variation', 't198_woocommerce_save_product_variation', 10, 1 );
/**
 * end hooks
 */

function t198_woocommerce_variation_options($loop, $variation_data, $variation){
?>
    <p class="form-field variable_product_code[<?=$loop?>]_field form-row form-row-last">
        <label for="variable_product_code[<?=$loop?>]">
            <abbr title="Product Code">Product Code</abbr>
        </label>
        <input type="text" class="short" style="" name="variable_product_code[<?=$loop?>]" id="variable_product_code<?=$loop?>" value="<?=get_post_meta($variation->ID,'product_code', true)?>" placeholder="">
    </p>
<?php
}

function t198_woocommerce_before_order_itemmeta($item_id, $item, $product){

    if ( method_exists($item, 'get_variation_id') ) {
        $variation_id = $item->get_variation_id();
        ?>
        <div class="wc-order-item-variation"><strong>Product Code:</strong> <?=get_post_meta($variation_id,'product_code', true)?></div>
        <?php
    }
}

function t198_woocommerce_save_product_variation($variation_id){
    $product_code = $_POST["variable_product_code"];
    if(!empty($product_code))
        update_post_meta( $variation_id, 'product_code', sanitize_text_field($product_code[0]) );
}