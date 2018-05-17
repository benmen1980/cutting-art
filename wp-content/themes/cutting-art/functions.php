<?php

add_action( 'wp_enqueue_scripts', 'enqueue_script' );
function enqueue_script() {
	wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/js/scripts.js', array( 'jquery' ), '', true );
}

//----- Attributes: remove "Choose an option" -----//
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'wc_remove_options_text');
function wc_remove_options_text( $args ){
	$args['show_option_none'] = '';
	return $args;
}

//----- External name for Attributes ----- //
// Add term page
/*function pippin_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[custom_term_meta]"><?php _e( 'Example meta field', 'pippin' ); ?></label>
		<input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
		<p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
	</div>
<?php
}
add_action( 'product_attributes_add_form_fields', 'pippin_taxonomy_add_new_meta_field', 10, 2 );*/

// Adds a custom rule type.
add_filter( 'acf/location/rule_types', function( $choices ){
    $choices[ __("Other",'acf') ]['wc_prod_attr'] = 'WC Product Attribute';
    return $choices;
} );

// Adds custom rule values.
add_filter( 'acf/location/rule_values/wc_prod_attr', function( $choices ){
    foreach ( wc_get_attribute_taxonomies() as $attr ) {
        $pa_name = wc_attribute_taxonomy_name( $attr->attribute_name );
        $choices[ $pa_name ] = $attr->attribute_label;
    }
    return $choices;
} );

// Matching the custom rule.
add_filter( 'acf/location/rule_match/wc_prod_attr', function( $match, $rule, $options ){
    if ( '==' === $rule['operator'] ) {
        $match = $rule['value'] === $options['ef_taxonomy'];
    } elseif ( '!=' === $rule['operator'] ) {
        $match = $rule['value'] !== $options['ef_taxonomy'];
    }
    return $match;
}, 10, 3 );

function wc_dropdown_variation_attribute_options( $args = array() ) {
    $args = wp_parse_args( apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ), array(
        'options'          => false,
        'attribute'        => false,
        'product'          => false,
        'selected'         => false,
        'name'             => '',
        'id'               => '',
        'class'            => '',
        'show_option_none' => __( 'Choose an option', 'woocommerce' ),
    ) );

    $options               = $args['options'];
    $product               = $args['product'];
    $attribute             = $args['attribute'];
    $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
    $id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
    $class                 = $args['class'];
    $show_option_none      = $args['show_option_none'] ? true : false;
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

    if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
        $attributes = $product->get_variation_attributes();
        $options    = $attributes[ $attribute ];
    }

    $html  = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
    $html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

    if ( ! empty( $options ) ) {
        if ( $product && taxonomy_exists( $attribute ) ) {
            // Get terms if this is a taxonomy - ordered. We need the names too.
            $terms = wc_get_product_terms( $product->get_id(), $attribute, array(
                'fields' => 'all',
            ) );

            foreach ( $terms as $term ) {
                if ( in_array( $term->slug, $options, true ) ) {
                    $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', get_field('external_name', $term->taxonomy . '_' . $term->term_id) ? get_field('external_name', $term->taxonomy . '_' . $term->term_id) : $term->name ) ) . '</option>';
                }
            }
        } else {
            foreach ( $options as $option ) {
                // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                $selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
                $html    .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
            }
        }
    }

    $html .= '</select>';

    echo apply_filters( 'woocommerce_dropdown_variation_attribute_options_html', $html, $args ); // WPCS: XSS ok.
}

//  added by Roy 16.15.18
//show attributes after summary in product single view
add_action('woocommerce_before_add_to_cart_form', function() {
	global $product;
	if($product->get_cross_sell_ids()) {

		$header = '<div><p>'._e('Choose a Material','woocommerce').'</p>';
		echo $header;
		echo '<select name="forma" onchange="location = this.value;">';
		echo '<option disabled selected value>'.$product->get_title().' </option>';

		foreach ( $product->get_cross_sell_ids() as $cross_sell_id ) {
			$crossproduct = wc_get_product( $cross_sell_id );
			$url          = get_permalink( $cross_sell_id );

			//echo '<a href="'. $url .'">'. $crossproduct->get_title() .'</a><br>';
			echo '<option value="' . $url . '">' . $crossproduct->get_title() . '</option>';

		};
		echo '</select></div>';
	}
}, 25);