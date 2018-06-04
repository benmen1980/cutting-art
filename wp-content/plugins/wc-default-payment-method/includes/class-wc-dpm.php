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

        add_action( 'show_user_profile', array($this, 'show_user_profile') );
        add_action( 'edit_user_profile', array($this, 'show_user_profile') );
        add_action( 'personal_options_update', array($this, 'personal_options_update') );
        add_action( 'edit_user_profile_update', array($this, 'personal_options_update') );

        add_filter( 'woocommerce_available_payment_gateways', array($this, 'woocommerce_available_payment_gateways') );

        add_action( 'wp_ajax_set_default_payment_method', array($this, 'set_default_payment_method') );
        add_action( 'wp_ajax_change_payment_method_for_user', array($this, 'change_payment_method_for_user') );
        add_action( 'wp_ajax_change_retail_price_proc_for_user', array($this, 'change_retail_price_proc_for_user') );

        /*add_filter( 'raw_woocommerce_price', array($this, 'raw_woocommerce_price'), 1, 10);*/
    }

    /**
     * @param $price
     * @return float|int
     */
    public function raw_woocommerce_price($price){
        $user_id = get_current_user_id();
        $price_proc = get_user_meta($user_id,'wcdpm_retail_price_proc', true);

        return $price + $price * $price_proc / 100;
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

        if ($_GET['page'] === 'wc-settings') {
            wp_enqueue_script('wcdpm-admin-wc-settings-js', plugins_url($this->js_folder_url . 'wcdpm-admin-wc-settings.js'), ['jquery'], $this->version, true);
            wp_enqueue_style( 'wcdpm-admin-wc-settings-css', plugins_url($this->css_folder_url . 'wcdpm-admin-wc-settings.css'), $this->version);
        }

        if ($pagenow === 'users.php') {
            wp_enqueue_script('wcdpm-admin-users-js', plugins_url($this->js_folder_url . 'wcdpm-admin-users.js'), ['jquery'], $this->version, true);
        }
    }

    /**
     * Add coloumn "Payment Method" to Users Table
     * @param $columns
     * @return array
     */
    public function manage_users_columns( $columns ) {
        $columns['payment-method'] = 'Payment Method';
        /*$columns['retail-price-proc'] = 'Retail Price';*/
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
                $procs = range(0,100,5);
                $options = '';
                $selected = selected($user_retail_price_proc, '0', 0);
                foreach ($procs as $proc) {
                    $selected = selected($user_retail_price_proc, $proc, 0);
                    $options .= "<option value='{$proc}' {$selected}>{$proc}%</option>";
                }
                return "<select user-id='{$user_id}' class='change-retail-price-proc-for-user'>{$options}</select>";
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
        $user_id = $_POST['userId'];
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

}