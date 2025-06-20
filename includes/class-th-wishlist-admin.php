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
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
    }
    
    /**
     * Add admin menu page and sub-menu page for tracking.
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

        add_submenu_page(
            'thw-wishlist',
            __( 'Wishlists Tracking', 'th-wishlist' ),
            __( 'Wishlists', 'th-wishlist' ),
            'manage_options',
            'thw-wishlists-tracking',
            array( $this, 'tracking_page' )
        );
    }

    /**
     * Render the wishlists tracking page with the list table.
     */
    public function tracking_page() {
        // The list table class should be included before it's instantiated.
        require_once THW_DIR . 'includes/class-th-wishlist-list-table.php';

        $wishlist_table = new TH_Wishlist_List_Table();
        $wishlist_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Wishlists', 'th-wishlist'); ?></h1>
            <form method="post">
                <?php
                // Display search box, filters, and the table
                $wishlist_table->search_box('Search Wishlists', 'wishlist');
                $wishlist_table->display();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register all plugin settings.
     */
    public function register_settings() {
        $settings = [
            'th_wcwl_wishlist_page_id', 'thw_require_login', 'thw_redirect_to_cart',
            'thw_button_display_style', 'thw_add_to_wishlist_text', 'thw_browse_wishlist_text',
            'thw_use_custom_icon', 'thw_custom_icon_url', 'thw_show_add_all_to_cart', 
            'thw_show_social_share', 'thw_show_quantity', 'thw_wishlist_table_columns'
        ];
        foreach( $settings as $setting ) {
            register_setting( 'thw_settings_group', $setting );
        }
    }

    /**
     * Render the main settings page.
     */
    public function settings_page() {
        $saved_columns = get_option('thw_wishlist_table_columns', ['thumbnail', 'name', 'price', 'stock', 'add_to_cart', 'remove']);
        $all_columns = ['checkbox' => 'Checkbox', 'thumbnail' => 'Image', 'name' => 'Name & Variation', 'price' => 'Price', 'stock' => 'Stock Status', 'quantity' => 'Quantity', 'add_to_cart' => 'Add to Cart', 'date' => 'Date Added', 'remove' => 'Remove'];
        ?>
        <div class="wrap">
            <h1><?php _e( 'TH Wishlist Settings', 'th-wishlist' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'thw_settings_group' ); ?>
                
                <h2><?php _e('General Settings', 'th-wishlist'); ?></h2>
                <table class="form-table">
                    <tr><th scope="row"><?php _e('Wishlist Page', 'th-wishlist'); ?></th><td><?php wp_dropdown_pages(['name' => 'th_wcwl_wishlist_page_id', 'selected' => get_option('th_wcwl_wishlist_page_id'), 'show_option_none' => __('Select a page', 'th-wishlist')]); ?><p class="description"><?php _e('The page where the `[th_wcwl_wishlist]` shortcode is located.', 'th-wishlist'); ?></p></td></tr>
                    <tr><th scope="row"><?php _e('Require Login', 'th-wishlist'); ?></th><td><input type="checkbox" name="thw_require_login" value="1" <?php checked(1, get_option('thw_require_login'), true); ?> /> <span class="description"><?php _e('Only logged-in users can add products to the wishlist.', 'th-wishlist'); ?></span></td></tr>
                </table>

                <h2><?php _e('"Add to Wishlist" Button Settings', 'th-wishlist'); ?></h2>
                <table class="form-table">
                    <tr><th scope="row"><?php _e('Button Style', 'th-wishlist'); ?></th><td><select name="thw_button_display_style"><option value="icon_text" <?php selected('icon_text', get_option('thw_button_display_style')); ?>>Icon and Text</option><option value="icon" <?php selected('icon', get_option('thw_button_display_style')); ?>>Icon Only (with Button Style)</option><option value="icon_only_no_style" <?php selected('icon_only_no_style', get_option('thw_button_display_style')); ?>>Icon Only (No Button Style)</option><option value="text" <?php selected('text', get_option('thw_button_display_style')); ?>>Text Only</option></select></td></tr>
                    <tr><th scope="row"><?php _e('"Add to Wishlist" Text', 'th-wishlist'); ?></th><td><input type="text" name="thw_add_to_wishlist_text" value="<?php echo esc_attr(get_option('thw_add_to_wishlist_text', 'Add to Wishlist')); ?>" class="regular-text" /></td></tr>
                    <tr><th scope="row"><?php _e('"Browse Wishlist" Text', 'th-wishlist'); ?></th><td><input type="text" name="thw_browse_wishlist_text" value="<?php echo esc_attr(get_option('thw_browse_wishlist_text', 'Browse Wishlist')); ?>" class="regular-text" /><p class="description"><?php _e('Text shown when the product is already in the wishlist.', 'th-wishlist'); ?></p></td></tr>
                    <tr><th scope="row"><?php _e('Use Custom Icon', 'th-wishlist'); ?></th><td><input type="checkbox" id="thw_use_custom_icon" name="thw_use_custom_icon" value="1" <?php checked(1, get_option('thw_use_custom_icon'), true); ?> /> <span class="description"><?php _e('Use a custom uploaded icon instead of the default heart.', 'th-wishlist'); ?></span></td></tr>
                    <tr class="thw-custom-icon-row"><th scope="row"><?php _e('Custom Wishlist Icon', 'th-wishlist'); ?></th><td><input type="text" name="thw_custom_icon_url" id="thw_custom_icon_url" value="<?php echo esc_attr(get_option('thw_custom_icon_url')); ?>" class="regular-text" /> <button type="button" class="button" id="thw_upload_icon_button"><?php _e('Upload Icon', 'th-wishlist'); ?></button></td></tr>
                </table>

                <h2><?php _e('Wishlist Page Settings', 'th-wishlist'); ?></h2>
                <table class="form-table">
                    <tr><th scope="row"><?php _e('Redirect to Cart', 'th-wishlist'); ?></th><td><input type="checkbox" name="thw_redirect_to_cart" value="1" <?php checked(1, get_option('thw_redirect_to_cart'), true); ?> /> <span class="description"><?php _e('Redirect to the cart page after adding item(s) from the wishlist.', 'th-wishlist'); ?></span></td></tr>
                    <tr><th scope="row"><?php _e('Show "Add all to cart"', 'th-wishlist'); ?></th><td><input type="checkbox" name="thw_show_add_all_to_cart" value="1" <?php checked(1, get_option('thw_show_add_all_to_cart'), true); ?> /> <span class="description"><?php _e('Requires the "Checkbox" column to be enabled.', 'th-wishlist'); ?></span></td></tr>
                    <tr><th scope="row"><?php _e('Show Social Share Buttons', 'th-wishlist'); ?></th><td><input type="checkbox" name="thw_show_social_share" value="1" <?php checked(1, get_option('thw_show_social_share'), true); ?> /> <span class="description"><?php _e('Allows logged-in users to share their wishlist.', 'th-wishlist'); ?></span></td></tr>
                    <tr><th scope="row"><?php _e('Show Quantity Field', 'th-wishlist'); ?></th><td><input type="checkbox" name="thw_show_quantity" value="1" <?php checked(1, get_option('thw_show_quantity'), true); ?> /> <span class="description"><?php _e('Allows users to manage item quantity directly in the wishlist.', 'th-wishlist'); ?></span></td></tr>
                    <tr><th scope="row"><?php _e('Wishlist Table Columns', 'th-wishlist'); ?></th>
                        <td>
                            <p class="description"><?php _e('Check the columns to display and drag to reorder.', 'th-wishlist'); ?></p>
                            <ul id="thw-sortable-columns">
                                <?php
                                // Display saved columns first, in order
                                foreach ($saved_columns as $key) {
                                    if(isset($all_columns[$key])) {
                                        echo '<li><input type="hidden" name="thw_wishlist_table_columns[]" value="'.$key.'"><span class="dashicons dashicons-menu"></span> '.$all_columns[$key].'</li>';
                                    }
                                }
                                // Display remaining available columns
                                foreach ($all_columns as $key => $label) {
                                    if (!in_array($key, $saved_columns)) {
                                        echo '<li class="disabled"><input type="hidden" name="thw_wishlist_table_columns[]" value="'.$key.'" disabled><span class="dashicons dashicons-menu"></span> '.$label.'</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <style>
            #thw-sortable-columns { list-style: none; margin: 0; padding: 0; width: 250px; }
            #thw-sortable-columns li { margin: 5px 0; padding: 10px; border: 1px solid #ccc; background: #fff; cursor: move; }
            #thw-sortable-columns li.disabled { background: #f1f1f1; cursor: default; opacity: 0.6; }
        </style>
        <script>
            jQuery(document).ready(function($){
                // Media uploader for custom icon
                $('#thw_upload_icon_button').click(function(e) {
                    e.preventDefault();
                    var frame = wp.media({ title: 'Upload Icon', multiple: false });
                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#thw_custom_icon_url').val(attachment.url);
                    });
                    frame.open();
                });

                // Toggle custom icon URL field visibility
                function toggleCustomIcon(){
                    $('.thw-custom-icon-row').toggle($('#thw_use_custom_icon').is(':checked'));
                }
                toggleCustomIcon();
                $('#thw_use_custom_icon').on('change', toggleCustomIcon);

                // Make columns sortable
                $('#thw-sortable-columns').sortable({
                    axis: 'y',
                    opacity: 0.7,
                    placeholder: 'ui-state-highlight'
                });
            });
        </script>
        <?php
    }
}
