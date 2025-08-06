<?php
/**
 * Plugin Name:       TH Wishlist for WooCommerce
 * Plugin URI:        https://themehunk.com/wishlist
 * Description:       TH Wishlist is a powerful and user-friendly wishlist plugin for WooCommerce that lets your customers save their favorite products for later and helps boost conversions by keeping users engaged with the products they love.
 * Version:           1.0.8
 * Author:            themehunk
 * Author URI:        https://www.themehunk.com
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       th-wishlist
 * Tested up to:      6.8
 * Requires at least: 5.0
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 8.9
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
 */
function thwl_hpos_compatibility() {
    if ( defined( 'THWL_PLUGIN_FILE' ) && class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', THWL_PLUGIN_FILE, true );
    }
}
add_action( 'before_woocommerce_init', 'thwl_hpos_compatibility' );

if ( ! class_exists( 'THWL_Wishlist' ) ) :
/**
 * Main THWL_Wishlist Class.
 *
 * @class THWL_Wishlist
 */
final class THWL_Wishlist {

    /**
     * @var string Plugin version.
     */
    public $version;

    /**
     * @var THWL_Wishlist The single instance of the class
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
        $this->thwl_define_constants();
        $this->thwl_set_version();
        $this->thwl_includes();
        $this->thwl_init_hooks();
    }

    /**
     * Define Constants.
     */
    private function thwl_define_constants() {
        define( 'THWL_PLUGIN_FILE', __FILE__ );
        define( 'THWL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        define( 'THWL_VERSION', $this->version );
        define( 'THWL_DIR', plugin_dir_path( __FILE__ ) );
        define( 'THWL_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Set the plugin version from the plugin header.
     */
    private function thwl_set_version() {
        $plugin_data = get_file_data( __FILE__, array( 'version' => 'version' ), false );
        $this->version = $plugin_data['version'];
    }

    /**
     * Include required core files.
     */
    public function thwl_includes() {
        require_once THWL_DIR . 'includes/admin/class-th-wishlist-list-table.php';
        require_once THWL_DIR . 'includes/admin/class-th-wishlist-data.php';
        require_once THWL_DIR . 'includes/admin/class-th-wishlist-admin.php';
        require_once THWL_DIR . 'includes/admin/class-th-wishlist-install.php';
        require_once THWL_DIR . 'includes/class-th-wishlist-settings-manager.php';
        require_once THWL_DIR . 'includes/class-th-wishlist-frontend.php';
        require_once THWL_DIR . 'includes/th-wishlist-function.php';
    }

    /**
     * Hook into actions and filters.
     */
    private function thwl_init_hooks() {
        // Activation hook for installation
        register_activation_hook( THWL_PLUGIN_FILE, array( 'THWL_Install', 'install' ) );

        // Initialize classes
        add_action( 'plugins_loaded', array( $this, 'thwl_init' ) );

        // Add plugin action links on init
        add_action( 'init', array( $this, 'thwl_add_plugin_action_links' ) );
    }

    /**
     * Add plugin action links filter on init.
     */
    public function thwl_add_plugin_action_links() {
        add_filter( 'plugin_action_links_' . THWL_PLUGIN_BASENAME, array( $this, 'thwl_wishlist_plugin_action_links' ), 10, 1 );
    }

    /**
     * Init plugin when plugins are loaded.
     */
    public function thwl_init() {
        // Move WooCommerce check to init hook
        add_action( 'init', array( $this, 'thwl_check_woocommerce' ) );
    }

    /**
     * Check if WooCommerce is active and set up admin notice if not.
     */
    public function thwl_check_woocommerce() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'thwl_woocommerce_missing_notice' ) );
            return;
        }
        // Instantiate classes only if WooCommerce is active
        THWL_Manager::get_instance();
        new THWL_Frontend();
        new THWL_Admin();
    }

    /**
     * Add the settings link to the plugin row.
     *
     * @param array $links - Links for the plugin
     * @return array - Links
     */
    public function thwl_wishlist_plugin_action_links( $links ) {
        $settings_page = add_query_arg( array( 'page' => 'thwl-wishlist' ), admin_url( 'admin.php' ) );
        $settings_link = '<a href="' . esc_url( $settings_page ) . '">' . esc_html__( 'Settings', 'th-wishlist' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Displays an admin notice if WooCommerce is not installed or active.
     */
    public function thwl_woocommerce_missing_notice() {
        if ( ! is_admin() ) {
            return;
        }
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                printf(
                    /* translators: 1: Plugin name, 2: woocommerce link. */
                    esc_html__( '%1$s requires %2$s to be installed and active.', 'th-wishlist' ),
                    esc_html__( 'TH Wishlist', 'th-wishlist' ),
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
 * Main instance of THWL_Wishlist.
 * Returns the main instance of THW.
 */
function THWL() {
    return THWL_Wishlist::instance();
}

// Global for backwards compatibility.
$GLOBALS['thwl_wishlist'] = THWL();