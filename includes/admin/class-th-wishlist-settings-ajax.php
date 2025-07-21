<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles AJAX requests for TH Wishlist admin.
 *
 * @class THWL_Ajax
 */
class THWL_Ajax {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_thwl_save_settings', array( $this, 'thwl_save_settings' ) );
        add_action( 'wp_ajax_thwl_reset_settings', array( $this, 'thwl_reset_settings' ) );
    }

    /**
     * Sanitize form data.
     *
     * @param array $data The form data to sanitize.
     * @return array Sanitized and validated data.
     */
    private function thwl_sanitize_form_data( $data ) {
        $sanitized = [];
        $defaults = THWL_Settings::thwl_get_default_settings();

        // Define valid columns for th_wishlist_table_columns and th_wishlist_table_column_labels
        $valid_columns = [
            'checkbox'    => __( 'Checkbox', 'th-wishlist' ),
            'thumbnail'   => __( 'Image', 'th-wishlist' ),
            'name'        => __( 'Name', 'th-wishlist' ),
            'price'       => __( 'Price', 'th-wishlist' ),
            'stock'       => __( 'Stock Status', 'th-wishlist' ),
            'quantity'    => __( 'Quantity', 'th-wishlist' ),
            'add_to_cart' => __( 'Add to Cart', 'th-wishlist' ),
            'date'        => __( 'Date Added', 'th-wishlist' ),
            'remove'      => __( 'Remove', 'th-wishlist' ),
        ];

        // Define valid values for specific fields
        $valid_values = [
            'thw_button_display_style' => ['icon_only', 'text_only', 'icon_text'],
            'thw_in_loop_position'     => ['before_crt_btn', 'after_crt_btn', 'shortcode'],
            'thw_in_single_position'   => ['before_crt_btn', 'after_crt_btn', 'shortcode'],
            'th_wishlist_add_icon'     => ['heart-outline', 'heart-filled', 'star'], // Add other valid icons
            'th_wishlist_brws_icon'    => ['heart-outline', 'heart-filled', 'star'], // Add other valid icons
        ];

        // Define sanitization rules for each field
        $sanitization_rules = [
            'thwl_page_id'                    => 'absint',
            'thw_require_login'               => 'absint',
            'thw_show_in_loop'                => 'absint',
            'thw_show_in_product'             => 'absint',
            'thw_button_display_style'        => 'sanitize_text_field',
            'thw_add_to_wishlist_text'        => 'sanitize_text_field',
            'thw_browse_wishlist_text'        => 'sanitize_text_field',
            'thw_in_loop_position'            => 'sanitize_text_field',
            'thw_in_single_position'          => 'sanitize_text_field',
            'th_wishlist_table_columns'       => 'sanitize_key',
            'th_wishlist_table_column_labels' => 'sanitize_text_field',
            'th_wishlist_add_icon'            => 'sanitize_text_field',
            'th_wishlist_brws_icon'           => 'sanitize_text_field',
            'th_wishlist_add_icon_color'      => 'sanitize_text_field',
            'th_wishlist_brws_icon_color'     => 'sanitize_text_field',
            'th_wishlist_btn_bg_color'        => 'sanitize_text_field',
            'th_wishlist_btn_txt_color'       => 'sanitize_text_field',
            'th_wishlist_tb_btn_txt_color'    => 'sanitize_text_field',
            'th_wishlist_tb_btn_bg_color'     => 'sanitize_text_field',
            'th_wishlist_table_bg_color'      => 'sanitize_text_field',
            'th_wishlist_table_brd_color'     => 'sanitize_text_field',
            'th_wishlist_table_txt_color'     => 'sanitize_text_field',
            'th_wishlist_shr_fb_color'        => 'sanitize_text_field',
            'th_wishlist_shr_fb_hvr_color'    => 'sanitize_text_field',
            'th_wishlist_shr_x_color'         => 'sanitize_text_field',
            'th_wishlist_shr_x_hvr_color'     => 'sanitize_text_field',
            'th_wishlist_shr_w_color'         => 'sanitize_text_field',
            'th_wishlist_shr_w_hvr_color'     => 'sanitize_text_field',
            'th_wishlist_shr_e_color'         => 'sanitize_text_field',
            'th_wishlist_shr_e_hvr_color'     => 'sanitize_text_field',
            'th_wishlist_shr_c_color'         => 'sanitize_text_field',
            'th_wishlist_shr_c_hvr_color'     => 'sanitize_text_field',
        ];

        /**
         * Sanitize individual array items.
         *
         * @param mixed  $item The value to sanitize.
         * @param string $key The key of the item.
         * @param array  $context Valid columns, rules, valid values, and parent key.
         */
        function thwl_sanitize_array_item( &$item, $key, $context ) {
            $valid_columns = $context['valid_columns'];
            $rules = $context['rules'];
            $parent_key = $context['parent_key'];

            if ( $parent_key === 'th_wishlist_table_columns' ) {
                // Sanitize and validate column keys
                $item = sanitize_key( $item );
                if ( ! array_key_exists( $item, $valid_columns ) ) {
                    $item = null; // Discard invalid column keys
                }
            } elseif ( $parent_key === 'th_wishlist_table_column_labels' ) {
                // Sanitize label values and ensure key is valid
                if ( array_key_exists( $key, $valid_columns ) ) {
                    $item = sanitize_text_field( $item );
                } else {
                    $item = null; // Discard labels for invalid keys
                }
            } else {
                // Apply rule if defined, otherwise null
                if ( isset( $rules[ $parent_key ] ) ) {
                    if ( is_callable( $rules[ $parent_key ] ) ) {
                        $item = call_user_func( $rules[ $parent_key ], $item );
                    } else {
                        $item = null;
                    }
                } else {
                    $item = null;
                }
            }
        }

        foreach ( $defaults as $key => $default_value ) {
            if ( ! isset( $data[ $key ] ) ) {
                // Use default if value is not provided
                $sanitized[ $key ] = is_array( $default_value ) ? [] : $default_value;
                continue;
            }

            if ( is_array( $data[ $key ] ) ) {
                // Sanitize array fields
                $sanitized[ $key ] = [];
                array_walk_recursive(
                    $data[ $key ],
                    'thwl_sanitize_array_item',
                    [
                        'valid_columns' => $valid_columns,
                        'rules'         => $sanitization_rules,
                        'valid_values'  => $valid_values,
                        'parent_key'    => $key,
                    ]
                );
                $sanitized[ $key ] = array_filter( $data[ $key ], function( $item ) {
                    return ! is_null( $item );
                } );
                // Preserve key-value structure for associative arrays
                if ( $key === 'th_wishlist_table_column_labels' ) {
                    $sanitized[ $key ] = array_intersect_key( $sanitized[ $key ], $valid_columns );
                }
                // Fallback to all columns if empty
                if ( $key === 'th_wishlist_table_columns' && empty( $sanitized[ $key ] ) ) {
                    $sanitized[ $key ] = array_keys( $valid_columns );
                }
            } else {
                // Sanitize scalar fields
                if ( isset( $sanitization_rules[ $key ] ) ) {
                    if ( is_callable( $sanitization_rules[ $key ] ) ) {
                        $value = call_user_func( $sanitization_rules[ $key ], $data[ $key ] );
                        // Validate against valid_values for specific fields
                        if ( isset( $valid_values[ $key ] ) ) {
                            $sanitized[ $key ] = in_array( $value, $valid_values[ $key ], true ) ? $value : $default_value;
                        } else {
                            $sanitized[ $key ] = $value ?: $default_value;
                        }
                    } else {
                        $sanitized[ $key ] = $default_value;
                    }
                } else {
                    // Default to text sanitization for unknown fields
                    $sanitized[ $key ] = sanitize_text_field( $data[ $key ] );
                }
            }
        }

        return $sanitized;
    }

    /**
     * Save settings via AJAX.
     */
    public function thwl_save_settings() {
        // Verify nonce
        check_ajax_referer( 'thwl_wishlist_nonce', '_wpnonce' );

        // Ensure user has permission
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Invalid permissions.', 'th-wishlist' ) );
        }

        // Check if settings are provided and are an array
        if ( ! isset( $_POST['settings'] ) || ! is_array( $_POST['settings'] ) ) {
            wp_send_json_error( __( 'Error saving settings. Invalid data.', 'th-wishlist' ) );
        }
        // Unslash and sanitize the settings array
        $sanitized_data = $this->thwl_sanitize_form_data( wp_unslash( $_POST['settings'] ) );
        if ( ! empty( $sanitized_data ) ) {
            update_option( 'thwl_settings', $sanitized_data );
            wp_send_json_success( __( 'Settings saved successfully!', 'th-wishlist' ) );
        }

        wp_send_json_error( __( 'Error saving settings. Invalid data.', 'th-wishlist' ) );
    }

    /**
     * Reset settings to defaults via AJAX.
     */
    public function thwl_reset_settings() {
        // Verify nonce
        check_ajax_referer( 'thwl_wishlist_nonce', '_wpnonce' );
        // Ensure user has permission
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Invalid permissions.', 'th-wishlist' ) );
        }
        // Get and save default settings
        $defaults = THWL_Settings::thwl_get_default_settings();
        update_option( 'thwl_settings', $defaults );
        wp_send_json_success( __( 'Settings reset to defaults!', 'th-wishlist' ) );
    }
}