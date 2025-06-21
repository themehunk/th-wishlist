<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TH_Wishlist_Settings_Manager {
    
    private static $instance = null;
    private $settings = [];

    private function __construct() {

        require_once THW_DIR . 'includes/admin/class-th-wishlist-settings.php';
        $this->settings = get_option( 'th_wishlist_settings', TH_Wishlist_Settings::get_default_settings() );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_setting( $key, $default = null ) {
        return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
    }

    public function get_settings() {
        return $this->settings;
    }

    public function refresh_settings() {
        $this->settings = get_option( 'th_wishlist_settings', TH_Wishlist_Settings::get_default_settings() );
    }
}