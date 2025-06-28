<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin-facing functions and hooks for TH Wishlist.
 *
 * @class TH_Wishlist_Admin
 */
class TH_Wishlist_Admin {

    public function __construct() {
        // Include and instantiate settings, tracking, and AJAX handlers.
        require_once THW_DIR . 'includes/admin/class-th-wishlist-settings.php';
        require_once THW_DIR . 'includes/admin/class-th-wishlist-settings-ajax.php';
        require_once THW_DIR . 'includes/admin/class-th-wishlist-track.php';
        new TH_Wishlist_Settings();
        new TH_Wishlist_Settings_Ajax();
        new TH_Wishlist_Tracking();
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ));
    }

    /**
     * Enqueue admin scripts for the color picker and media uploader.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_thw-wishlist' !== $hook && 'th-wishlist_page_thw-wishlists-tracking' !== $hook ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        // Enqueue custom admin styles.
            wp_enqueue_style(
                'th-wishlist-admin',
                THW_URL . 'assets/css/admin.css',
                [],
                '1.0.4'
            );

             wp_enqueue_style(
                'pickr-style',
                THW_URL . 'assets/css/pickr.min.css',
                [],
                '1.0.0'
            );

            // Enqueue custom admin scripts with localized data.
            wp_enqueue_script(
                'pickr-script',
                THW_URL . 'assets/js/pickr.min.js',
                ['jquery'],
                '1.5.1',
                true
            );
            wp_enqueue_script(
                'th-wishlist-admin',
                THW_URL . 'assets/js/admin.js',
                [ 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ],
                '1.1.6',
                true
            );

            wp_localize_script(
                'th-wishlist-admin',
                'thWishlistAdmin',
                [
                    'ajax_url'  => admin_url( 'admin-ajax.php' ),
                    'nonce'     => wp_create_nonce( 'th_wishlist_nonce' ),
                    'i18n'      => [
                        'save_success'  => __( 'Settings saved successfully!', 'th-wishlist' ),
                        'save_error'    => __( 'Error saving settings. Please try again.', 'th-wishlist' ),
                        'reset_success' => __( 'Settings reset to defaults!', 'th-wishlist' ),
                        'reset_error'   => __( 'Error resetting settings. Please try again.', 'th-wishlist' ),
                        'confirm_reset' => __( 'Are you sure you want to reset all settings to default?', 'th-wishlist' ),
                    ],
                ]
            );
      } 
}