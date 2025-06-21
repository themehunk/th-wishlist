<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend-facing functions and hooks for TH Wishlist.
 *
 * @class TH_Wishlist_Frontend
 */
class TH_Wishlist_Frontend {
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_to_wishlist_button' ), 20 );
        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_to_wishlist_button' ), 20 );

        add_shortcode( 'th_wcwl_wishlist', array( $this, 'wishlist_page_shortcode' ) );

        // AJAX handlers
        add_action( 'wp_ajax_thw_add_to_wishlist', array( $this, 'add_to_wishlist_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_add_to_wishlist', array( $this, 'add_to_wishlist_ajax' ) );
        add_action( 'wp_ajax_thw_remove_from_wishlist', array( $this, 'remove_from_wishlist_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_remove_from_wishlist', array( $this, 'remove_from_wishlist_ajax' ) );
        add_action( 'wp_ajax_thw_update_item_quantity', array( $this, 'update_item_quantity_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_update_item_quantity', array( $this, 'update_item_quantity_ajax' ) );
        add_action( 'wp_ajax_thw_add_all_to_cart', array( $this, 'add_all_to_cart_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_add_all_to_cart', array( $this, 'add_all_to_cart_ajax' ) );
    }

    public function enqueue_styles_scripts() {
        wp_enqueue_style( 'thw-wishlist', THW_URL . 'assets/css/wishlist.css', array(), THW_VERSION );
        wp_enqueue_script( 'thw-wishlist', THW_URL . 'assets/js/wishlist.js', array( 'jquery' ), THW_VERSION, true );
        
        $wishlist_page_id = get_option( 'th_wcwl_wishlist_page_id' );
        
        wp_localize_script( 'thw-wishlist', 'thw_wishlist_params', array(
            'ajax_url'            => admin_url( 'admin-ajax.php' ),
            'add_nonce'           => wp_create_nonce( 'thw-add-nonce' ),
            'remove_nonce'        => wp_create_nonce( 'thw-remove-nonce' ),
            'update_qty_nonce'    => wp_create_nonce( 'thw-update-qty-nonce' ),
            'add_all_nonce'       => wp_create_nonce( 'thw-add-all-nonce' ),
            'wishlist_page_url'   => $wishlist_page_id ? get_permalink( $wishlist_page_id ) : '',
            'i18n_added'          => get_option('thw_browse_wishlist_text', __( 'Browse Wishlist', 'th-wishlist' )),
            'i18n_error'          => __( 'An error occurred. Please try again.', 'th-wishlist' ),
            'i18n_empty_wishlist' => __('Your wishlist is currently empty.', 'th-wishlist'),
            'redirect_to_cart'    => get_option('thw_redirect_to_cart') === '1',
            'cart_url'            => wc_get_cart_url(),
        ) );
    }

    public function add_to_wishlist_button() {
        global $product;
        if (get_option('thw_require_login') === '1' && !is_user_logged_in()) {
            echo '<a href="' . esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) . '" class="button thw-login-required">' . __('Login to add to wishlist', 'th-wishlist') . '</a>';
            return;
        }

        $wishlist = TH_Wishlist_Data::get_or_create_wishlist();
        $product_id = $product->get_id();
        $variation_id = $product->is_type('variation') ? $product->get_id() : 0;
        
        $in_wishlist = $wishlist ? TH_Wishlist_Data::is_product_in_wishlist( $wishlist->id, $product_id, $variation_id ) : false;
        
        $add_text = get_option('thw_add_to_wishlist_text', __( 'Add to Wishlist', 'th-wishlist' ));
        $browse_text = get_option('thw_browse_wishlist_text', __( 'Browse Wishlist', 'th-wishlist' ));
        $text = $in_wishlist ? $browse_text : $add_text;
        
        $class = $in_wishlist ? 'in-wishlist' : '';
        
        $display_style = get_option('thw_button_display_style', 'icon_text');
        if($display_style === 'icon_only_no_style') {
            $class .= ' no-style';
        }

        $icon_html = '';
        $text_html = '<span>' . esc_html( $text ) . '</span>';
        
        if ($display_style === 'icon' || $display_style === 'icon_text' || $display_style === 'icon_only_no_style') {
            if (get_option('thw_use_custom_icon') === '1' && get_option('thw_custom_icon_url')) {
                $icon_html = '<img src="'.esc_url(get_option('thw_custom_icon_url')).'" class="thw-icon" alt="Wishlist Icon" />';
            } else {
                 $icon_html = '<span class="thw-icon">&hearts;</span>';
            }
        }
        if($display_style === 'icon' || $display_style === 'icon_only_no_style') $text_html = '';
        if($display_style === 'text') $icon_html = '';

        echo '<button class="thw-add-to-wishlist-button button ' . $class . '" data-product-id="' . esc_attr( $product_id ) . '" data-variation-id="' . esc_attr( $variation_id ) . '">' . $icon_html . $text_html . '</button>';
    }

    public function wishlist_page_shortcode() {
        ob_start();

        $wishlist = null;
        $wishlist_token = isset($_GET['wishlist_token']) ? sanitize_text_field($_GET['wishlist_token']) : null;

        if ($wishlist_token) {
            $shared_wishlist = TH_Wishlist_Data::get_wishlist_by_token($wishlist_token);

            if ($shared_wishlist) {
                // Wishlist found by token. Now check privacy.
                $is_owner = ( is_user_logged_in() && $shared_wishlist->user_id == get_current_user_id() );

                // Allow viewing if wishlist is not private, or if the viewer is the owner or an admin.
                if ( $shared_wishlist->privacy !== 'private' || $is_owner || current_user_can('manage_options') ) {
                    $wishlist = $shared_wishlist;
                } else {
                    echo '<p>' . __('This wishlist is private and cannot be viewed.', 'th-wishlist') . '</p>';
                    return ob_get_clean();
                }
            } else {
                echo '<p>' . __('The wishlist you are trying to view does not exist or has been deleted.', 'th-wishlist') . '</p>';
                return ob_get_clean();
            }

        } else {
            // No token, get the current user's wishlist.
            $wishlist = TH_Wishlist_Data::get_or_create_wishlist();
        }

        if(!$wishlist) {
            echo '<p>' . __('Could not retrieve your wishlist.', 'th-wishlist') . '</p>';
            return ob_get_clean();
        }

        $items = TH_Wishlist_Data::get_wishlist_items($wishlist->id);
        $columns = get_option('thw_wishlist_table_columns', ['thumbnail', 'name', 'price', 'stock', 'add_to_cart', 'remove']);

        echo '<div class="thw-wishlist-wrapper">';
        echo '<h2>' . esc_html($wishlist->wishlist_name) . '</h2>';
        echo '<form class="thw-wishlist-form">';
        echo '<table class="thw-wishlist-table"><thead><tr>';
        
        $headers = [
            'checkbox' => '<input type="checkbox" id="thw-select-all" />',
            'thumbnail' => '&nbsp;',
            'name' => __('Product', 'th-wishlist'),
            'price' => __('Price', 'th-wishlist'),
            'stock' => __('Stock Status', 'th-wishlist'),
            'quantity' => __('Quantity', 'th-wishlist'),
            'add_to_cart' => '&nbsp;',
            'date' => __('Date Added', 'th-wishlist'),
            'remove' => '&nbsp;',
        ];
        foreach($columns as $key) {
            if (isset($headers[$key])) echo '<th class="product-'.$key.'">'.$headers[$key].'</th>';
        }

        echo '</tr></thead><tbody>';

        if (!empty($items)) {
            foreach ($items as $item) {
                $_product = wc_get_product($item->variation_id ? $item->variation_id : $item->product_id);
                if (!$_product) continue;
                echo '<tr data-item-id="' . esc_attr($item->id) . '" data-product-id="' . esc_attr($_product->get_id()) . '">';
                
                foreach($columns as $key) {
                    echo '<td class="product-'.$key.'">';
                    switch($key) {
                        case 'checkbox':
                            echo '<input type="checkbox" name="wishlist_items[]" value="'.esc_attr($item->id).'">';
                            break;
                        case 'thumbnail':
                            echo '<a href="'.esc_url($_product->get_permalink()).'">'.$_product->get_image().'</a>';
                            break;
                        case 'name':
                            echo '<a href="'.esc_url($_product->get_permalink()).'">'.$_product->get_name().'</a>';
                            if($_product->is_type('variation')) {
                                echo wc_get_formatted_variation($_product, true);
                            }
                            break;
                        case 'price':
                            echo $_product->get_price_html();
                            break;
                        case 'stock':
                            echo $_product->get_stock_status() === 'instock' ? '<span class="stock in-stock">' . __('In Stock', 'th-wishlist') . '</span>' : '<span class="stock out-of-stock">' . __('Out of Stock', 'th-wishlist') . '</span>';
                            break;
                        case 'quantity':
                             if(in_array('quantity', $columns)) {
                                echo '<input type="number" class="thw-qty" value="'.esc_attr($item->quantity).'" min="1" step="1" data-item-id="'.esc_attr($item->id).'">';
                             }
                            break;
                        case 'add_to_cart':
                             echo '<div class="thw-add-to-cart-cell">';
                             woocommerce_template_loop_add_to_cart(['product' => $_product]);
                             echo '</div>';
                            break;
                        case 'date':
                            echo '<span>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->added_at ) ) ) . '</span>';
                            break;
                        case 'remove':
                            echo '<a href="#" class="thw-remove-item" title="'.__('Remove this product', 'th-wishlist').'">&times;</a>';
                            break;
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="' . count($columns) . '">' . __('Your wishlist is currently empty.', 'th-wishlist') . '</td></tr>';
        }

        echo '</tbody></table></form>';

        echo '<div class="thw-wishlist-actions">';
        if (get_option('thw_show_add_all_to_cart') === '1' && in_array('checkbox', $columns) && !empty($items)) {
            echo '<button class="button thw-add-all-to-cart">' . __('Add Selected to Cart', 'th-wishlist') . '</button>';
        }

        if (get_option('thw_show_social_share') === '1' && $wishlist->privacy !== 'private' && !empty($wishlist->wishlist_token)) {
            $share_url = add_query_arg('wishlist_token', $wishlist->wishlist_token, get_permalink(get_option('th_wcwl_wishlist_page_id')));
            echo '<div class="thw-social-share">';
            echo '<span>' . __('Share on:', 'th-wishlist') . '</span>';
            echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($share_url) . '" target="_blank" title="Facebook">FB</a>';
            echo '<a href="https://twitter.com/intent/tweet?url=' . urlencode($share_url) . '&text=' . urlencode('My Wishlist') . '" target="_blank" title="Twitter">TW</a>';
            echo '<a href="#" class="thw-copy-link-button" data-link="'.esc_attr($share_url).'" title="'.__('Copy Link', 'th-wishlist').'">Copy</a>';
            echo '</div>';
        }
        echo '</div>'; // .thw-wishlist-actions
        echo '</div>'; // .thw-wishlist-wrapper
        
        return ob_get_clean();
    }
    
    // AJAX Handlers
    public function add_to_wishlist_ajax() {
        if (get_option('thw_require_login') === '1' && !is_user_logged_in()) {
            wp_send_json_error(['message' => 'login_required']);
            return;
        }
        check_ajax_referer('thw-add-nonce', 'nonce');
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $wishlist = TH_Wishlist_Data::get_or_create_wishlist();
        if ($wishlist && TH_Wishlist_Data::add_item($wishlist->id, $product_id)) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function remove_from_wishlist_ajax() {
        check_ajax_referer('thw-remove-nonce', 'nonce');
        $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        TH_Wishlist_Data::remove_item($item_id);
        wp_send_json_success();
    }
    
    public function update_item_quantity_ajax() {
        check_ajax_referer('thw-update-qty-nonce', 'nonce');
        $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
        TH_Wishlist_Data::update_item_quantity($item_id, $quantity);
        wp_send_json_success();
    }
    
    public function add_all_to_cart_ajax() {
        check_ajax_referer('thw-add-all-nonce', 'nonce');
        $item_ids = isset($_POST['items']) ? array_map('absint', $_POST['items']) : [];
        foreach($item_ids as $item_id) {
            $item = TH_Wishlist_Data::get_item($item_id);
            if($item) {
                WC()->cart->add_to_cart($item->product_id, $item->quantity, $item->variation_id);
            }
        }
        wp_send_json_success(['message' => 'Products added to cart.']);
    }
}
