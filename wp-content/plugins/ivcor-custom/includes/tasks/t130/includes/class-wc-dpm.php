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

class WC_DPM {

    /**
     * WC_DPM version.
     */
    public $version = '0.0.1';

    /**
     * WC_DPM js folder url for "plugins_url".
     */
    public $js_folder_url = 'wc-default-payment-method/assets/js/';

    /**
     * WC_DPM css folder url for "plugins_url".
     */
    public $css_folder_url = 'wc-default-payment-method/assets/css/';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * @return WC_DPM|WooCommerce
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WC_DPM constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        add_filter( 'manage_users_columns', array($this, 'manage_users_columns') );
        add_filter( 'manage_users_custom_column', array($this, 'manage_users_custom_column'), 10, 3 );

        add_filter( 'woocommerce_payment_gateways_setting_columns', array($this, 'wc_payment_gateways_setting_columns') );
        add_action( 'woocommerce_payment_gateways_setting_column_default', array($this, 'wc_payment_gateways_setting_columns_default') );

        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
        add_action( 'wp_enqueue_scripts', array($this, 'wp_enqueue_scripts') );

        add_action( 'show_user_profile', array($this, 'show_user_profile') );
        add_action( 'edit_user_profile', array($this, 'show_user_profile') );
        add_action( 'personal_options_update', array($this, 'personal_options_update') );
        add_action( 'edit_user_profile_update', array($this, 'personal_options_update') );

        add_filter( 'woocommerce_available_payment_gateways', array($this, 'woocommerce_available_payment_gateways') );

        add_action( 'wp_ajax_set_default_payment_method', array($this, 'set_default_payment_method') );
        add_action( 'wp_ajax_change_payment_method_for_user', array($this, 'change_payment_method_for_user') );

        add_action( 'wp_ajax_nopriv_change_retail_price_proc_for_user', array($this, 'change_retail_price_proc_for_user') );
        add_action( 'wp_ajax_change_retail_price_proc_for_user', array($this, 'change_retail_price_proc_for_user') );

        add_action( 'wp_ajax_nopriv_t208_hide_price_for_user_save', array($this, 't208_hide_price_for_user_save'));
        add_action( 'wp_ajax_t208_hide_price_for_user_save', array($this, 't208_hide_price_for_user_save'));

        add_action( 'wp_ajax_t208_table_retail_price_category_for_user_save', array($this, 't208_table_retail_price_category_for_user_save'));
        add_action( 'wp_ajax_nopriv_t208_table_retail_price_category_for_user_save', array($this, 't208_table_retail_price_category_for_user_save'));

        add_action( 'wp_ajax_t208_table_retail_price_category_any_for_user_save', array($this, 't208_table_retail_price_category_any_for_user_save'));
        add_action( 'wp_ajax_nopriv_t208_table_retail_price_category_any_for_user_save', array($this, 't208_table_retail_price_category_any_for_user_save'));

        add_action( 'wp_ajax_t216_update_price_display', array($this, 't216_update_price_display'));
        add_action( 'wp_ajax_nopriv_t216_update_price_display', array($this, 't216_update_price_display'));

        add_filter( 'woocommerce_product_variation_get_price', array($this, 'woocommerce_product_variation_get_price'), 99, 2);
        add_filter( 'woocommerce_variable_price_html', array($this, 'woocommerce_variable_price_html'), 99, 2);

        add_action( 'woocommerce_after_my_account', array($this, 'woocommerce_after_my_account') );

