<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles the settings page for TH Wishlist.
 *
 * @class TH_Wishlist_Settings
 */
class TH_Wishlist_Settings {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     * Add admin menu page.
     */
    public function admin_menu() {
        add_menu_page(
            __( 'TH Wishlist', 'th-wishlist' ),
            __( 'TH Wishlist', 'th-wishlist' ),
            'manage_options',
            'thw-wishlist',
            array( $this, 'settings_page' ),
            'dashicons-heart',
            56
        );
    }

    /**
     * Render the settings page.
     */
    public function settings_page() {
        $default_columns = [ 'thumbnail', 'name', 'price', 'stock', 'add_to_cart', 'remove' ];
        $all_columns = [
            'checkbox'    => __( 'Checkbox', 'th-wishlist' ),
            'thumbnail'   => __( 'Image', 'th-wishlist' ),
            'name'        => __( 'Name & Variation', 'th-wishlist' ),
            'price'       => __( 'Price', 'th-wishlist' ),
            'stock'       => __( 'Stock Status', 'th-wishlist' ),
            'quantity'    => __( 'Quantity', 'th-wishlist' ),
            'add_to_cart' => __( 'Add to Cart', 'th-wishlist' ),
            'date'        => __( 'Date Added', 'th-wishlist' ),
            'remove'      => __( 'Remove', 'th-wishlist' ),
        ];
        $options = get_option( 'th_wishlist_settings', self::get_default_settings() );
        $saved_columns = isset( $options['th_wishlist_table_columns'] ) ? $options['th_wishlist_table_columns'] : $default_columns;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'TH Wishlist Settings', 'th-wishlist' ); ?></h1>
            <div id="thw-settings-notice"></div>
            <form id="thw-settings-form" data-nonce="<?php echo esc_attr( wp_create_nonce( 'th_wishlist_nonce' ) ); ?>">
                <h2><?php esc_html_e( 'General Settings', 'th-wishlist' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Wishlist Page', 'th-wishlist' ); ?></th>
                        <td>
                            <?php
                            wp_dropdown_pages( [
                                'name'              => 'settings[th_wcwl_wishlist_page_id]',
                                'selected'          => isset( $options['th_wcwl_wishlist_page_id'] ) ? $options['th_wcwl_wishlist_page_id'] : 0,
                                'show_option_none'  => __( 'Select a page', 'th-wishlist' ),
                            ] );
                            ?>
                            <p class="description"><?php esc_html_e( 'The page where the `[th_wcwl_wishlist]` shortcode is located.', 'th-wishlist' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Require Login', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="checkbox" name="settings[thw_require_login]" value="1" <?php checked( isset( $options['thw_require_login'] ) ? $options['thw_require_login'] : 0, 1 ); ?> />
                            <span class="description"><?php esc_html_e( 'Only logged-in users can add products to the wishlist.', 'th-wishlist' ); ?></span>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( '"Add to Wishlist" Button Settings', 'th-wishlist' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Button Style', 'th-wishlist' ); ?></th>
                        <td>
                            <select name="settings[thw_button_display_style]">
                                <option value="icon_text" <?php selected( isset( $options['thw_button_display_style'] ) ? $options['thw_button_display_style'] : 'icon_text', 'icon_text' ); ?>><?php esc_html_e( 'Icon and Text', 'th-wishlist' ); ?></option>
                                <option value="icon" <?php selected( isset( $options['thw_button_display_style'] ) ? $options['thw_button_display_style'] : 'icon_text', 'icon' ); ?>><?php esc_html_e( 'Icon Only (with Button Style)', 'th-wishlist' ); ?></option>
                                <option value="icon_only_no_style" <?php selected( isset( $options['thw_button_display_style'] ) ? $options['thw_button_display_style'] : 'icon_text', 'icon_only_no_style' ); ?>><?php esc_html_e( 'Icon Only (No Button Style)', 'th-wishlist' ); ?></option>
                                <option value="text" <?php selected( isset( $options['thw_button_display_style'] ) ? $options['thw_button_display_style'] : 'icon_text', 'text' ); ?>><?php esc_html_e( 'Text Only', 'th-wishlist' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( '"Add to Wishlist" Text', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="text" name="settings[thw_add_to_wishlist_text]" value="<?php echo esc_attr( isset( $options['thw_add_to_wishlist_text'] ) ? $options['thw_add_to_wishlist_text'] : __( 'Add to Wishlist', 'th-wishlist' ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( '"Browse Wishlist" Text', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="text" name="settings[thw_browse_wishlist_text]" value="<?php echo esc_attr( isset( $options['thw_browse_wishlist_text'] ) ? $options['thw_browse_wishlist_text'] : __( 'Browse Wishlist', 'th-wishlist' ) ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'Text shown when the product is already in the wishlist.', 'th-wishlist' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Use Custom Icon', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="checkbox" id="thw_use_custom_icon" name="settings[thw_use_custom_icon]" value="1" <?php checked( isset( $options['thw_use_custom_icon'] ) ? $options['thw_use_custom_icon'] : 0, 1 ); ?> />
                            <span class="description"><?php esc_html_e( 'Use a custom uploaded icon instead of the default heart.', 'th-wishlist' ); ?></span>
                        </td>
                    </tr>
                    <tr class="thw-custom-icon-row">
                        <th scope="row"><?php esc_html_e( 'Custom Wishlist Icon', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="text" name="settings[thw_custom_icon_url]" id="thw_custom_icon_url" value="<?php echo esc_attr( isset( $options['thw_custom_icon_url'] ) ? $options['thw_custom_icon_url'] : '' ); ?>" class="regular-text" />
                            <button type="button" class="button" id="thw_upload_icon_button"><?php esc_html_e( 'Upload Icon', 'th-wishlist' ); ?></button>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Loop Settings', 'th-wishlist' ); ?></h2>
                <table class="form-table">
                     <tr>
                        <th scope="row"><?php esc_html_e( 'Show "Add to wishlist" in loop', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="checkbox" name="settings[thw_show_in_loop]" value="1" <?php checked( isset( $options['thw_show_in_loop'] ) ? $options['thw_show_in_loop'] : 0, 1 ); ?> />
                            <span class="description"><?php esc_html_e( 'Enable the "Add to wishlist" feature in WooCommerce products loop', 'th-wishlist' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Position of "Add to wishlist" in loop', 'th-wishlist' ); ?></th>
                        <td>
                            <?php $selected_position = isset( $options['thw_in_loop_position'] ) ? $options['thw_in_loop_position'] : 'after_crt_btn';?>
                            <select name="settings[thw_in_loop_position]">
                                <option value="after_crt_btn" <?php selected( $selected_position, 'after_crt_btn' ); ?>><?php esc_html_e( 'After "Add to Cart" Button', 'th-wishlist' ); ?></option>
                                <option value="before_crt_btn" <?php selected( $selected_position, 'before_crt_btn' ); ?>><?php esc_html_e( 'Before "Add to Cart" Button', 'th-wishlist' ); ?></option>
                                <option value="on_top" <?php selected( $selected_position, 'on_top' ); ?>><?php esc_html_e( 'On Top', 'th-wishlist' ); ?></option>
                                <option value="on_shortcode" <?php selected( $selected_position, 'on_shortcode' ); ?>><?php esc_html_e( 'Use Shortcode', 'th-wishlist' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Product Page', 'th-wishlist' ); ?></h2>
                <table class="form-table">
                     
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Position of "Add to wishlist" on product page', 'th-wishlist' ); ?></th>
                        <td>
                            <?php $selected_position = isset( $options['thw_in_single_position'] ) ? $options['thw_in_single_position'] : 'after_crt_btn';?>
                            <select name="settings[thw_in_single_position]">
                                <option value="after_crt_btn" <?php selected( $selected_position, 'after_crt_btn' ); ?>><?php esc_html_e( 'After "Add to Cart"', 'th-wishlist' ); ?></option>
                                <option value="after_thumb" <?php selected( $selected_position, 'after_thumb' ); ?>><?php esc_html_e( 'After Thumbnails', 'th-wishlist' ); ?></option>
                                <option value="after_summ" <?php selected( $selected_position, 'after_summ' ); ?>><?php esc_html_e( 'After Summary', 'th-wishlist' ); ?></option>
                                <option value="on_shortcode" <?php selected( $selected_position, 'on_shortcode' ); ?>><?php esc_html_e( 'Use Shortcode', 'th-wishlist' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>


                <h2><?php esc_html_e( 'Wishlist Page Settings', 'th-wishlist' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Redirect to Cart', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="checkbox" name="settings[thw_redirect_to_cart]" value="1" <?php checked( isset( $options['thw_redirect_to_cart'] ) ? $options['thw_redirect_to_cart'] : 0, 1 ); ?> />
                            <span class="description"><?php esc_html_e( 'Redirect to the cart page after adding item(s) from the wishlist.', 'th-wishlist' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Show "Add all to cart"', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="checkbox" name="settings[thw_show_add_all_to_cart]" value="1" <?php checked( isset( $options['thw_show_add_all_to_cart'] ) ? $options['thw_show_add_all_to_cart'] : 0, 1 ); ?> />
                            <span class="description"><?php esc_html_e( 'Requires the "Checkbox" column to be enabled.', 'th-wishlist' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Show Social Share Buttons', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="checkbox" name="settings[thw_show_social_share]" value="1" <?php checked( isset( $options['thw_show_social_share'] ) ? $options['thw_show_social_share'] : 0, 1 ); ?> />
                            <span class="description"><?php esc_html_e( 'Allows logged-in users to share their wishlist.', 'th-wishlist' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Show Quantity Field', 'th-wishlist' ); ?></th>
                        <td>
                            <input type="checkbox" name="settings[thw_show_quantity]" value="1" <?php checked( isset( $options['thw_show_quantity'] ) ? $options['thw_show_quantity'] : 0, 1 ); ?> />
                            <span class="description"><?php esc_html_e( 'Allows users to manage item quantity directly in the wishlist.', 'th-wishlist' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Wishlist Table Columns', 'th-wishlist' ); ?></th>
                        <td>
                            <p class="description"><?php esc_html_e( 'Check the columns to display and drag to reorder.', 'th-wishlist' ); ?></p>
                            <ul id="thw-sortable-columns" class="thw-sortable-columns">
                                <?php
                                foreach ( $saved_columns as $key ) {

                                   
                                    if ( isset( $all_columns[ $key ] ) ) {
                                        ?>
                                        <li class="thw-sortable-item">
                                            <input type="checkbox" name="settings[th_wishlist_table_columns][]" value="<?php echo esc_attr( $key ); ?>" checked="checked" />
                                            <span class="dashicons dashicons-menu thw-drag-handle"></span>
                                            <span class="thw-column-label"><?php echo esc_html( $all_columns[ $key ] ); ?></span>
                                        </li>
                                        <?php
                                    }
                                }
                                foreach ( $all_columns as $key => $label ) {
                                    if ( ! in_array( $key, $saved_columns ) ) {
                                         
                                        ?>
                                        <li class="thw-sortable-item">
                                            <input type="checkbox" name="settings[th_wishlist_table_columns][]" value="<?php echo esc_attr( $key ); ?>" />
                                            <span class="dashicons dashicons-menu thw-drag-handle"></span>
                                            <span class="thw-column-label"><?php echo esc_html( $label ); ?></span>
                                        </li>
                                        <?php
                                    }
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'th-wishlist' ); ?></button>
                    <button type="button" class="button" id="thw-reset-settings" data-nonce="<?php echo esc_attr( wp_create_nonce( 'th_wishlist_nonce' ) ); ?>"><?php esc_html_e( 'Reset to Defaults', 'th-wishlist' ); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Get default settings.
     *
     * @return array
     */
    public static function get_default_settings() {
        return [
            'th_wcwl_wishlist_page_id'    => 0,
            'thw_require_login'           => 0,
            'thw_redirect_to_cart'        => 0,
            'thw_button_display_style'    => 'icon_text',
            'thw_add_to_wishlist_text'    => __( 'Add to Wishlist', 'th-wishlist' ),
            'thw_browse_wishlist_text'    => __( 'Browse Wishlist', 'th-wishlist' ),
            'thw_use_custom_icon'         => 0,
            'thw_custom_icon_url'         => '',
            'thw_show_in_loop'            => 1,
            'thw_in_loop_position'        => 'after_crt_btn',
            'thw_in_single_position'     => 'after_crt_btn',
            'thw_show_add_all_to_cart'    => 0,
            'thw_show_social_share'       => 0,
            'thw_show_quantity'           => 0,
            'th_wishlist_table_columns'     => [ 'thumbnail', 'name', 'price', 'stock', 'add_to_cart', 'remove' ],
        ];
    }
}