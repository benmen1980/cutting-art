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
     * WC_API_CUSTOM js folder url for "plugins_url".
     */
    public $js_folder_url = 'ivcor-priority-wc-api-ivcor-custom/assets/js/';

    /**
     * WC_API_CUSTOM css folder url for "plugins_url".
     */
    public $css_folder_url = 'ivcor-priority-wc-api-ivcor-custom/assets/css/';

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

        add_action( 'wp_ajax_wc_api_custom_get_admin_url_product', [$this, 'wc_api_custom_get_admin_url_product']);

        add_action( 'admin_menu', [$this, 'admin_menu']);

        add_action( 'woocommerce_after_edit_attribute_fields', [$this, 'woocommerce_after_edit_attribute_fields']);

        add_action( 'woocommerce_attribute_updated', [$this, 'woocommerce_attribute_updated'], 3, 10);
    }

    public function woocommerce_attribute_updated( $attribute_id, $data, $old_slug) {
        global $wpdb;

        $table_name = "{$wpdb->prefix}woocommerce_termmeta";

        $update = $wpdb->update( $table_name,
            array( 'meta_key' => '_attribute_gold_price', 'meta_value' => $_POST['attribute_gold_price'] ? $_POST['attribute_gold_price'] : 0 ),
            array( 'woocommerce_term_id' => $attribute_id )
        );

        if ($update === 0) {
            $wpdb->insert( $table_name,
                array( 'woocommerce_term_id' => $attribute_id, 'meta_key' => '_attribute_gold_price', 'meta_value' => $_POST['attribute_gold_price'] ? $_POST['attribute_gold_price'] : 0 )
            );
        }

    }

    public function woocommerce_after_edit_attribute_fields() {
        global $wpdb;

        $attribute_id = absint( $_GET['edit'] );
        $table_name = "{$wpdb->prefix}woocommerce_termmeta";

        $attribute_gold_price = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE woocommerce_term_id = $attribute_id AND meta_key = '_attribute_gold_price'");

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

    /**
     * Admin Enqueue Scripts
     */
    public function admin_enqueue_scripts() {

        wp_localize_script('jquery', 'wc_api_custom', ['ajaxUrl' => admin_url('admin-ajax.php')]);

        if ($_GET['page'] === 'priority-woocommerce-api') {
            wp_enqueue_script('wc-api-custom-js', plugins_url($this->js_folder_url . 'admin-script.js'), ['jquery'], $this->version, true);
            wp_enqueue_style( 'wc-api-custom-css', plugins_url($this->css_folder_url . 'admin-style.css'), $this->version);
        }

        if ($_GET['page'] === 'cutting-art') {
            wp_localize_script('jquery', 'wc_api_custom', ['tab_gold_price' => admin_url('admin.php?page=tab_gold_price')]);
            wp_enqueue_script('wc-api-custom-js-cutting-art', plugins_url($this->js_folder_url . 'admin-script-cutting-art.js'), ['jquery'], $this->version, true);
        }
    }


    /**
     *
     */
    public function wc_api_custom_get_admin_url_product() {
        $res = [];
        $res['product_id'] = wc_get_product_id_by_sku($_POST['sku']);
        $res['product'] = get_post($res['product_id']);
        if ($res['product']->post_parent !== 0)
            $res['product_id'] = $res['product']->post_parent;
        $res['url'] = get_edit_post_link($res['product_id'], '');
        wp_die(json_encode($res));
    }

    /**
     * admin menu
     */
    public function admin_menu() {
        add_submenu_page(NULL,'Gold Price', 'Gold Price', 'manage_options', 'tab_gold_price', [$this, 'tab_gold_price']);
    }

    /**
     * Add Tab Gold Price
     */
    public function tab_gold_price() {
        include plugin_dir_path(__DIR__ ) . 'includes/admin/tab-gold-price.php';
    }


}