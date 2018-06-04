<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 30.05.2018
 * Time: 8:58
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WCAC_DB {

    /**
     * WCAC_DB version.
     */
    protected static $version = '0.0.1';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * @return WCAC_DB|WooCommerce
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WCAC_DB constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        add_action( 'init', [$this, 'check_version'], 5 );
    }

    /**
     * Check WCAC_DB version and run the updater is required.
     *
     * This check is done on all requests and runs if the versions do not match.
     */
    public static function check_version() {
        if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'wcac_db_version' ), self::$version, '<' ) ) {
            self::install();
        }
    }

    /**
     * Install WC.
     */
    public static function install() {
        self::create_tables();
        self::update_wcac_version();
    }


    /**
     * create tables
     */
    private static function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }
        $tables = "
CREATE TABLE {$wpdb->prefix}woocommerce_termmeta (
  meta_id BIGINT UNSIGNED NOT NULL auto_increment,
  woocommerce_term_id BIGINT UNSIGNED NOT NULL,
  meta_key varchar(255) default NULL,
  meta_value longtext NULL,
  PRIMARY KEY  (meta_id),
  KEY woocommerce_term_id (woocommerce_term_id),
  KEY meta_key (meta_key(32))
) $collate;
			";

        dbDelta($tables);
    }

    /**
     * update option "wcac_db_version"
     */
    private static function update_wcac_version() {
        delete_option( 'wcac_db_version' );
        add_option( 'wcac_db_version', self::$version );
    }

}