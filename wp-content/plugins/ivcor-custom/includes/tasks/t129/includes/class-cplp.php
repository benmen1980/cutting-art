<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 24.05.2018
 * Time: 8:28
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CPLP {

    /**
     * CPLP version.
     */
    public $version = '0.0.1';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * @return CPLP
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * CPLP constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {

        add_action('login_enqueue_scripts', [$this, 'login_enqueue_scripts']);
        add_action('wp_logout', [$this, 'wp_logout']);

        add_action( 'wp_ajax_nopriv_cplp_send_form_login_page', [$this, 'cplp_send_form_login_page'] );
        add_action( 'wp_ajax_cplp_send_form_login_page', [$this, 'cplp_send_form_login_page'] );
    }

    /**
     * Enqueue scripts for login page
     */
    public function login_enqueue_scripts(){

        wp_localize_script('jquery', 'cplp', ['ajaxUrl' => admin_url('admin-ajax.php')]);

        wp_enqueue_script('cplp-js',  plugin_dir_url(dirname(__FILE__, 1)).'assets/js/script.js', ['jquery'], $this->version, true);
        wp_enqueue_style( 'cplp-css', plugin_dir_url(dirname(__FILE__, 1)).'assets/css/style.css', $this->version);
    }

    /**
     * Send Form from Login Page
     */
    public function cplp_send_form_login_page(){
        $options = $_POST['options'];

        $options_name = [
            'cplp-firstname' =>   'First Name',
            'cplp-lastname' =>    'Last Name',
            'cplp-companyname' => 'Company Name',
            'cplp-street' =>      'Street',
            'cplp-city' =>        'City',
            'cplp-zipcode' =>     'Zip Code',
            'cplp-email' =>       'Email',
            'cplp-subject' =>     'Subject',
            'cplp-message' =>     'Message'
        ];

        $to = get_option('admin_email', true);
        $subject = 'Contact Us';
        $message = '';
        if ($options) {
            foreach ($options as $option_key => $option_val)
                $message .= "<strong>{$options_name[$option_key]}:</strong> {$option_val}<br>";

            $headers = array(
                'content-type: text/html'
            );

            $res = wp_mail($to, $subject, $message, $headers);
            echo $res;
        }
        exit;
    }

    /**
     * Hook logout
     */
    public function wp_logout(){
        if (isset($_POST['action']) && $_POST['action'] === 'cplp_send_form_login_page') {
            $this->cplp_send_form_login_page();
            exit;
        }
    }

}