        add_action( 'init', array($this, 'init') );
    }

    /**
     * Init
     */
    public function init() {
        if ( is_user_logged_in() && is_admin() && !current_user_can( 'administrator' ) ) {

            if (isset($_POST['action']) && $_POST['action'] === 'change_retail_price_proc_for_user')
                $this->change_retail_price_proc_for_user();
            if (isset($_POST['action']) && $_POST['action'] === 't208_hide_price_for_user_save')
                $this->t208_hide_price_for_user_save();
            if (isset($_POST['action']) && $_POST['action'] === 't208_table_retail_price_category_for_user_save')
                $this->t208_table_retail_price_category_for_user_save();
            if (isset($_POST['action']) && $_POST['action'] === 't208_table_retail_price_category_any_for_user_save')
                $this->t208_table_retail_price_category_any_for_user_save();
            if (isset($_POST['action']) && $_POST['action'] === 't216_update_price_display')
                $this->t216_update_price_display();
            if (isset($_POST['action']) && $_POST['action'] === 'wmp_variation_price_array')
                if (class_exists('WM_Variation_Price_Hints')) {
                    $price_hints = new WM_Variation_Price_Hints();
                    $price_hints->ajax_wmp_variation_price_array_callback();
                }
            exit;
        }
    }

    /**
     * WooCommerce after my account add Select
     */
    public function woocommerce_after_my_account(){
        $user_id = get_current_user_id();
        if (current_user_can('customer') || current_user_can('administrator') || current_user_can('shop_manager')) {
            $user_retail_price_proc = get_user_meta($user_id, 'wcdpm_retail_price_proc', true);
            $user_retail_price_proc = $user_retail_price_proc ? $user_retail_price_proc : 0;

            echo "<div class='row wc_dpm_woocommerce_after_my_account'>";
            echo "Retail Price: <input class='input-retail-price-proc-for-user' user-id='{$user_id}' min='0' max='500' style='width: 70px' type='number' value='{$user_retail_price_proc}'>";
            echo " <a style='cursor: pointer; padding: 0 10px;' class='woocommerce-button button input-retail-price-proc-for-user-save'>Save</a>";
            echo "</div><br>";
        }
    }

    /**
     * @param $price
     * @param $product_variation
     * @return float
     */
    public function woocommerce_product_variation_get_price($price, $product_variation){

        $price_display = get_user_meta(get_current_user_id(), '_price_display', true);
        if (!is_admin() && $price_display === 'retail') {
            $variation_id = $product_variation->get_id();

            $product_id = wc_get_product_id_by_sku($product_variation->get_parent_data()['sku']);
            $product = new WC_Product($product_id);
            $categories = $product->get_category_ids();
            $user_id = get_current_user_id();
            $price_proc = get_user_meta($user_id, 'wcdpm_retail_price_proc', true);
            $price_proc = $price_proc ? $price_proc : 0;
            $price_out = $price;

            if ($categories && is_array($categories)) {
                $price_proc_by_cat = get_user_meta($user_id, '_retail_price_category', true);

                foreach ($categories as $term_id) {

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

                if ($price_out === $price)
                    $price_out = floatval($price + $price * floatval($price_proc) / 100);
            }

            return $price_out;
        } else {
            return $price;
        }
    }

    public function woocommerce_variable_price_html($price, $product){

        $variations = $product->get_available_variations();
        $prices = [];

        foreach($variations as $variation) {
            $prices[] = $variation['display_price'];
        }

        if ( ! empty($prices)) {
            return wc_price(min($prices)) . ' - ' . wc_price(max($prices));
        }

        return $price;
    }

    /**
     * Unset Payment Method on frontend page "Checkout"
     * @param $available_gateways
     * @return mixed
     */
    public function woocommerce_available_payment_gateways($available_gateways ){
        $payment_method_id_current_user = get_user_meta(get_current_user_id(), 'wcdpm_user_payment_method', true);

        if ($payment_method_id_current_user === 'default')
            $payment_method_id_current_user = get_option('wcdpm_default_payment_method', true);

        $available_gateways_new = [];

        foreach ($available_gateways as $id => $available_gateway) {
            if ($payment_method_id_current_user === $id)
                $available_gateways_new[$id] = $available_gateway;
        }

        if (empty($available_gateways_new))
            $available_gateways_new = $available_gateways;

        return $available_gateways_new;
    }

    /**
     * Save meta "Payment Method"
     * @param $user_id
     * @return bool
     */
    public function personal_options_update( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }
        update_user_meta( $user_id, 'wcdpm_user_payment_method', $_POST['payment-methods'] );
    }

    /**
     * Add meta "Payment Method" to profile
     * @param $user
     */
    public function show_user_profile( $user ) {
        $wc_payment_methods = WC()->payment_gateways->payment_gateways;
        $payment_methods = [];
        foreach ($wc_payment_methods as $wc_payment_method) {
            if ($wc_payment_method->enabled === 'yes')
                $payment_methods[$wc_payment_method->id] = $wc_payment_method->title;
        }
        $user_payment_method = get_user_meta($user->ID,'wcdpm_user_payment_method', true);
        $selected = selected($user_payment_method, 'default', 0);
        $options = "<option value='default' {$selected}>Default</option>";
        foreach ($payment_methods as $id => $title) {
            $selected = selected($user_payment_method, $id, 0);
            $options .= "<option value='{$id}' {$selected}>{$title}</option>";
        }
        $select = "<select id='payment-methods' name='payment-methods' class='change-payment-method-for-user'>{$options}</select>";

        ?>
        <h3>Payment Methods</h3>
        <table class="form-table">
            <tr>
                <th><label for="payment-methods">Payment Methods</label></th>
                <td><?php echo $select;?></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Admin Enqueue Scripts
     */
    public function admin_enqueue_scripts() {
        global $pagenow;

        wp_localize_script('jquery', 'wcdpm', ['ajaxUrl' => admin_url('admin-ajax.php')]);

        if (isset($_GET['page']) && $_GET['page'] === 'wc-settings') {
            wp_enqueue_script('wcdpm-admin-wc-settings-js', plugin_dir_url(dirname(__FILE__, 1)).'assets/js/wcdpm-admin-wc-settings.js', ['jquery'], $this->version, true);
            wp_enqueue_style( 'wcdpm-admin-wc-settings-css', plugin_dir_url(dirname(__FILE__, 1)).'assets/css/wcdpm-admin-wc-settings.css', $this->version);
        }

        if ($pagenow === 'users.php') {
            wp_enqueue_script('wcdpm-admin-users-js', plugin_dir_url(dirname(__FILE__, 1)).'assets/js/wcdpm-admin-users.js', ['jquery'], $this->version, true);
        }
    }

    /**
     * WP Enqueue Scripts
     */
    public function wp_enqueue_scripts() {

        wp_localize_script('jquery', 'wcdpm', ['retailPriceProc' => ((isset($price_proc) && $price_proc) ? $price_proc : 0), 'ajaxUrl' => admin_url('admin-ajax.php')]);

        wp_enqueue_script('wcdpm-front-wc-js', plugin_dir_url(dirname(__FILE__, 1)).'assets/js/wcdpm-front-wc.js', ['jquery'], $this->version, true);
    }

    /**
     * Add coloumn "Payment Method" to Users Table
     * @param $columns
     * @return array
     */
    public function manage_users_columns( $columns ) {
        $columns['payment-method'] = 'Payment Method';
        $columns['retail-price-proc'] = 'Retail Price';
        return $columns;
    }

    /**
     * Add value to coloumn "Payment Method" to Users Table
     * @param $val
     * @param $column_name
     * @param $user_id
     * @return value
     */
    public function manage_users_custom_column($val, $column_name, $user_id ) {
        switch ($column_name) {
            case 'payment-method' :
                $wc_payment_methods = WC()->payment_gateways->payment_gateways;
                $payment_methods = [];
                foreach ($wc_payment_methods as $wc_payment_method) {
                    if ($wc_payment_method->enabled === 'yes')
                        $payment_methods[$wc_payment_method->id] = $wc_payment_method->title;
                }
                $user_payment_method = get_user_meta($user_id,'wcdpm_user_payment_method', true);
                $selected = selected($user_payment_method, 'default', 0);
                $options = "<option value='default' {$selected}>Default</option>";
                foreach ($payment_methods as $id => $title) {
                    $selected = selected($user_payment_method, $id, 0);
                    $options .= "<option value='{$id}' {$selected}>{$title}</option>";
                }
                return "<select user-id='{$user_id}' class='change-payment-method-for-user'>{$options}</select>";
                break;
            case 'retail-price-proc' :
                $user_retail_price_proc = get_user_meta($user_id,'wcdpm_retail_price_proc', true);
                $user_retail_price_proc = $user_retail_price_proc ? $user_retail_price_proc : 0;
                /*$procs = range(0,100,5);
                $options = '';
                $selected = selected($user_retail_price_proc, '0', 0);
                foreach ($procs as $proc) {
                    $selected = selected($user_retail_price_proc, $proc, 0);
                    $options .= "<option value='{$proc}' {$selected}>{$proc}%</option>";
                }
                return "<select user-id='{$user_id}' class='change-retail-price-proc-for-user'>{$options}</select>";*/
                return "<input class='input-retail-price-proc-for-user' user-id='{$user_id}' min='0' max='500' style='width: 70px' type='number' value='{$user_retail_price_proc}'> <a style='cursor: pointer' class='input-retail-price-proc-for-user-save'>Save</a>";
                break;
            default:
        }
        return $val;
    }

    /**
     * Add coloumn "Default" to Gateways
     * @param $columns
     * @return array
     */
    public function wc_payment_gateways_setting_columns( $columns ) {
        $columns['default'] = 'Default';
        return $columns;
    }

    /**
     * Add value to coloumn "Default" to Gateways
     * @param $gateway
     */
    public function wc_payment_gateways_setting_columns_default( $gateway ) {
        $default_payment_method = get_option('wcdpm_default_payment_method', true);
        $class = ($default_payment_method === $gateway->id) ? 'yes-default' : 'no-default';
        echo '<td class="status default ' . $class . '">';
        echo ( 'yes' === $gateway->enabled ) ? '<span gateway-id="' . $gateway->id . '" class="status-enabled tips" data-tip="' . esc_attr__( 'Default', 'woocommerce' ) . '">' . esc_html__( 'Default', 'woocommerce' ) . '</span>' : '-';
        echo '</td>';
    }

    /**
     * Ajax set default payment method
     */
    public function set_default_payment_method() {
        $gateway_id = $_POST['gatewayId'];
        $res = update_option('wcdpm_default_payment_method', $gateway_id);
        wp_die(json_encode($res));
    }

    /**
     * Ajax change payment method for user
     */
    public function change_payment_method_for_user() {
        $payment_method_id = $_POST['paymentMethodId'];
        $user_id = $_POST['userId'];;
        $res = update_user_meta($user_id, 'wcdpm_user_payment_method', $payment_method_id);
        wp_die(json_encode($res));
    }

    /**
     * Ajax change retail price for user
     */
    public function change_retail_price_proc_for_user() {
        $proc = $_POST['proc'];
        $user_id = $_POST['userId'];
        $res = update_user_meta($user_id, 'wcdpm_retail_price_proc', $proc);
        wp_die(json_encode($res));
    }

    /**
     * Ajax hide price for user
     */
    public function t208_hide_price_for_user_save(){
        $hide_price = $_POST['hidePrice'];
        $res = update_user_meta(get_current_user_id(), '_hide_prices', $hide_price);
        wp_die(json_encode($res));
    }

    /**
     * Ajax table retail price category any for user save
     */
    public function t208_table_retail_price_category_any_for_user_save(){
        $retail_price = $_POST['retailPrice'];
        $new_price = $_POST['newPrice'];
        $termId = $_POST['termId'];

        $res = update_user_meta(get_current_user_id(), '_retail_price_addition_by_' . $termId, $retail_price);
        update_user_meta(get_current_user_id(), '_new_retail_price_by_' . $termId, $new_price);
        wp_die(json_encode($retail_price));
    }

    /**
     * Ajax table retail price category for user save
     */
    public function t208_table_retail_price_category_for_user_save(){
        $update = $_POST['update'];
        update_user_meta(get_current_user_id(), '_retail_price_category', $update);
        wp_die(json_encode($update));
    }

    /**
     * Ajax update price display
     */
    public function t216_update_price_display(){
        $param = $_POST['param'];
        $res = update_user_meta(get_current_user_id(), '_price_display', $param);

        if ($param === 'hide')
            update_user_meta(get_current_user_id(), '_hide_prices', 1);
        else
            update_user_meta(get_current_user_id(), '_hide_prices', 0);

        wp_die(json_encode($res));
    }

}