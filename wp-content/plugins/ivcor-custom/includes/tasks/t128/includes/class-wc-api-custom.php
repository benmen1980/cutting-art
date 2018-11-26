<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 23.05.2018
 * Time: 8:51
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WC_API_CUSTOM {

    /**
     * WC_API_CUSTOM version.
     */
    public $version = '0.0.1';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * @return WC_API_CUSTOM|WooCommerce
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WC_API_CUSTOM constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
        add_action( 'wp_enqueue_scripts', array($this, 'wp_enqueue_scripts') );

        add_action( 'admin_menu', [$this, 'admin_menu'], 99);

        add_action( 'product_cat_add_form_fields', [$this, 'product_cat_add_form_fields'], 99, 2);
        add_action( 'product_cat_edit_form_fields', [$this, 'product_cat_edit_form_fields'], 99, 2);

        add_action('edited_product_cat', [$this, 'save_product_cat'], 10, 2);
        add_action('create_product_cat', [$this, 'save_product_cat'], 10, 2);

        add_filter('raw_woocommerce_price', [$this, 'raw_woocommerce_price'], 1, 1);
    }

    /**
     * WP Enqueue Scripts
     */
    public function wp_enqueue_scripts() {

        if (!is_admin()) {
            global $product;
            global $wpdb;
            $query = new WP_Query([
                'name' => $product,
                'post_type' => 'product',
                'post_parent' => 0,
                'fields' => 'ids'
            ]);
            $product_id = $query->posts[0];

            $wc_product = new WC_Product($product_id);
            $attributes = $wc_product->get_category_ids();

            $gold = 0;
            foreach ($attributes as $attribute_id) {
                $gold = $wpdb->get_var(
                    "SELECT meta_value FROM {$wpdb->prefix}woocommerce_termmeta WHERE woocommerce_term_id = {$attribute_id} AND meta_key = '_attribute_gold_price'"
                );
                if ($gold)
                    break;
            }

            if ($gold) {
                $options = get_option('cutting_art_gold_price');
                $extra_proc = $options['extra_proc'] ? $options['extra_proc'] : 0;
            } else {
                $extra_proc = 0;
            }
        } else {
            $extra_proc = 0;
        }

        wp_localize_script('jquery', 'wcac', ['retailPriceProc' => $extra_proc, 'ajaxUrl' => admin_url('admin-ajax.php')]);
        wp_enqueue_script('wcac-front-wc-js',   plugin_dir_url(dirname(__FILE__, 1)).'assets/js/front-wc.js', ['jquery'], $this->version, true);
    }

    public function product_cat_add_form_fields() {
        ?>
        <div class="form-field">
            <label for="attribute_gold_price"><?php esc_html_e( 'Gold Price', 'woocommerce' ); ?></label>
            <input type="checkbox" name="attribute_gold_price" id="attribute_gold_price">
            <p class="description"><?php esc_html_e( '', 'woocommerce' ); ?></p>
        </div>
        <?php
    }

    public function product_cat_edit_form_fields($term) {

        global $wpdb;

        $term_id = $term->term_id;
        $table_name = "{$wpdb->prefix}woocommerce_termmeta";

        $attribute_gold_price = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE woocommerce_term_id = $term_id AND meta_key = '_attribute_gold_price'");

        ?>
        <tr class="form-field form-required">
            <th scope="row" valign="top">
                <label for="attribute_gold_price"><?php esc_html_e( 'Gold Price', 'woocommerce' ); ?></label>
            </th>
            <td>
                <input name="attribute_gold_price" id="attribute_gold_price" type="checkbox" <?=checked($attribute_gold_price, 1)?> value="1" />
                <p class="description"><?php esc_html_e( '', 'woocommerce' ); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save_product_cat($term_id) {
        global $wpdb;

        $table_name = "{$wpdb->prefix}woocommerce_termmeta";
        $meta_key = "_attribute_gold_price";
        $meta_value = isset($_POST['attribute_gold_price']) ? 1 : 0;

        $meta_id = $wpdb->get_var("SELECT meta_id FROM $table_name WHERE woocommerce_term_id = '$term_id' AND meta_key = '$meta_key'");

        if (is_null($meta_id)) {
            $wpdb->insert( $table_name,
                array( 'woocommerce_term_id' => $term_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value )
            );
        } else {
            $wpdb->update( $table_name,
                array( 'woocommerce_term_id' => $term_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ),
                array( 'meta_id' => $meta_id )
            );
        }
    }

    public function raw_woocommerce_price( $price ){

        if (!is_admin()) {
            global $product;
            global $wpdb;
            $wc_product = new WC_Product($product);
            $attributes = $wc_product->get_category_ids();
            $gold = 0;
            foreach ($attributes as $attribute_id) {
                $gold = $wpdb->get_var(
                    "SELECT meta_value FROM {$wpdb->prefix}woocommerce_termmeta WHERE woocommerce_term_id = {$attribute_id} AND meta_key = '_attribute_gold_price'"
                );
                if ($gold)
                    break;
            }

            if ($gold) {
                $options = get_option('cutting_art_gold_price');
                $extra_proc = $options['extra_proc'] ? $options['extra_proc'] : 0;
                $price_proc = $price + $price * $extra_proc / 100;
            } else {
                $price_proc = $price;
            }
        } else {
            $price_proc = $price;
        }

        wp_localize_script('jquery', 'wcac', ['retailPriceProc' => $price_proc, 'ajaxUrl' => admin_url('admin-ajax.php')]);
        wp_enqueue_script('wcac-front-wc-js');
        return $price_proc;
    }

    /**
     * Admin Enqueue Scripts
     */
    public function admin_enqueue_scripts() {

        wp_localize_script('jquery', 'wc_api_custom', ['ajaxUrl' => admin_url('admin-ajax.php')]);

        if (isset($_GET['page']) && $_GET['page'] === 'cutting-art') {
            wp_localize_script('jquery', 'wc_api_custom', ['tab_gold_price' => admin_url('admin.php?page=tab_gold_price')]);
            wp_enqueue_script('wc-api-custom-js-cutting-art',  plugin_dir_url(dirname(__FILE__, 1)).'assets/js/admin-script-cutting-art.js', ['jquery'], $this->version, true);
        }

        if (isset($_GET['page']) && $_GET['page'] === 'tab_gold_price') {
            wp_enqueue_script('wc-api-custom-js-tab-gold-price',   plugin_dir_url(dirname(__FILE__, 1)).'assets/js/admin-script-tab-gold-price.js', ['jquery'], $this->version, true);
        }
    }

    /**
     * admin menu
     */
    public function admin_menu() {
        add_submenu_page('cutting-art','Gold Price', 'Gold Price', 'manage_options', 'tab_gold_price', [$this, 'tab_gold_price']);
    }

    /**
     * Add Tab Gold Price
     */
    public function tab_gold_price() {
        include plugin_dir_path(__DIR__ ) . 'includes/admin/tab-gold-price.php';
    }


}