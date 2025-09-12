<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles the settings page for TH Wishlist.
 *
 * @class THWL_Settings
 */
class THWL_Settings {

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
            'thwl-wishlist',
            array( $this, 'settings_page' ),
            'dashicons-heart',
            56
        );
    }
/**
 * Render Wishlist Settings Tabs
 */
function thwl_render_settings_tabs() {

    $tabs = [
        'general'  => esc_html__( 'General Settings', 'th-wishlist' ),
        'button'   => esc_html__( 'Wishlist Button', 'th-wishlist' ),
        'loop'     => esc_html__( 'Loop Settings', 'th-wishlist' ),
        'product'  => esc_html__( 'Product Page', 'th-wishlist' ),
        'wishlist' => esc_html__( 'Wishlist Page', 'th-wishlist' ),
        'style'    => esc_html__( 'Style Customization', 'th-wishlist' ),
    ];
    /**
     * Allow Pro plugin or other addons to add new tabs.
     */
    $tabs = apply_filters( 'thwl_pro_settings_tabs', $tabs );
    ?>
    <ul class="thw-tabs-list">
        <?php 
        $first = true;
        foreach ( $tabs as $id => $label ) : ?>
            <li class="thw-tab <?php echo esc_attr($first) ? 'active' : ''; ?>" data-tab="<?php echo esc_attr( $id ); ?>">
                <?php echo esc_html( $label ); ?>
            </li>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </ul>
    <?php
}

