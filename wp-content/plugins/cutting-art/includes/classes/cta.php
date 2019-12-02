<?php
/**
 * @package     Cutting Art Plugin
 * @author      Ante Laca <ante.laca@gmail.com>
 * @copyright   2018 Roi Holdings
 */

namespace CuttingArt;

class CTA extends \PriorityAPI\API
{
    // instance
    private static $instance;

    public static $parameters = [];
    public static $conversion_data = [];

    // initialize
    public static function init()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct()
    {
    }

    public function run()
    {
        return is_admin() ? $this->backend() : $this->frontend();
    }

    // frontend part
    private function frontend()
    {
        // if we are not on login page
        if ($GLOBALS['pagenow'] != 'wp-login.php') {

            // user must be logged in as administrator or customer
            if (current_user_can('administrator') || current_user_can('customer') || current_user_can('shop_owner')) {

                add_action('wp_enqueue_scripts', function(){

                    wp_enqueue_script('jquery');
                    wp_enqueue_script('jquery-ui-dialog');
                    wp_enqueue_style('wp-jquery-ui-dialog');
                    wp_enqueue_script('cta-frontend-js', CTA_ASSET_URL . 'frontend.js', ['jquery','jquery-ui-dialog']);

                });


                // include parameters to product page
                add_action('woocommerce_before_add_to_cart_button', function(){
                    include CTA_ADMIN_DIR . 'product_parameters.php';
                });

                // insert selected data into cart
                add_filter('woocommerce_add_cart_item_data', function($cart_data, $product_id, $variation_id){

                    $parameters = $this->getProductParameters($product_id);

                    foreach ($parameters as $parameter) {

                        // cannot pass id as is because its changed
                        if (isset($_POST['cta_parameters'][$parameter->id])) {
                            $cart_data[$parameter->priority_id] = filter_var($_POST['cta_parameters'][$parameter->id], FILTER_SANITIZE_STRING);
                        }

                    }

                    return $cart_data;

                }, 10, 3);

                // show selected parameters in cart
                add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {

                    $parameters = $this->getProductParameters($cart_item['product_id']);

                    foreach ($parameters as $parameter) {

                        if (isset($cart_item[$parameter->priority_id])) {

                            // check if parameter is using conversion
                            $display = ($parameter->use_conversion) ? $this->getConvertedValue($cart_item[$parameter->priority_id]) : '';

                            $item_data[] = array(
                                'key' => $parameter->name,
                                'value' => $cart_item[$parameter->priority_id],
                                'display' => $display,
                            );

                        }

                    }

                    return $item_data;

                }, 10, 2);


                // add parameter values to order
                add_action( 'woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {

                    $parameters = $this->getProductParameters($values['product_id']);

                    foreach ($parameters as $parameter) {

                        if (isset($values[$parameter->priority_id])) {
                            if ($parameter->use_conversion){
                                $item->add_meta_data($parameter->name, $this->getConvertedValue($values[$parameter->priority_id]));
                            } else {
                                $item->add_meta_data($parameter->name, $values[$parameter->priority_id]);
                            }
                        }

                    }

                }, 10, 4);


                /*
                 * the default conversion type (should be Europe size type)
                 * convert ring size value for order/api
                */
                add_action( 'woocommerce_thankyou', function ($order_get_id){
                $default = $this->getDefaultConversionType();
                if ($default->name !== 'Europe'){
                    $order = wc_get_order($order_get_id);
                    foreach ($order->get_items() as $item_id => $item_obj) {
                        $meta = wc_get_order_item_meta ($item_id, 'Ring Size', 'true' );
                        if ($meta) {
                        $size_id = $GLOBALS['wpdb']->get_var('SELECT id FROM ' . $GLOBALS['wpdb']->prefix . 'cta_sizes WHERE size = '.$meta.'');
                        if($size_id){
                            $europe_size = $GLOBALS['wpdb']->get_var('SELECT size FROM ' . $GLOBALS['wpdb']->prefix . 'cta_conversions WHERE size_id = ' . $size_id . ' AND type_id = 2');
                            wc_update_order_item_meta($item_id, 'Ring Size', $europe_size);
                        }
                        }

                     }
                  }
                  },10, 4);


            } else {

                // unregistered user
                $this->redirectToLoginPage();

            }

        }

    }

    // backend part

    public  function checkbox_login()
    {
        add_settings_section("section", null, null, "redirect");
        add_settings_field("redirect-checkbox", "Disable login redirect", array($this, 'checkbox_login_display'), "redirect", "section");
        register_setting("section", "redirect-checkbox");
    }
   public function checkbox_login_display()
    {?>
        <input type="checkbox" name="redirect-checkbox" value="1" <?php checked(1, get_option('redirect-checkbox'), true); ?> />
        <?php
    }

    private function backend()
    {

        add_action('admin_init', function() {

            $this->checkbox_login();

            // check if current user is an admin
            if ( ! current_user_can('administrator')&& ! current_user_can('shop_owner')) {
               $this->redirectToLoginPage();
            }

            // enqueue admin styles and scripts
            wp_enqueue_style('cta-admin-css', CTA_ASSET_URL . 'style.css');
            wp_enqueue_script('cta-admin-js', CTA_ASSET_URL . 'admin.js', ['jquery']);
            wp_localize_script('cta-admin-js', 'CTA', [
                'delete' => __('Delete', 'cta'),
                'remove' => __('Remove', 'cta'),
                'save' => __('Save', 'cta'),
                'add' => __('Add', 'cta'),
                'removeParameter' => __('Are you sure you want to remove parameter', 'cta'),
                'removeValue' => __('Are you sure you want to remove value', 'cta'),
                'assetUrl' => CTA_ASSET_URL,
            ]);


            // add parameters tab to product page
            add_filter('woocommerce_product_data_tabs', function($tabs) {

                $tabs['cta-parameters'] = [
                    'label' => __('Parameters', 'cta'),
                    'target' => 'cta_parameters',
                    //'class' => ['show_if_simple']
                ];

                return $tabs;

            });

            // add parameters panel to product page
            add_action('woocommerce_product_data_panels', function() {

                include CTA_ADMIN_DIR . 'panel.php';

            });


        });

        // admin page
        add_action('admin_menu', function(){

            include CTA_CLASSES_DIR . 'listtable.php';

            add_menu_page(CTA_PLUGIN_NAME, CTA_PLUGIN_NAME, 'manage_options', CTA_PLUGIN_ADMIN_URL, function() {

                // admin pages
                switch($this->get('tab')) {

                    case 'add_parameter':

                        include CTA_ADMIN_DIR . 'add_parameter.php';

                        break;

                    case 'edit_parameter':

                        $data = $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters WHERE id = ' . intval($this->get('parameter')));

                        if (empty($data)) {
                            wp_redirect('admin.php?page=' . CTA_PLUGIN_ADMIN_URL);
                            exit;
                        }

                        include CTA_ADMIN_DIR . 'edit_parameter.php';

                        break;

                    case 'default_values':

                        $parameter = $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters WHERE id = ' . intval($this->get('parameter')));

                        if (empty($parameter) || $parameter->type != 'dropdown') {
                            wp_redirect('admin.php?page=' . CTA_PLUGIN_ADMIN_URL);
                            exit;
                        }

                        include CTA_ADMIN_DIR . 'defaults_values.php';

                        break;


                    case 'conversion-types':

                        include CTA_ADMIN_DIR . 'conversion_types.php';

                        break;

                    case 'add-type':

                        include CTA_ADMIN_DIR . 'add_type.php';

                        break;

                    case 'edit-type':

                        $data = $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_types WHERE id = ' . intval($this->get('type')));

                        if (empty($data)) {
                            wp_redirect('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-types');
                            exit;
                        }

                        include CTA_ADMIN_DIR . 'edit_type.php';

                        break;

                    case 'conversion-table':

                        include CTA_ADMIN_DIR . 'conversion_table.php';

                        break;

                    case 'add-conversion':

                        include CTA_ADMIN_DIR . 'add_conversion.php';

                        break;

                    case 'edit-conversion':

                        $data = $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_sizes WHERE id = ' . intval($this->get('id')));

                        if (empty($data)) {
                            wp_redirect('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-table');
                            exit;
                        }

                        include CTA_ADMIN_DIR . 'edit_conversion.php';

                        break;

                    default:

                        include CTA_ADMIN_DIR . 'parameters.php';
                }

            });

        });

        /**
         * AJAX
         */

        // ajax add parameter
        add_action('wp_ajax_cta_add_param', function() {

            $parameter = $this->getParameter($this->post('param'));

            $meta_value = ($parameter->type == 'dropdown') ? __('Irrelevant', 'cta') : null;

            $status = $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_parameters_meta', [
                'param_id' => $this->post('param'),
                'product_id' => $this->post('post'),
                'meta_value' => $meta_value
            ]);

            // user inserted
            delete_post_meta($this->post('post'), 'cta_param_removed_' . $this->post('param'));


            include CTA_ADMIN_DIR . 'ajax_panel.php';

            exit;

        });


        // ajax save parameter value
        add_action('wp_ajax_cta_save_value', function() {

            $status = $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_parameters_meta', [
                'param_id' => $this->post('param'),
                'product_id' => $this->post('post'),
                'meta_value' => $this->post('meta')
            ]);

            // send response
            wp_send_json([
                'status' => $status,
                'id' => $GLOBALS['wpdb']->insert_id,
                'error' => $GLOBALS['wpdb']->last_error
            ]);

        });


        // ajax remove parameter value
        add_action('wp_ajax_cta_remove_value', function() {

            $status = $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters_meta WHERE id = ' . intval($this->post('id')));

            wp_send_json(['status' => $status, 'error' => $GLOBALS['wpdb']->last_error]);

        });

        // ajax remove parameter group
        add_action('wp_ajax_cta_remove_param', function() {

            $id = intval($this->post('id'));
            $post_id = intval($this->post('post_id'));

            $status = $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters_meta WHERE param_id = ' . $id);

            // set it as removed
            add_post_meta($post_id, 'cta_param_removed_' . $id, true);

            wp_send_json(['status' => ($status === false) ? 0 : 1, 'error' => $GLOBALS['wpdb']->last_error]);

        });


        /**
         * CRUD
         */

        // insert parameter
        if ($this->post('insert_param') && wp_verify_nonce($this->post('cta'), 'insert_param')) {

            $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_parameters', [
                'priority_id' => $this->post('param_priority'),
                'name' => $this->post('param_name'),
                'type' => $this->post('param_type'),
                'use_defaults' => intval($this->post('param_defaults')),
                'use_foreach' => $this->post('param_foreach') ? 1 : 0,
                'use_conversion' => $this->post('param_conversion') ? 1 : 0
            ]);

            $this->notify(__('Parameter inserted', 'cta'));

        }

        // edit parameter
        if ($this->post('edit_param') && wp_verify_nonce($this->post('cta'), 'edit_param')) {

            $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . 'cta_parameters', [
                'priority_id' => $this->post('param_priority'),
                'name' => $this->post('param_name'),
                'type' => $this->post('param_type'),
                'use_defaults' => intval($this->post('param_defaults')),
                'use_foreach' => $this->post('param_foreach') ? 1 : 0,
                'use_conversion' => $this->post('param_conversion') ? 1 : 0
            ], [
                'id' => intval($this->post('param_id'))
            ]);

            $this->notify(__('Parameter edited', 'cta'));

        }

        // delete parameter
        if ($this->get('delete') && wp_verify_nonce($this->get('cta'), 'delete_param')) {

            $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters WHERE id = ' . intval($this->get('delete')));
            $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_defaults_meta WHERE param_id = ' . intval($this->get('delete')));

            $this->notify(__('Parameter deleted', 'cta'));

        }


        // insert defaults value
        if ($this->post('insert_defaults_value') && wp_verify_nonce($this->post('cta'), 'insert_defaults_value')) {

            $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_defaults_meta', [
                'param_id' => $this->post('param_id'),
                'meta_value' => $this->post('param_value')
            ]);

            $this->notify(__('Value added', 'cta'));

        }


        // delete defaults
        if ($this->get('delete') && wp_verify_nonce($this->get('cta'), 'delete_default_value')) {

            $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_defaults_meta WHERE id = ' . intval($this->get('delete')));

            $this->notify(__('Value deleted', 'cta'));

        }

        // add conversion type
        if ($this->post('insert_conversion_type') && wp_verify_nonce($this->post('cta'), 'insert_conversion_type')) {

            $default = $this->post('default_conversion_type') ? 1 : 0;

            // only one is default
            if($default) {

                $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . 'cta_types', [
                    'default_type' => 0,
                ], [
                    'default_type' => 1
                ]);
            }

            $status = $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_types', [
                'name' => $this->post('conversion_type_name'),
                'code' => $this->post('conversion_type_code'),
                'default_type' => $default
            ]);

            $message = ($status) ? __('Conversion type added', 'cta') : __('Something went wrong, Conversion type not added', 'cta');

            $this->notify($message);

        }

        // edit conversion type
        if ($this->post('edit_conversion_type') && wp_verify_nonce($this->post('cta'), 'edit_conversion_type')) {


            $default = $this->post('default_conversion_type') ? 1 : 0;

            // only one is default
            if($default) {

                $status = $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . 'cta_types', [
                    'default_type' => 0,
                ], [
                    'default_type' => 1
                ]);
            }


            $status = $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . 'cta_types', [
                'name' => $this->post('conversion_type_name'),
                'code' => $this->post('conversion_type_code'),
                'default_type' => $default
            ], [
                'id' => intval($this->post('conversion_type_id'))
            ]);


            $message = ($status) ? __('Conversion type edited', 'cta') :  __('Something went wrong, conversion type not edited', 'cta');


            $this->notify($message, $status ? 'success' : 'error');

        }

        // delete conversion type
        if ($this->get('delete') && wp_verify_nonce($this->get('cta'), 'delete_conversion_type')) {

            $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_types WHERE id = ' . intval($this->get('delete')));

            $this->notify(__('Conversion type deleted', 'cta'));

        }


        // add conversion
        if ($this->post('insert_conversion') && wp_verify_nonce($this->post('cta'), 'insert_conversion')) {

            $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_sizes', [
                'size'               => $_POST['size'],
                'circumference_mm'   => $_POST['circumference_mm'],
                'circumference_inch' => $_POST['circumference_inch'],
                'diameter'           => $_POST['diameter']
            ]);

            $size_id = $GLOBALS['wpdb']->insert_id;

            foreach($_POST['conversion_size'] as $type_id => $value) {

                $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_conversions', [
                    'size_id' => $size_id,
                    'type_id' => $type_id,
                    'size'    => $value
                ]);

            }

            $message = empty($GLOBALS['wpdb']->last_error) ?  __('Conversion added', 'cta')   : __('Something went wrong, Conversion  not added', 'cta');

            $this->notify($message);

        }

        // edit conversion
        if ($this->post('edit_conversion') && wp_verify_nonce($this->post('cta'), 'edit_conversion')) {


            $default = $this->getDefaultConversionType();

            $size_id = intval($_POST['conversion_id']);

            $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . 'cta_sizes', [
                'size'               => $_POST['size'],
                'circumference_mm'   => $_POST['circumference_mm'],
                'circumference_inch' => $_POST['circumference_inch'],
                'diameter'           => $_POST['diameter']
            ],[
                'id' => $size_id
            ]);

            foreach($_POST['conversion_size'] as $type_id => $value) {

                $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . 'cta_conversions', [
                    'size_id' => $size_id,
                    'type_id' => $type_id,
                    'size'    => $value
                ],[
                    'size_id' => $size_id,
                    'type_id' => $type_id,
                ]);

            }

            $message = empty($GLOBALS['wpdb']->last_error) ? __('Conversion edited', 'cta') :  __('Something went wrong, conversion not edited', 'cta');


            $this->notify($message);

        }

        // delete conversion
        if ($this->get('delete') && wp_verify_nonce($this->get('cta'), 'delete_conversion')) {

            $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_sizes WHERE id = ' . intval($this->get('delete')));
            $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'cta_conversions WHERE size_id = ' . intval($this->get('delete')));

            $this->notify(__('Conversion deleted', 'cta'));

        }

    }

    /**
     * Redirect to login page
     *
     * @return void
     */
    public function redirectToLoginPage()
    {
        if (get_option('redirect-checkbox')!=='1') {
            wp_logout(); // logout current user
            wp_redirect(wp_login_url());
            exit;
        }
    }


    /**
     * Get all parameters
     *
     * @return array
     */
    public static function getParameters()
    {
        #return $GLOBALS['wpdb']->get_results('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters WHERE use_conversion = 0');
        return $GLOBALS['wpdb']->get_results('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters');
    }


    /**
     * Get  parameter
     *
     * @return array
     */
    public function getParameter($id)
    {
        return $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters WHERE id = ' . intval($id));
    }


    /**
     * Get parameter by name
     *
     * @return array
     */
    public static function getParameterByName($name)
    {
        return $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters WHERE name = "' . esc_sql($name) , '"');
    }


    /**
     * Get parameters meta for poroduct
     *
     * @param [int] $product_id
     * @param [int] $param_id
     * @return array
     */
    public function getParametersMeta($product_id, $param_id)
    {

        return $GLOBALS['wpdb']->get_results('
            SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_parameters_meta 
            WHERE product_id = ' . intval($product_id) . ' 
            AND param_id = ' . intval($param_id)
        );

    }



    public function getProductParameters($id)
    {

        if (isset(static::$parameters[$id])) {
            return static::$parameters[$id];
        }

        $parameters = static::getParameters();

        foreach($parameters as $i => $parameter) {

            if ($parameter->use_foreach) {

                // check if parameter is removed
                if (get_post_meta($id, 'cta_param_removed_' . $parameter->id, true)) {
                    unset($parameters[$i]);
                    continue;
                }

                if($parameter->type == 'dropdown') {

                    $meta = $this->getParametersMeta($id, $parameter->id);

                    // check parameter data
                    if (empty($meta)) {

                        $default = $this->getDefaultsMeta($parameter->id);

                        // remove parameter because its empty
                        if(empty($default)) {
                            unset($parameters[$i]);
                            continue;
                        }

                        // insert default data
                        foreach($default as $data) {

                            $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'cta_parameters_meta', [
                                'param_id'   => $parameter->id,
                                'product_id' => $id,
                                'meta_value' => $data->meta_value
                            ]);

                        }

                    }

                }

            } else {

                $meta = $this->getParametersMeta($id, $parameter->id);

                // check parameter data
                if (empty($meta)) {
                    unset($parameters[$i]);
                    continue;
                }

            }

        }


        static::$parameters[$id] = $parameters;


        return static::$parameters[$id];

    }


    /**
     * Get default data for parameter
     *
     * @param [int] $param_id
     * @return array
     */
    public function getDefaultsMeta($param_id)
    {
        return $GLOBALS['wpdb']->get_results('SELECT id, meta_value FROM ' . $GLOBALS['wpdb']->prefix . 'cta_defaults_meta WHERE param_id = ' . intval($param_id));
    }


    /**
     * Get conversion types
     *
     * @return void
     */
    public function getConversionTypes()
    {
        return $GLOBALS['wpdb']->get_results('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_types ORDER BY default_type DESC');
    }


    /**
     * Get defaul conversion type
     *
     * @return void
     */
    public function getDefaultConversionType()
    {
        $user_id = get_current_user_id();
        $user_default_ring_size_type = get_user_meta($user_id,'default_ring_size_type', true);
        if ($user_default_ring_size_type){
            return $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_types WHERE id = '.$user_default_ring_size_type.'');
        } else {
            return $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_types WHERE default_type = 1');
        }
    }


    /**
     * Get conversion data by size id and type id
     *
     * @return void
     */
    public function getConversionData($size_id)
    {
        // cache results
        if(!isset(static::$conversion_data[$size_id])) {
            static::$conversion_data[$size_id] = $GLOBALS['wpdb']->get_row('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_conversions WHERE size_id = ' . intval($size_id));
        }

        return static::$conversion_data[$size_id];

    }

    public function getConversions()
    {
        return $GLOBALS['wpdb']->get_results('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_conversions', ARRAY_A);

    }


    public function getConvertedValue($key)
    {
        $default = $this->getDefaultConversionType();

        $query = '
            SELECT conversions.size 
            FROM ' . $GLOBALS['wpdb']->prefix . 'cta_sizes as sizes
            LEFT JOIN ' . $GLOBALS['wpdb']->prefix . 'cta_conversions as conversions
            ON sizes.id = conversions.size_id
            WHERE sizes.size = ' . esc_attr($key). ' 
            AND conversions.type_id = ' . $default->id . '
        ';

        $data = $GLOBALS['wpdb']->get_row($query);

        return ($data) ? $data->size : null;

    }



    /**
     * Get default sizes
     *
     * @return void
     */
    public function getDefaultSizes()
    {
        return $GLOBALS['wpdb']->get_results('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'cta_sizes');

    }



}
