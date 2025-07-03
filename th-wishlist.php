<?php
/**
 * Plugin Name:       TH Wishlist
 * Plugin URI:        https://themehunk.com/wishlist
 * Description:       TH Wishlist is a powerful and user-friendly wishlist plugin for WooCommerce that lets your customers save their favorite products for later and helps boost conversions by keeping users engaged with the products they love.
 * Version:           1.0.0
 * Author:            themehunk
 * Author URI:        https://www.themehunk.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       th-wishlist
 * Tested up to:      6.8
 * Requires at least: 5.0
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 8.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
 */
function thw_hpos_compatibility() {
    if ( defined( 'THW_PLUGIN_FILE' ) && class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', THW_PLUGIN_FILE, true );
    }
}
add_action( 'before_woocommerce_init', 'thw_hpos_compatibility' );


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
    public $version;

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
        $this->set_version();
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
     * Set the plugin version from the plugin header.
     */
    private function set_version() {
        $plugin_data = get_file_data(__FILE__, array('version' => 'version'), false);
        $this->version = $plugin_data['version'];
    }

    /**
     * Include required core files.
     */
    public function includes() {
        require_once THW_DIR . 'includes/admin/class-th-wishlist-list-table.php';
        require_once THW_DIR . 'includes/admin/class-th-wishlist-data.php';
        require_once THW_DIR . 'includes/admin/class-th-wishlist-admin.php';
        require_once THW_DIR . 'includes/admin/class-th-wishlist-install.php';
        require_once THW_DIR . 'includes/class-th-wishlist-settings-manager.php';
        require_once THW_DIR . 'includes/class-th-wishlist-frontend.php';
        require_once THW_DIR . 'includes/th-wishlist-front-style.php';
        require_once THW_DIR . 'includes/th-wishlist-function.php';
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        // Activation hook for installation
        register_activation_hook( THW_PLUGIN_FILE, array( 'TH_Wishlist_Install', 'install' ) );

        // Initialize classes
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_filter('plugin_action_links_'.THW_PLUGIN_BASENAME, array( $this,'th_wishlist_plugin_action_links'), 10, 1);
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
        TH_Wishlist_Settings_Manager::get_instance();
        new TH_Wishlist_Frontend();
        new TH_Wishlist_Admin();
        
        }

         /**
         * Add the settings link to the plugin row
         *
         * @param array $links - Links for the plugin
         * @return array - Links
         */
        public function th_wishlist_plugin_action_links($links) {

                      $settings_page = add_query_arg(array('page' => 'thw-wishlist'), admin_url('admin.php'));

                      $settings_link = '<a href="'.esc_url($settings_page).'">'.esc_html__('Settings', 'th-wishlist' ).'</a>';

                      array_unshift($links, $settings_link); 

                      return $links;
        }
        /**
         * Displays an admin notice if WooCommerce is not installed or active.
         */
        public function woocommerce_missing_notice() {
            // Ensure this is only called in the admin area
            if ( ! is_admin() ) {
                return;
            }
            // Use the WordPress notice classes for consistent styling
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                <?php
                    printf(
                        /* translators: 1: Plugin name, 2: woocommerce link. */
                        esc_html__( '%1$s requires %2$s to be installed and active.', 'th-wishlist' ),
                        esc_html__( 'TH Wishlist', 'th-wishlist' ), // Translatable plugin name
                        '<a href="' . esc_url( 'https://woocommerce.com/' ) . '" target="_blank">' . esc_html__( 'WooCommerce', 'th-wishlist' ) . '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
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