public function settings_page() {
    $all_columns = apply_filters( 'thwl_all_columns', [
    'checkbox'    => __( 'Checkbox', 'th-wishlist' ),
    'thumbnail'   => __( 'Image', 'th-wishlist' ),
    'name'        => __( 'Name', 'th-wishlist' ),
    'price'       => __( 'Price', 'th-wishlist' ),
    'stock'       => __( 'Stock Status', 'th-wishlist' ),
    'quantity'    => __( 'Quantity', 'th-wishlist' ),
    'add_to_cart' => __( 'Add to Cart', 'th-wishlist' ),
    'date'        => __( 'Date Added', 'th-wishlist' ),
    'remove'      => __( 'Remove', 'th-wishlist' ),
    ] );

    $default_columns = apply_filters( 'thwl_default_columns', [
        'thumbnail',
        'name',
        'price',
        'stock',
        'add_to_cart',
        'remove'
    ] );
    
    $options = get_option( 'thwl_settings', self::thwl_get_default_settings() );
    $saved_columns = isset( $options['th_wishlist_table_columns'] ) ? $options['th_wishlist_table_columns'] : $default_columns;
    $labels = isset( $options['th_wishlist_table_column_labels'] ) ? $options['th_wishlist_table_column_labels'] : self::thwl_get_default_settings()['th_wishlist_table_column_labels'];
?>
<div class="wrap">
    <div id="thw-settings-notice"></div>
    <div class="thw-tabs-container">
        <div class="thw-tabs-nav">
            <div class="thw-title-content">
                <div id="logo">
						<a href="https://themehunk.com/" target="_blank">
						<img src="<?php echo esc_url(THWL_URL.'assets/images/th-logo.png') ?>" alt="th-logo">
					</a>
					</div>
            <h3><?php esc_html_e( 'TH Wishlist', 'th-wishlist' ); ?></h3>
            </div>
            <?php $this->thwl_render_settings_tabs(); ?>
        </div>
        <form id="thw-settings-form" data-nonce="<?php echo esc_attr( wp_create_nonce( 'thwl_wishlist_nonce' ) ); ?>">
            <div class="thw-tabs-content">
                <div id="general" class="thw-tab-content active">
                    <h3 class="thws-content-title"><?php esc_html_e( 'General Settings', 'th-wishlist' ); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Wishlist Page', 'th-wishlist' ); ?></th>
                            <td>
                            <?php
                          
    // Retrieve the stored wishlist page ID, default to empty string if not set
    $selected_page_id = isset($options['thwl_page_id']) && !empty($options['thwl_page_id']) 
    ? absint($options['thwl_page_id']) 
    : get_option('thwl_page_id');
                                wp_dropdown_pages([
                                    'name'              => 'settings[thwl_page_id]', // Corrected to match form structure
                                    'selected'          => absint($selected_page_id), // Sanitize as integer
                                    'show_option_none'  => esc_html__('Select a page', 'th-wishlist'), // Escaped for safety
                                ]);
                                ?>                       
                                <p class="description"><?php esc_html_e( 'The page where the `[thwl_wishlist]` shortcode is located.', 'th-wishlist' ); ?></p>
                            </td>
                        </tr>
                        <tr class="th-row-with-checkbox">
                            <th scope="row"><?php esc_html_e( 'Require Login', 'th-wishlist' ); ?></th>
                            <td>
                                <input type="checkbox" name="settings[thw_require_login]" value="1" <?php checked( isset( $options['thw_require_login'] ) ? $options['thw_require_login'] : 0, 1 ); ?> />
                                <span class="description"><?php esc_html_e( 'Only logged-in users can add products to the wishlist.', 'th-wishlist' ); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="button" class="thw-tab-content">
                    <h3 class="thws-content-title"><?php esc_html_e( 'Wishlist Button', 'th-wishlist' ); ?></h3>
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
                        <tr class="th-row-with-checkbox">
                            <th scope="row"><?php esc_html_e( 'Theme Default Style', 'th-wishlist' ); ?></th>
                            <td>
                                <input type="checkbox" name="settings[thw_btn_style_theme]" value="1" <?php checked( isset( $options['thw_btn_style_theme'] ) ? $options['thw_btn_style_theme'] : 0, 1 ); ?> />
                                <span class="description"><?php esc_html_e( 'Choose to Wishlist button style as theme', 'th-wishlist' ); ?></span>
                            </td>
                        </tr>
                        
                    </table>
                </div>
                <div id="loop" class="thw-tab-content">
                     <h3 class="thws-content-title"><?php esc_html_e( 'Loop Settings', 'th-wishlist' ); ?></h3>
                    <table class="form-table">
                        <tr class="th-row-with-checkbox">
                            <th scope="row"><?php esc_html_e( 'Show "Add to wishlist" in loop', 'th-wishlist' ); ?></th>
                            <td>
                                <input type="checkbox" name="settings[thw_show_in_loop]" value="1" <?php checked( isset( $options['thw_show_in_loop'] ) ? $options['thw_show_in_loop'] : 0, 1 ); ?> />
                                <span class="description"><?php esc_html_e( 'Enable the "Add to wishlist" feature in WooCommerce products loop', 'th-wishlist' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Position of "Add to wishlist" in loop', 'th-wishlist' ); ?></th>
                            <td>
                                <?php $selected_position = isset( $options['thw_in_loop_position'] ) ? $options['thw_in_loop_position'] : 'after_crt_btn'; ?>
                                <select name="settings[thw_in_loop_position]">
                                    <option value="after_crt_btn" <?php selected( $selected_position, 'after_crt_btn' ); ?>><?php esc_html_e( 'After "Add to Cart" Button', 'th-wishlist' ); ?></option>
                                    <option value="before_crt_btn" <?php selected( $selected_position, 'before_crt_btn' ); ?>><?php esc_html_e( 'Before "Add to Cart" Button', 'th-wishlist' ); ?></option>
                                    <option value="on_top" <?php selected( $selected_position, 'on_top' ); ?>><?php esc_html_e( 'On Top', 'th-wishlist' ); ?></option>
                                    <option value="on_shortcode" <?php selected( $selected_position, 'on_shortcode' ); ?>><?php esc_html_e( 'Use Shortcode', 'th-wishlist' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="product" class="thw-tab-content">
                     <h3 class="thws-content-title"><?php esc_html_e( 'Product Page', 'th-wishlist' ); ?></h3>
                    <table class="form-table">
                        <tr class="th-row-with-checkbox">
                            <th scope="row"><?php esc_html_e( 'Show "Add to wishlist" in Product Page', 'th-wishlist' ); ?></th>
                            <td>
                                <input type="checkbox" name="settings[thw_show_in_product]" value="1" <?php checked( isset( $options['thw_show_in_product'] ) ? $options['thw_show_in_product'] : 0, 1 ); ?> />
                                <span class="description"><?php esc_html_e( 'Enable the "Add to wishlist" feature in WooCommerce products Page', 'th-wishlist' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Position of "Add to wishlist" on product page', 'th-wishlist' ); ?></th>
                            <td>
                                <?php $selected_position = isset( $options['thw_in_single_position'] ) ? $options['thw_in_single_position'] : 'after_crt_btn'; ?>
                                <select name="settings[thw_in_single_position]">
                                    <option value="after_crt_btn" <?php selected( $selected_position, 'after_crt_btn' ); ?>><?php esc_html_e( 'After "Add to Cart"', 'th-wishlist' ); ?></option>
                                    <option value="after_summ" <?php selected( $selected_position, 'after_summ' ); ?>><?php esc_html_e( 'After Summary', 'th-wishlist' ); ?></option>
                                    <option value="before_summ" <?php selected( $selected_position, 'before_summ' ); ?>><?php esc_html_e( 'Before Summary', 'th-wishlist' ); ?></option>
                                    <option value="on_shortcode" <?php selected( $selected_position, 'on_shortcode' ); ?>><?php esc_html_e( 'Use Shortcode', 'th-wishlist' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="wishlist" class="thw-tab-content">
                    <h3 class="thws-content-title"><?php esc_html_e( 'Wishlist Page', 'th-wishlist' ); ?></h3>
                    <table class="form-table">
                        <tr class="th-row-with-checkbox">
                            <th scope="row"><?php esc_html_e( 'Redirect to Cart', 'th-wishlist' ); ?></th>
                            <td>
                                <input type="checkbox" name="settings[thw_redirect_to_cart]" value="1" <?php checked( isset( $options['thw_redirect_to_cart'] ) ? $options['thw_redirect_to_cart'] : 0, 1 ); ?> />
                                <span class="description"><?php esc_html_e( 'Remove Product in Wishlist table and Redirect to the cart page after adding item(s) from the wishlist.', 'th-wishlist' ); ?></span>
                            </td>
                        </tr>
                        <tr class="th-row-with-checkbox">
                            <th scope="row"><?php esc_html_e( 'Show Social Share Buttons', 'th-wishlist' ); ?></th>
                            <td>
                                <input type="checkbox" name="settings[thw_show_social_share]" value="1" <?php checked( isset( $options['thw_show_social_share'] ) ? $options['thw_show_social_share'] : 0, 1 ); ?> />
                                <span class="description"><?php esc_html_e( 'Allows to share their wishlist.', 'th-wishlist' ); ?></span>
                            </td>
                        </tr>
                        <tr class="th-sort-row">
                            <th scope="row"><?php esc_html_e( 'Wishlist Table Columns', 'th-wishlist' ); ?></th>
                            <td>
                                <p class="description"><?php esc_html_e( 'Check the columns to display and drag to reorder.', 'th-wishlist' ); ?></p>
                                <ul id="thw-sortable-columns" class="thw-sortable-columns">
                                    <?php
                                    foreach ( $saved_columns as $key ) {
                                        if ( isset( $all_columns[ $key ] ) ) {
                                            $label = isset( $labels[ $key ] ) ? $labels[ $key ] : $all_columns[ $key ];
                                    ?>
                                    <li class="thw-sortable-item">
                                        <input type="checkbox" name="settings[th_wishlist_table_columns][]" value="<?php echo esc_attr( $key ); ?>" checked="checked" />
                                        <span class="dashicons dashicons-menu thw-drag-handle"></span>
                                        <span class="thw-column-label">
                                            <?php echo esc_html( $all_columns[ $key ] ); ?>:
                                            <input type="text" name="settings[th_wishlist_table_column_labels][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" />
                                        </span>
                                    </li>
                                    <?php
                                        }
                                    }
                                    foreach ( $all_columns as $key => $label ) {
                                        if ( ! in_array( $key, $saved_columns ) ) {
                                            $custom_label = isset( $labels[ $key ] ) ? $labels[ $key ] : $label;
                                    ?>
                                    <li class="thw-sortable-item">
                                        <input type="checkbox" name="settings[th_wishlist_table_columns][]" value="<?php echo esc_attr( $key ); ?>" />
                                        <span class="dashicons dashicons-menu thw-drag-handle"></span>
                                        <span class="thw-column-label">
                                            <?php echo esc_html( $label ); ?>
                                            <input type="text" name="settings[th_wishlist_table_column_labels][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $custom_label ); ?>" class="regular-text" />
                                        </span>
                                    </li>
                                    <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </td>
                        </tr>
                        <tr class="th-row-with-checkbox">
                            <th scope="row"><?php esc_html_e( 'Redirect to Wishlist Page via shorcode', 'th-wishlist' ); ?></th>
                            <td>
                               <input type="checkbox" 
            id="thw_redirect_wishlist_page" 
            name="settings[thw_redirect_wishlist_page]" 
            value="1" 
            <?php checked( isset( $options['thw_redirect_wishlist_page'] ) ? $options['thw_redirect_wishlist_page'] : 0, 1 ); ?> 
        />
                                <span class="description">
                                     <?php esc_html_e( 'Use this shortcode anywhere on your site to create a Icon that redirects users to the Wishlist page. Example: [thwl_wishlist_redirect]', 'th-wishlist' ); ?>
                                </span>
                            </td>
                        </tr>
                        <tr class="th-row thw-redirect-wishlist-dependent">
                        <th scope="row"><?php esc_html_e( 'Add to Wishlist Icon', 'th-wishlist' ); ?></th>
                        <td>
                            <?php 
                            $selected_page_icon = $options['thw_redirect_wishlist_page_icon'];
                            $redirect_wishlist_page_icon = thwl_get_wishlist_icons_svg();
                            $thw_redirect_wishlist_page_icon_color = isset( $options['thw_redirect_wishlist_page_icon_color'] ) ? $options['thw_redirect_wishlist_page_icon_color'] : '#111';
                            $thw_redirect_wishlist_page_icon_color_hvr = isset( $options['thw_redirect_wishlist_page_icon_color_hvr'] ) ? $options['thw_redirect_wishlist_page_icon_color_hvr'] : '#111';
                            $allowed_page_svg_tags = array(
                                'svg'  => array(
                                    'class'        => true,
                                    'width'        => true,
                                    'height'       => true,
                                    'viewbox'      => true,
                                    'fill'         => true,
                                    'stroke'       => true,
                                    'stroke-width' => true,
                                    'xmlns'        => true,
                                ),
                                'path' => array(
                                    'd'              => true,
                                    'fill'           => true,
                                    'stroke'         => true,
                                    'stroke-linecap' => true,
                                    'stroke-linejoin'=> true,
                                    'clip-rule'      => true,
                                    'fill-rule'      => true,
                                ),
                            );
                            ?>
                            <p><?php esc_html_e( 'Choose Page wishlist icon', 'th-wishlist' ); ?></p>
                            <?php foreach ( $redirect_wishlist_page_icon as $icon1_key => $icon1_data ) : ?>
                                <label class="thw-dashicon-option">
                                    <input type="radio"
                                        name="settings[thw_redirect_wishlist_page_icon]"
                                        value="<?php echo esc_attr( $icon1_key ); ?>"
                                        <?php checked($selected_page_icon, $icon1_key ); ?> />
                                        <span title="<?php echo esc_attr( $icon1_data['name'] ); ?>">
                                        <?php echo wp_kses( $icon1_data['svg'], $allowed_page_svg_tags); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                            </td>
                       </tr>
                       <?php $thw_redirect_wishlist_page_icon_size = isset( $options['thw_redirect_wishlist_page_icon_size'] ) ? $options['thw_redirect_wishlist_page_icon_size'] : '';?>
                       <tr class="th-row thw-redirect-wishlist-dependent">
                        <th scope="row"><?php esc_html_e( 'Page Icon', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[thw_redirect_wishlist_page_icon_color]"  value="<?php echo esc_attr( $thw_redirect_wishlist_page_icon_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $thw_redirect_wishlist_page_icon_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Hover color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[thw_redirect_wishlist_page_icon_color_hvr]"  value="<?php echo esc_attr( $thw_redirect_wishlist_page_icon_color_hvr ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $thw_redirect_wishlist_page_icon_color_hvr ); ?>" />
                            </div>
                        <div class="th-number">
                        <p><?php esc_html_e( 'Size', 'th-wishlist' ); ?></p>
                         <input type="number" class="small-text" id="thw_redirect_wishlist_page_icon_size" name="settings[thw_redirect_wishlist_page_icon_size]" value="<?php echo esc_attr( $thw_redirect_wishlist_page_icon_size ); ?>" min="0" max="100" step="">
                         <span><?php esc_html_e( 'px', 'th-wishlist' ); ?></span>
                        </div>
                        </td>
                     </tr>
                    </table>
                </div>
                <div id="style" class="thw-tab-content">
                  <h3 class="thws-content-title"><?php esc_html_e( 'Wishlist Button', 'th-wishlist' ); ?></h3>
                  <table class="form-table">
                    <?php $allowed_svg_tags = array(
                                'svg'  => array(
                                    'class'        => true,
                                    'width'        => true,
                                    'height'       => true,
                                    'viewbox'      => true,
                                    'fill'         => true,
                                    'stroke'       => true,
                                    'stroke-width' => true,
                                    'xmlns'        => true,
                                ),
                                'path' => array(
                                    'd'              => true,
                                    'fill'           => true,
                                    'stroke'         => true,
                                    'stroke-linecap' => true,
                                    'stroke-linejoin'=> true,
                                    'clip-rule'      => true,
                                    'fill-rule'      => true,
                                ),
                            );?>
                    <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Add to Wishlist Icon', 'th-wishlist' ); ?></th>
                        <td>
                            <?php 
                            $selected_icon = $options['th_wishlist_add_icon'];
                            $addicondashicons = thwl_get_wishlist_icons_svg();
                            $th_wishlist_add_icon_color = isset( $options['th_wishlist_add_icon_color'] ) ? $options['th_wishlist_add_icon_color'] : '#111';
                            ?>
                            <p><?php esc_html_e( 'Choose add to wishlist icon', 'th-wishlist' ); ?></p>
                           <?php foreach ( $addicondashicons as $icon_key => $icon_data ) : ?>
                                <label class="thw-dashicon-option">
                                    <input type="radio"
                                        name="settings[th_wishlist_add_icon]"
                                        value="<?php echo esc_attr( $icon_key ); ?>"
                                        <?php checked( $selected_icon, $icon_key ); ?> />
                                    <span title="<?php echo esc_attr( $icon_data['name'] ); ?>">
                                        <?php echo wp_kses( $icon_data['svg'], $allowed_svg_tags ); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                            <div class="th-color-picker">
                            <p><?php esc_html_e( 'Add to Wishlist Icon color', 'th-wishlist' ); ?></p>
                            <input type="text" name="settings[th_wishlist_add_icon_color]"  value="<?php echo esc_attr( $th_wishlist_add_icon_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_add_icon_color ); ?>" />
                            <div>
                        </td>
                        
                    </tr>
                    <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Browse Wishlist Icon', 'th-wishlist' ); ?></th>
                        <td>
                            <?php 
                            $selected_brws_icon = $options['th_wishlist_brws_icon'];
                            $brwsicondashicons =  thwl_get_wishlist_icons_svg();
                            $th_wishlist_brws_icon_color = isset( $options['th_wishlist_brws_icon_color'] ) ? $options['th_wishlist_brws_icon_color'] : '#111';
                            ?>
                             <p><?php esc_html_e( 'Choose Browse to wishlist icon', 'th-wishlist' ); ?></p>
                             <div class="thw-dashicon-picker" id="thw-wishlist-icon">
                             <?php foreach ( $brwsicondashicons as $icon_key => $icon_data ) : ?>
                             <label class="thw-dashicon-option">
                                <input type="radio"
                                    name="settings[th_wishlist_brws_icon]"
                                    value="<?php echo esc_attr( $icon_key ); ?>"
                                    <?php checked( $selected_brws_icon, $icon_key ); ?> />
                                <span title="<?php echo esc_attr( $icon_data['name'] ); ?>">
                                    <?php
                                    echo wp_kses( $icon_data['svg'], $allowed_svg_tags );
                                    ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                            </div>
                            <div class="th-color-picker">
                            <p><?php esc_html_e( 'Browse Wishlist Icon color', 'th-wishlist' ); ?></p>
                            <input type="text" name="settings[th_wishlist_brws_icon_color]" id="thw-wishlist-brws-icon-color" value="<?php echo esc_attr( $th_wishlist_brws_icon_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_brws_icon_color ); ?>" />
                            </div>
                        </td>
                    </tr>
                    <?php 
                    $th_wishlist_btn_bg_color = isset( $options['th_wishlist_btn_bg_color'] ) ? $options['th_wishlist_btn_bg_color'] : '';
                    $th_wishlist_btn_brd_color = isset( $options['th_wishlist_btn_brd_color'] ) ? $options['th_wishlist_btn_brd_color'] : '';
                    $th_wishlist_btn_txt_color = isset( $options['th_wishlist_btn_txt_color'] ) ? $options['th_wishlist_btn_txt_color'] : '';
                    ?>

                    <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Button', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Backround color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_btn_bg_color]" id="thw-wishlist-btn-bg-color" value="<?php echo esc_attr( $th_wishlist_btn_bg_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_btn_bg_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Text color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_btn_txt_color]" id="thw-wishlist-btn-txt-color" value="<?php echo esc_attr( $th_wishlist_btn_txt_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_btn_txt_color ); ?>" />
                        </div> 
                        </div>
                        </td>
                     </tr>
                    
                    </table>
                    <?php 
                    $th_wishlist_tb_btn_txt_color = isset( $options['th_wishlist_tb_btn_txt_color'] ) ? $options['th_wishlist_tb_btn_txt_color'] : '';
                    $th_wishlist_tb_btn_bg_color = isset( $options['th_wishlist_tb_btn_bg_color'] ) ? $options['th_wishlist_tb_btn_bg_color'] : '';
                    ?>
                    <h3 class="thws-content-title"><?php esc_html_e( 'Wishlist Page', 'th-wishlist' ); ?></h3>
                    <table class="form-table">
                     <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Button Style', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Button Text color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_tb_btn_txt_color]" id="thw-wishlist-tb-btn-txt-color" value="<?php echo esc_attr(  $th_wishlist_tb_btn_txt_color  ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_tb_btn_txt_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Button Background color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_tb_btn_bg_color]" id="thw-wishlist-tb-btn-bg-color" value="<?php echo esc_attr( $th_wishlist_tb_btn_bg_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr($th_wishlist_tb_btn_bg_color); ?>" />
                        <div> 
                        </td>
                     </tr>
                     
                   </table>
  
                   <?php 
                    $th_wishlist_table_bg_color = isset( $options['th_wishlist_table_bg_color'] ) ? $options['th_wishlist_table_bg_color'] : '';
                    $th_wishlist_table_brd_color = isset( $options['th_wishlist_table_brd_color'] ) ? $options['th_wishlist_table_brd_color'] : '';
                    $th_wishlist_table_txt_color = isset( $options['th_wishlist_table_txt_color'] ) ? $options['th_wishlist_table_txt_color'] : '';
                    ?>
                   <table class="form-table">
                     <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Wishlist Table', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Backround color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_table_bg_color]" id="thw-wishlist-table-bg-color" value="<?php echo esc_attr( $th_wishlist_table_bg_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_table_bg_color ); ?>" />
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Border color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_table_brd_color]" id="thw-wishlist-table-brd-color" value="<?php echo esc_attr( $th_wishlist_table_brd_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_table_brd_color ); ?>" />
                        </div>
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Text color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_table_txt_color]" id="thw-wishlist-table-txt-color" value="<?php echo esc_attr( $th_wishlist_table_txt_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_table_txt_color ); ?>" />
                        </div> 
                        </td>
                     </tr>
                   </table>
                    <?php 
                    $th_wishlist_shr_fb_color = isset( $options['th_wishlist_shr_fb_color'] ) ? $options['th_wishlist_shr_fb_color'] : '';
                    $th_wishlist_shr_fb_hvr_color = isset( $options['th_wishlist_shr_fb_hvr_color'] ) ? $options['th_wishlist_shr_fb_hvr_color'] : '';
                    $th_wishlist_shr_x_color = isset( $options['th_wishlist_shr_x_color'] ) ? $options['th_wishlist_shr_x_color'] : '';
                    $th_wishlist_shr_x_hvr_color = isset( $options['th_wishlist_shr_x_hvr_color'] ) ? $options['th_wishlist_shr_x_hvr_color'] : '';

                    $th_wishlist_shr_w_color = isset( $options['th_wishlist_shr_w_color'] ) ? $options['th_wishlist_shr_w_color'] : '';
                    $th_wishlist_shr_w_hvr_color = isset( $options['th_wishlist_shr_w_hvr_color'] ) ? $options['th_wishlist_shr_w_hvr_color'] : '';

                    $th_wishlist_shr_c_color = isset( $options['th_wishlist_shr_c_color'] ) ? $options['th_wishlist_shr_c_color'] : '';
                    $th_wishlist_shr_c_hvr_color = isset( $options['th_wishlist_shr_c_hvr_color'] ) ? $options['th_wishlist_shr_c_hvr_color'] : '';

                    $th_wishlist_shr_e_color = isset( $options['th_wishlist_shr_e_color'] ) ? $options['th_wishlist_shr_e_color'] : '';
                    $th_wishlist_shr_e_hvr_color = isset( $options['th_wishlist_shr_e_hvr_color'] ) ? $options['th_wishlist_shr_e_hvr_color'] : '';
                    ?>


                   <h3 class="thws-content-title"><?php esc_html_e( 'Share Button', 'th-wishlist' ); ?></h3>
                   <table class="form-table">
                     <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Facebook', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_fb_color]" id="thw-wishlist-shr-fb-color" value="<?php echo esc_attr( $th_wishlist_shr_fb_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_fb_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Hover', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_fb_hvr_color]" id="thw-wishlist-shr-fb-color" value="<?php echo esc_attr( $th_wishlist_shr_fb_hvr_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_fb_hvr_color ); ?>" />
                        </div>
                        </td>
                     </tr>
                     <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Twitter', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_x_color]" id="thw-wishlist-shr-x-color" value="<?php echo esc_attr( $th_wishlist_shr_x_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_x_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Hover', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_x_hvr_color]" id="thw-wishlist-shr-x-color" value="<?php echo esc_attr( $th_wishlist_shr_x_hvr_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_x_hvr_color ); ?>" />
                        </div>
                        </td>
                     </tr>
                     <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Whatsapp', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_w_color]" id="thw-wishlist-shr-w-color" value="<?php echo esc_attr( $th_wishlist_shr_w_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_w_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Hover', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_w_hvr_color]" id="thw-wishlist-shr-w-color" value="<?php echo esc_attr( $th_wishlist_shr_w_hvr_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_w_hvr_color ); ?>" />
                        </div>
                        </td>
                     </tr>
                      <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Email', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_e_color]" id="thw-wishlist-shr-w-color" value="<?php echo esc_attr( $th_wishlist_shr_e_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_e_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Hover', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_e_hvr_color]" id="thw-wishlist-shr-w-color" value="<?php echo esc_attr( $th_wishlist_shr_e_hvr_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_e_hvr_color ); ?>" />
                        </div>
                        </td>
                     </tr>
                     <tr class="th-row-with-icon-radio">
                        <th scope="row"><?php esc_html_e( 'Copy Url', 'th-wishlist' ); ?></th>
                        <td class="th-row-flex">
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Color', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_c_color]" id="thw-wishlist-shr-c-color" value="<?php echo esc_attr( $th_wishlist_shr_c_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_c_color ); ?>" />
                        </div>
                        <div class="th-color-picker">
                        <p><?php esc_html_e( 'Hover', 'th-wishlist' ); ?></p>
                        <input type="text" name="settings[th_wishlist_shr_c_hvr_color]" id="thw-wishlist-shr-c-color" value="<?php echo esc_attr( $th_wishlist_shr_c_hvr_color ); ?>" class="th_color_picker" style="background-color: <?php echo esc_attr( $th_wishlist_shr_c_hvr_color ); ?>" />
                        </div>
                        </td>
                     </tr>
                   </table>
                </div>
              <?php do_action( 'thwl_after_pro_settings_fields' );?>
            </div>
            <p>
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'th-wishlist' ); ?></button>
                <button type="button" class="button" id="thw-reset-settings" data-nonce="<?php echo esc_attr( wp_create_nonce( 'thwl_wishlist_nonce' ) ); ?>"><?php esc_html_e( 'Reset to Defaults', 'th-wishlist' ); ?></button>
            </p>
        </form>
        <div class="thw-notes">
            <div class="thw-wrap-side">
                <h4 class="wrp-title"><?php esc_html_e( 'Documentation', 'th-wishlist' ); ?></h4>
                <p><?php esc_html_e( 'Want to know how this plugin works. Read our Documentation.', 'th-wishlist' ); ?></p>
                <a target="_blank" href="https://themehunk.com/docs/th-wishlist"><?php esc_html_e( 'View More', 'th-wishlist' ); ?></a>
            	</div>

                <div class="thw-wrap-side">
                <h4 class="wrp-title"><?php esc_html_e( 'Contact Support', 'th-wishlist' ); ?></h4>
                <p><?php esc_html_e( 'If you need any help you can contact to our support team', 'th-wishlist' ); ?></p>
                <a target="_blank" href="https://themehunk.com/contact-us/"><?php esc_html_e( 'Need Help ?', 'th-wishlist' ); ?></a>
            	</div>


                <div class="thw-wrap-side">
                <h4 class="wrp-title"><?php esc_html_e( 'Spread the News', 'th-wishlist' ); ?></h4>
                <p><?php esc_html_e( 'Enjoying this plugin? Help spread the the creation and show off your amazing website with such amazing functionality.', 'th-wishlist' ); ?></p>
                <a target="_blank" href="https://twitter.com/intent/tweet?url=https://themehunk.com/advance-product-search/&amp;text=Hey, I just tried out this amazing WordPress Plugin for http://localhost:8888/wp1 to add SearchBar. Show off your amazing website with such amazing functionality with this awesome plugin: TH Advance Product Search Pro By 
@ThemeHunk %20%23WooCommerce%20%23WordPress"></span>
        <span> <?php esc_html_e( 'Click to Tweet', 'th-wishlist' ); ?></span></a>
            	</div>
        </div>

    </div>

</div>
<?php }
    /**
     * Get default settings.
     *
     * @return array
     */
    public static function thwl_get_default_settings() {
        $defaults =  [
            'thwl_page_id' =>'',
            'thw_require_login'            => 0,
            'thw_redirect_to_cart'         => 0,
            'thw_button_display_style'     => 'icon_text',
            'thw_add_to_wishlist_text'     => __( 'Add to Wishlist', 'th-wishlist' ),
            'thw_browse_wishlist_text'     => __( 'Browse Wishlist', 'th-wishlist' ),
            'thw_btn_style_theme'          => 0,
            'thw_custom_icon_url'          => '',
            'thw_show_in_loop'             => 1,
            'thw_show_in_product'          => 1,
            'thw_in_loop_position'         => 'after_crt_btn',
            'thw_in_single_position'       => 'after_crt_btn',
            'thw_show_social_share'        => 0,
            'thw_show_quantity'            => 0,
            'th_wishlist_table_columns'       => [ 'thumbnail', 'name', 'price', 'stock', 'add_to_cart', 'remove' ],
            'th_wishlist_table_column_labels' => [],
            'thw_redirect_wishlist_page'      => 0,
            'thw_redirect_wishlist_page_icon' => 'heart-outline',
            'thw_redirect_wishlist_page_icon_color' => '#111',
            'thw_redirect_wishlist_page_icon_color_hvr' => '#111',
            'thw_redirect_wishlist_page_icon_size' => '24',
            'th_wishlist_add_icon'         => 'heart-outline',
            'th_wishlist_add_icon_color'   => '#111',
            'th_wishlist_brws_icon'        => 'heart-filled',
            'th_wishlist_brws_icon_color'  => '#111',
            'th_wishlist_btn_txt_color'    => '',
            'th_wishlist_btn_bg_color'     => '',
            'th_wishlist_table_bg_color'   => '',
            'th_wishlist_table_brd_color'  => '',
            'th_wishlist_table_txt_color'  => '',
            'th_wishlist_tb_btn_bg_color'  => '',
            'th_wishlist_tb_btn_txt_color' => '',
            'th_wishlist_shr_fb_color'     => '',
            'th_wishlist_shr_fb_hvr_color' => '',
            'th_wishlist_shr_x_color'      => '',
            'th_wishlist_shr_x_hvr_color'  => '',
            'th_wishlist_shr_w_color'      => '',
            'th_wishlist_shr_w_hvr_color'  => '',
            'th_wishlist_shr_e_color'      => '',
            'th_wishlist_shr_e_hvr_color'  => '',
            'th_wishlist_shr_c_color'      => '',
            'th_wishlist_shr_c_hvr_color'  => '', 
        ];
        return apply_filters( 'thwl_default_settings', $defaults );
    }
}