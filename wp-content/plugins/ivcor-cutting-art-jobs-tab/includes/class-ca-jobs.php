<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 15.06.2018
 * Time: 14:10
 */

class CA_JOBS {

    /**
     * @var string version
     */
    public $version = '0.0.1';

    /**
     * @var string plugin name
     */
    public $plugin_name = 'ivcor-cutting-art-jobs-tab';

    /**
     * CA_JOBS constructor.
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Init Hook
     */
    private function init_hooks()
    {
        add_action('init', [$this, 'wp_init']);

        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'wp_enqueue_scripts']);

        add_filter('bulk_actions-edit-shop_order', [$this, 'generate_job_register_bulk_action']);
        add_action('admin_action_generate_job', [$this, 'admin_action_generate_job']);
        add_action('admin_notices', [$this, 'admin_notices']);

        add_action('save_post', [$this, 'save_post']);
        add_action('new_to_publish', [$this, 'save_post']);

        add_action('all_admin_notices', [$this, 'admin_header'], 99);
        add_action('admin_footer', [$this, 'admin_footer'], 99);

        add_filter('manage_job_posts_columns', [$this, 'manage_job_posts_columns']);
        add_action('manage_job_posts_custom_column' , [$this, 'manage_job_posts_custom_column'], 10, 2);

        add_filter('woocommerce_account_menu_items', [$this, 'woocommerce_account_menu_items'], 10, 1);
        add_action('woocommerce_account_jobs_endpoint', [$this, 'woocommerce_account_jobs_endpoint']);

        add_action('job_status_add_form_fields', [$this, 'job_status_add_form_fields'], 10, 2);
        add_action('job_status_edit_form_fields', [$this, 'job_status_edit_form_fields'], 10, 2);

        add_action('edited_job_status', [$this, 'job_status_save_meta'], 10, 2);
        add_action('create_job_status', [$this, 'job_status_save_meta'], 10, 2);

        add_filter('manage_edit-job_status_columns', [$this, 'manage_edit_job_status_columns']);
        add_filter('manage_job_status_custom_column', [$this, 'manage_job_status_custom_column'], 10, 3);

        add_filter('woocommerce_order_get_items', [$this, 'woocommerce_order_get_items'], 10, 1);

        add_action('wp_ajax_update_job_status_default', [$this, 'update_job_status_default']);
    }

    /**
     *
     */
    public function update_job_status_default() {
        $res = update_option('_update_job_status_default', $_POST['term_id']);
        wp_die(json_encode($res));
    }

    /**
     * @param $items
     * @return array
     */
    public function woocommerce_order_get_items($items) {
        if (isset($_GET['product_line'])) {
            $product_line = intval($_GET['product_line']);

            $filter_item = array_slice($items, ($product_line - 1), 1 );
            return $filter_item;
        }
        return $items;
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function manage_edit_job_status_columns($columns){
        $columns['external_description'] = 'External Description';
        $columns['job_status_default'] = 'Default';
        unset($columns['slug']);
        unset($columns['posts']);

        return $columns;
    }

    /**
     * @param $content
     * @param $column_name
     * @param $term_id
     * @return mixed
     */
    public function manage_job_status_custom_column($content, $column_name, $term_id){
        switch ($column_name) {
            case 'external_description':
                $content = get_term_meta($term_id, '_job_external_description', true );
                break;
            case 'job_status_default':
                $term_id_db = get_option('_update_job_status_default');
                echo '<input name="job_status_default" type="radio" term_id="' . $term_id . '" ' . checked($term_id_db,$term_id,0) . '>';
                break;
            default:
                break;
        }
        return $content;
    }

    /**
     * @param $term_id
     */
    public function job_status_save_meta($term_id) {
        if ( isset( $_POST['job_external_description'] ) ) {
            update_term_meta($term_id, '_job_external_description', $_POST['job_external_description'] );
        }
    }

    /**
     *
     */
    public function job_status_add_form_fields() {
        ?>
        <div class="form-field">
            <label for="job_external_description">External Description</label>
            <input type="text" name="job_external_description" id="job_external_description" value="">
            <p class="description">External Status Description</p>
        </div>
        <?php
    }

    /**
     * @param $term
     */
    public function job_status_edit_form_fields($term) {
        $term_id = $term->term_id;
        $job_external_desc = get_term_meta($term_id, '_job_external_description', true );?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="job_external_status">External Status</label></th>
            <td>
                <input type="text" name="job_external_description" id="job_external_description" value="<?=$job_external_desc?>">
                <p class="description">External Status Description</p>
            </td>
        </tr>
        <?php
    }

    /**
     *
     */
    public function woocommerce_account_jobs_endpoint() {
        echo '<h1>Jobs</h1>';
        $query = new WP_Query([
            'post_type' => 'job',
            'meta_key' => '_customer_id',
            'meta_value' => get_current_user_id()
        ]);
        $jobs = $query->posts;

        ?>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
            <tr>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-job-title"><span class="nobr">Title</span></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-job-number"><span class="nobr">Number</span></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-job-status"><span class="nobr">Status</span></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-job-action"><span class="nobr">Action</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($jobs as $job) { ?>
            <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-on-hold order">
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-job-title" data-title="Order">
                    <?=$job->post_title?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-job-number" data-title="Order">
                    <?=get_post_meta($job->ID, '_job_number', true)?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-job-status" data-title="Order">
                    <?php
                        $term = wp_get_object_terms( $job->ID, 'job_status');

                        if (!$term || $term[0]->slug === 'default') {
                            $term_id = get_option('_update_job_status_default');
                        } else {
                            $term_id = $term[0]->term_id;
                        }
                        if ($term_id) {
                            $job_external_desc = get_term_meta($term_id, '_job_external_description', true );
                            echo $job_external_desc ? $job_external_desc : 'None';
                        }else{
                            echo 'None';
                        }
                    ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-job-actions" data-title="Actions">
                    <?php
                        $order_line = get_post_meta($job->ID, '_order_line', true);
                        if ($order_line) {
                            $order_id = explode('-', $order_line)[0];
                            $product_line = explode('-', $order_line)[1];
                        }
                    ?>
                    <a href="<?=site_url("my-account/view-order/$order_id/?product_line=$product_line")?>" class="woocommerce-button button view">View</a>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * @param $items
     * @return mixed
     */
    public function woocommerce_account_menu_items($items) {
        $items['jobs'] = __( 'Jobs', 'iconic' );
        return $items;
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function manage_job_posts_columns($columns) {
        unset( $columns['date'] );
        $columns['number'] = 'Number';
        $columns['status'] = 'Status';
        $columns['sku'] = 'SKU';
        $columns['price'] = 'Price';
        $columns['order_status'] = 'Order Status';
        $columns['customer'] = 'Customer';
        $columns['description'] = 'Description';

        return $columns;
    }


    /**
     * @param $column
     * @param $post_id
     */
    public function manage_job_posts_custom_column($column, $post_id ) {
        switch ( $column ) {

            case 'number' :
                echo get_post_meta( $post_id , '_job_number' , true );
                break;
            case 'status' :
                $term = wp_get_object_terms( $post_id, 'job_status');
                if (!$term || $term[0]->slug === 'default') {
                    $term_id = get_option('_update_job_status_default');
                    $term = get_term($term_id,'job_status');
                    $job_status = $term->name . " (Default)";
                }else{
                    $term = $term[0];
                    $job_status = $term->name;
                }
                echo $job_status ? $job_status : 'None';
                break;
            case 'sku' :
                $product_id = get_post_meta($post_id, '_product_id', true);
                $sku = get_post_meta($post_id, '_product_sku', true);
                $product_link = ($product_id) ? admin_url("post.php?post=$product_id&action=edit") : '';
                echo ($sku && $product_link) ? "<a href='$product_link'>$sku</a>" : 'None';
                break;
            case 'price' :
                $job_price = get_post_meta($post_id, '_order_price', true);
                echo $job_price ? $job_price : 'None';
                break;
            case 'order_status' :
                try {
                    $order_id = get_post_meta($post_id, '_order_id', true);
                    if ($order_id)
                        $order = new WC_Order($order_id);
                    echo (isset($order)) ? wc_get_order_status_name($order->get_status()) : 'None';
                } catch (Exception $e) {
                    echo 'Error';
                }
                break;
            case 'customer' :
                try {
                    $order_id = get_post_meta($post_id, '_order_id', true);
                    if ($order_id) {
                        $order = new WC_Order( $order_id );
                        $customer_id = $order->get_customer_id();
                        $customer_first_name = get_user_meta($customer_id, 'billing_first_name', true);
                        $customer_last_name = get_user_meta($customer_id, 'billing_last_name', true);
                        echo "<b>ID:</b> $customer_id<br>";
                        echo "<b>Name:</b> $customer_first_name $customer_last_name";
                    } else {
                        echo 'None';
                    }
                } catch (Exception $e) {
                    echo 'Error';
                }
                break;
            case 'description' :
                echo get_post($post_id)->post_content;
                break;
        }
    }

    /**
     * close html tag for TAB
     */
    public function admin_footer() {
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'job') {
            ?>
            </div>
            </div>
            <?php
        }
    }

    /**
     * open html tag for TAB
     */
    public function admin_header() {
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'job') {
            ?>
            <div class="wrap">
            <h1>
                <?php echo CTA_PLUGIN_NAME; ?>
                <span id="p18a_version"><?php echo CTA_VERSION; ?></span>
            </h1>
            <br/>
            <div id="p18a_tabs_menu">
                <ul>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL); ?>">
                            <?php _e('Parameters', 'cta'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-types'); ?>">
                            <?php _e('Conversion types', 'cta'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-table'); ?>">
                            <?php _e('Conversion table', 'cta'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('edit.php?post_type=job'); ?>" class="active">
                            <?php _e('Jobs', 'cta'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=tab_gold_price'); ?>">
                            <?php _e('Gold Price', 'cta'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="p18a-page-wrapper">
                <style>
                    .wp-admin select {height: 28px !important;}
                    .p18a-page-wrapper input[type="text"],
                    .p18a-page-wrapper textarea {width: 95% !important;}
                    .p18a-page-wrapper #current-page-selector {padding: 0 !important; line-height: 1 !important; height: 32px !important; width: 45px !important}
                </style>
                    <br>
                    <a href="<?=admin_url('edit.php?post_type=job')?>" class="">Jobs</a> |
                    <a href="<?=admin_url('edit-tags.php?taxonomy=job_status&post_type=job')?>" class="">Job Statuses</a>
            <?php
        }
    }

    /**
     *
     */
    function admin_notices() {

        global $pagenow, $typenow;

        if( $typenow == 'shop_order'
            && $pagenow == 'edit.php'
            && isset( $_REQUEST['generate_job'] )
            && $_REQUEST['generate_job'] == 1
            && isset( $_REQUEST['changed'] ) ) {

            $message = 'Generate Jobs Success!';
            echo "<div class=\"updated\"><p>{$message}</p></div>";

        }

    }

    /**
     * @param $bulk_actions
     * @return mixed
     */
    public function generate_job_register_bulk_action($bulk_actions ) {

        $bulk_actions['generate_job'] = 'Generate Job';
        return $bulk_actions;

    }

    public function check_default_job_status()
    {
        if (!term_exists('default', 'job_status'))
            wp_insert_term('Default', 'job_status', ['slug' => 'default']);
    }

    /**
     * @param $jobs
     */
    private function generate_jobs($jobs)
    {
        if (empty($jobs)) return;

        $this->check_default_job_status();

        foreach ($jobs as $job)
        {
            $id = get_page_by_title($job['title'] . ' ' . $job['product_sku'], ARRAY_A,'job');

            if ($id) {
                $args = array(
                    'ID' => $id['ID'],
                    'post_title'    => wp_strip_all_tags( $job['title'] ) . ' ' . $job['product_sku'],
                    'post_status'   => 'publish',
                    'post_type' => 'job'
                );
                $id = wp_update_post($args);
            } else {
                $args = array(
                    'post_title'    => wp_strip_all_tags( $job['title'] ) . ' ' . $job['product_sku'],
                    'post_status'   => 'publish',
                    'post_type' => 'job'
                );
                $id = wp_insert_post($args);
            }
            wp_set_object_terms($id, 'default', 'job_status');
            update_post_meta($id, '_job_number',  $job['job_number']);
            update_post_meta($id, '_order_line',  $job['order_line']);
            update_post_meta($id, '_customer_id', $job['customer_id']);
            update_post_meta($id, '_product_sku', $job['product_sku']);
            update_post_meta($id, '_product_id',  $job['product_id']);
            update_post_meta($id, '_order_price', $job['order_price']);
            update_post_meta($id, '_order_id',    $job['order_id']);
        }
    }

    /**
     *
     */
    public function admin_action_generate_job() {

        // if an array with order IDs is not presented, exit the function
        if( !isset( $_REQUEST['post'] ) && !is_array( $_REQUEST['post'] ) )
            return;

        $jobs = [];

        foreach( $_REQUEST['post'] as $order_id ) {
            $order = new WC_Order( $order_id );

            $order_line = 0;
            foreach ($order->get_items() as $item){
                $order_line++;
                $add_price = 0;
                if ($tmcartepo_data = $item->get_meta('_tmcartepo_data')) {
                    foreach ($tmcartepo_data as $tmcartepo) {
                        $add_price += $tmcartepo['price'];
                    }
                } else {
                    $add_price = 0;
                }
                $quantity = $item->get_quantity();
                foreach (range(1,$quantity) as $running_number){
                    $job_number = $order_id.'-'.$order_line.'-'.$running_number;
                    //$title = $job_number . ' ' . $item->get_name();
                    $product = $item->get_product();
                    $jobs[$job_number] = [
                        'job_number' => $job_number,
                        'title' => $job_number . ' ' . $item->get_name(),
                        'order_line' => $order_id.'-'.$order_line,
                        'customer_id' => $order->get_customer_id(),
                        'order_id' => $order_id,
                        'order_price' => ($product) ? floatval($product->get_price()) + floatval($add_price) : 0,
                        'product_id' => $item->get_product_id(),
                        'product_sku' => ($product) ? $product->get_sku() : 0
                    ];
                }
            }
        }

        $this->generate_jobs($jobs);

        // of course using add_query_arg() is not required, you can build your URL inline
        $location = add_query_arg( array(
            'post_type' => 'shop_order',
            'generate_job' => 1,
            'changed' => count( $_REQUEST['post'] ),
            'ids' => join( $_REQUEST['post'], ',' ),
            'post_status' => 'all'
        ), 'edit.php' );

        wp_redirect( admin_url( $location ) );
        exit;

    }

    /**
     * WordPress Init
     */
    public function wp_init()
    {
        $this->register_post_type_job();
        $this->register_taxonomy_job_status();

        add_rewrite_endpoint( 'jobs', EP_PAGES );
    }

    /**
     * Register Custom Taxonomy Job Status
     */
    public function register_taxonomy_job_status()
    {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Job Statuses',
                'singular_name' => 'Job Status',
            ],
            'show_ui' => true,
            'query_var' => true,
            'meta_box_cb' => false,
            'rewrite' => ['slug' => 'job_status'],
        ];

        register_taxonomy('job_status', ['job'], $args);
    }

    /**
     * Register Custom Post Type Job
     */
    public function register_post_type_job()
    {
        $args = [
            'labels' => [
                'name'          => 'Jobs',
                'singular_name' => 'Job'
            ],
            'support' => [
                'title',
                'editor'
            ],
            'public' => true,
            'show_in_menu' => 'cutting-art',
            'register_meta_box_cb' => [$this, 'add_meta_box_for_job'],
        ];
        register_post_type( 'job', $args );
    }

    /**
     *
     */
    public function add_meta_box_for_job()
    {
        add_meta_box(
            'job_number',
            'Job Number',
            [$this, 'job_number_box'],
            'job',
            'side'
        );

        add_meta_box(
            'job_status',
            'Status',
            [$this, 'job_status_box'],
            'job',
            'side'
        );

        add_meta_box(
            'job_order',
            'Order Line',
            [$this, 'job_order_box'],
            'job',
            'side'
        );

        add_meta_box(
            'job_sku',
            'SKU',
            [$this, 'job_sku_box'],
            'job',
            'side'
        );

        add_meta_box(
            'job_price',
            'Price',
            [$this, 'job_price_box'],
            'job',
            'side'
        );

        add_meta_box(
            'job_order_status',
            'Order Status',
            [$this, 'job_order_status_box'],
            'job',
            'side'
        );

        add_meta_box(
            'job_customer',
            'Customer',
            [$this, 'job_customer_box'],
            'job',
            'side'
        );
    }

    /**
     *
     */
    public function job_customer_box()
    {
        global $post;
        $order_id = get_post_meta($post->ID, '_order_id', true);
        if ($order_id) {
            $order = new WC_Order( $order_id );
            $customer_id = $order->get_customer_id();
            $customer_first_name = get_user_meta($customer_id, 'billing_first_name', true);
            $customer_last_name = get_user_meta($customer_id, 'billing_last_name', true);
            echo "<b>ID:</b> $customer_id<br>";
            echo "<b>Name:</b> $customer_first_name $customer_last_name";
        } else {
            echo 'None';
        }
    }

    /**
     *
     */
    public function job_order_status_box()
    {
        global $post;
        $order_id = get_post_meta($post->ID, '_order_id', true);
        if ($order_id)
            $order = new WC_Order( $order_id );
        echo (isset($order)) ? wc_get_order_status_name($order->get_status()) : 'None';
    }

    /**
     *
     */
    public function job_price_box()
    {
        global $post;
        $job_price = get_post_meta($post->ID, '_order_price', true);
        echo $job_price ? $job_price : 'None';
    }

    /**
     *
     */
    public function job_sku_box()
    {
        global $post;
        $product_id = get_post_meta($post->ID, '_product_id', true);
        $sku = get_post_meta($post->ID, '_product_sku', true);
        $product_link = ($product_id) ? admin_url("post.php?post=$product_id&action=edit") : '';
        echo ($sku && $product_link) ? "<a href='$product_link'>$sku</a>" : 'None';
    }

    /**
     *
     */
    public function job_number_box()
    {
        global $post;
        $job_number = get_post_meta($post->ID, '_job_number', true);
        echo $job_number ? $job_number : 'None';
    }

    /**
     *
     */
    public function job_status_box()
    {
        global $post;

        $term = wp_get_object_terms( $post->ID, 'job_status');
        if ($term) $job_status = $term[0]->term_id;
        if (!$job_status) $job_status = get_option('_update_job_status_default');
        $select = "";

        wp_nonce_field('save_job_status', 'nonce_job_status');

        $job_statuses = get_terms([
            'taxonomy' => 'job_status',
            'hide_empty' => false
        ]);

        $select .= "<select name='_job_status'>";
        foreach ($job_statuses as $status){
            $value = $status->term_id;
            $selected = selected($value, $job_status, 0);
            $select .= "<option value='{$value}' {$selected}>{$status->name}</option>";
        }
        $select .= "</select>";

        echo $select;
    }

    /**
     *
     */
    public function job_order_box()
    {
        global $post;
        $order_line = get_post_meta($post->ID, '_order_line', true);
        $url = '';
        if ($order_line) {
            $order_id = explode('-', $order_line)[0];
            $url = "post.php?post=$order_id&action=edit";
        }
        echo $order_line ? "<a href='$url'>$order_line</a>" : 'None';
    }

    /**
     * @param $post_id
     */
    public function save_post($post_id)
    {
        if ( !empty($_POST) ) {
            if ($_POST['_job_status']) {
                wp_set_object_terms($post_id, intval($_POST['_job_status']), 'job_status');
            }
            if ($_POST['_job_external_description']) {
                update_post_meta($post_id, '_job_external_description', $_POST['_job_external_description']);
            }
        }
    }

    /**
     * Admin Enqueue Scripts
     */
    public function admin_enqueue_scripts()
    {
        wp_register_script(
            'ca-jobs-js',
            plugins_url($this->plugin_name . '/assets/js/admin-script.js'),
            ['jquery'],
            $this->version,
            true
        );
        //wp_localize_script('jquery', 'wc_api_custom', ['ajaxUrl' => admin_url('admin-ajax.php')]);

        if (isset($_GET['page']) && $_GET['page'] === 'cutting-art') {
            wp_localize_script('jquery', 'caJobs', ['tab' => admin_url('edit.php?post_type=job')]);
            wp_enqueue_script('ca-jobs-js');
        }

        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'job_status') {
            wp_localize_script('jquery', 'caJobs', ['ajaxUrl' => admin_url('admin-ajax.php')]);
            wp_enqueue_script('ca-jobs-js');
        }

        if (isset($_GET['post_type']) && $_GET['post_type'] === 'job') {
            wp_localize_script('jquery', 'caJobs', ['isJobs' => true, 'ajaxUrl' => admin_url('admin-ajax.php')]);
            wp_enqueue_script('ca-jobs-js');
        }
    }

    /**
     * Front Enqueue Scripts
     */
    public function wp_enqueue_scripts()
    {
        wp_register_script(
            'ca-jobs-front-js',
            plugins_url($this->plugin_name . '/assets/js/front-script.js'),
            ['jquery'],
            $this->version,
            true
        );

        if (is_account_page() && isset($_GET['product_line'])) {
            wp_enqueue_script('ca-jobs-front-js');
        }
    }


}