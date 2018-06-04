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
     * CPLP js folder url for "plugins_url".
     */
    public $js_folder_url = 'add-contact-us-popup/assets/js/';

    /**
     * CPLP css folder url for "plugins_url".
     */
    public $css_folder_url = 'add-contact-us-popup/assets/css/';

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

        wp_localize_script('jquery', 'cplp', ['ajaxUrl' => plugins_url('add-contact-us-popup/includes/send-form-login-page.php')]);

        wp_enqueue_script('cplp-js', plugins_url($this->js_folder_url . 'script.js'), ['jquery'], $this->version, true);
        wp_enqueue_style( 'cplp-css', plugins_url($this->css_folder_url . 'style.css'), $this->version);
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