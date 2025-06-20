<?php
/**
 * Plugin Name:       TH Wishlist
 * Plugin URI:        https://www.themehunk.com/
 * Description:       A modern wishlist plugin for WooCommerce. Allows users to add products to a wishlist, view, and manage them.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://www.themehunk.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       th-wishlist
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 8.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'TH_Wishlist' ) ) :

/**
 * Main TH_Wishlist Class.
 *
 * @class TH_Wishlist
 */
final class TH_Wishlist {

    /**
     * @var string Plugin version.
     */
    public $version = '1.0.0';

    /**
     * @var TH_Wishlist The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main TH_Wishlist Instance.
     * Ensures only one instance of the class is loaded.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * TH_Wishlist Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define Constants.
     */
    private function define_constants() {
        define( 'THW_PLUGIN_FILE', __FILE__ );
        define( 'THW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        define( 'THW_VERSION', $this->version );
        define( 'THW_DIR', plugin_dir_path( __FILE__ ) );
        define( 'THW_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include required core files.
     */
    public function includes() {
        require_once THW_DIR . 'includes/class-th-wishlist-install.php';
        require_once THW_DIR . 'includes/class-th-wishlist-data.php';
        require_once THW_DIR . 'includes/class-th-wishlist-frontend.php';
        require_once THW_DIR . 'includes/class-th-wishlist-admin.php';
        require_once THW_DIR . 'includes/class-th-wishlist-list-table.php';

        
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        // Activation hook for installation
        register_activation_hook( THW_PLUGIN_FILE, array( 'TH_Wishlist_Install', 'install' ) );

        // Initialize classes
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Init plugin when plugins are loaded.
     */
    public function init() {
        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        // Instantiate classes
        new TH_Wishlist_Frontend();
        new TH_Wishlist_Admin();
    }

    /**
     * WooCommerce missing notice.
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p>' . sprintf( __( 'TH Wishlist requires %s to be installed and active.', 'th-wishlist' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
    }
}

endif;

/**
 * Main instance of TH_Wishlist.
 * Returns the main instance of THW.
 */
function THW() {
    return TH_Wishlist::instance();
}

// Global for backwards compatibility.
$GLOBALS['th_wishlist'] = THW();

