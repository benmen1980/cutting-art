<?php

/*
Plugin Name: MyPlugin
Plugin URL: http://roi-holdings.com/
Description: Ensure variation combinations are working properly(500) - standard limit is 30
Version: 0.1
Author: Roy Ben Menachem
Author URI: http://roi-holdings.com/
*/

/**
* Ensure variation combinations are working properly - standard limit is 30
*
*/

function woo_custom_ajax_variation_threshold( $qty, $product ) {
return 500;
}
add_filter( 'woocommerce_ajax_variation_threshold', 'woo_custom_ajax_variation_threshold', 10, 2 );


/*  add field to extra options selectbox */
function my_add_extra_choice( $options = array() ) {
	$options[] = array(
		"name"        => "SKU",
		"label"       => __( 'SKU', 'woocommerce-tm-extra-product-options' ),
		"admin_class" => "tm_cell_display",
		"type"        => "selectbox",//selectbox , radiobuttons , checkboxes
		"field"       => array(
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "text",
			"tags"        => array( "class" => "t tm_option_display", "value" => "" ),
			"label"       => __( 'SKU', 'woocommerce-tm-extra-product-options' )
		)
	);

	return $options;
}

add_filter( 'wc_epo_extra_multiple_choices', 'my_add_extra_choice', 50, 1 );


