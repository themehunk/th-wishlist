<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend-facing functions and hooks for TH Wishlist.
 *
 * @class THWL_Frontend
 */
class THWL_Frontend {

    // Declare the property to avoid dynamic property deprecation warning
    private $thwl_option;

    public function __construct() {

        // Use static method directly, no need to instantiate
        $this->thwl_option = get_option( 'thwl_settings', THWL_Settings::thwl_get_default_settings() );

        add_action('wp_enqueue_scripts', array( $this, 'thwl_enqueue_styles_scripts' ) );
        add_shortcode('thwl_wishlist', array( $this, 'thwl_wishlist_page_shortcode' ) );
        add_shortcode('thwl_wishlist_button', array( $this,'thwl_add_to_wishlist_button_shortcode'));
        
        //flexible shortcode
        add_shortcode( 'thwl_add_to_wishlist', array( $this, 'thwl_add_to_wishlist_button_flexible_shortcode') );
        add_action( 'wp', array( $this, 'thwl_hook_wishlist_loop_button_position' ) );
        add_action( 'wp', array( $this, 'thwl_hook_wishlist_single_button_position' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_thwl_add_to_wishlist', array( $this, 'thwl_add_to_wishlist_ajax' ) );
        add_action( 'wp_ajax_nopriv_thwl_add_to_wishlist', array( $this, 'thwl_add_to_wishlist_ajax' ) );
        add_action( 'wp_ajax_thwl_remove_from_wishlist', array( $this, 'thwl_remove_from_wishlist_ajax' ) );
        add_action( 'wp_ajax_nopriv_thwl_remove_from_wishlist', array( $this, 'thwl_remove_from_wishlist_ajax' ) );
        add_action( 'wp_ajax_thwl_update_item_quantity', array( $this, 'thwl_update_item_quantity_ajax' ) );
        add_action( 'wp_ajax_nopriv_thwl_update_item_quantity', array( $this, 'thwl_update_item_quantity_ajax' ) );
        add_action( 'wp_ajax_thwl_add_all_to_cart', array( $this, 'thwl_add_all_to_cart_ajax' ) );
        add_action( 'wp_ajax_nopriv_thwl_add_all_to_cart', array( $this, 'thwl_add_all_to_cart_ajax' ) );
        add_action( 'wp_ajax_thwl_add_to_cart_and_manage', array( $this, 'thwl_add_to_cart_and_manage'));
        add_action( 'wp_ajax_nopriv_thwl_add_to_cart_and_manage', array( $this, 'thwl_add_to_cart_and_manage'));
    }

    public function thwl_enqueue_styles_scripts() {
        
        wp_enqueue_style('thwl', THWL_URL . 'assets/css/wishlist.css', array(),THWL_VERSION);
        wp_register_script( 'thwl', THWL_URL . 'assets/js/wishlist.js', array( 'jquery' ),THWL_VERSION, array( 
                'strategy'  => 'async',
                'in_footer' => false,
        ) );
        wp_enqueue_script( 'thwl' );
        wp_add_inline_style('thwl',thwl_front_style());
        $wishlist_page_id = isset($this->thwl_option['thwl_page_id']) ? $this->thwl_option['thwl_page_id'] : 0;
        $thw_redirect_to_cart = isset($this->thwl_option['thw_redirect_to_cart']) ? $this->thwl_option['thw_redirect_to_cart'] : '';
        wp_localize_script( 'thwl', 'thwl_wishlist_params', array(
            'ajax_url'            => admin_url( 'admin-ajax.php' ),
            'add_nonce'           => wp_create_nonce( 'thwl-add-nonce' ),
            'remove_nonce'        => wp_create_nonce( 'thwl-remove-nonce' ),
            'update_qty_nonce'    => wp_create_nonce( 'thwl-update-qty-nonce' ),
            'add_all_nonce'       => wp_create_nonce( 'thwl-add-all-nonce' ),
            'wishlist_page_url'   => $wishlist_page_id ? get_permalink( $wishlist_page_id ) : '',
            'i18n_added'          => isset($this->thwl_option['thw_browse_wishlist_text']) ? $this->thwl_option['thw_browse_wishlist_text'] : __('Browse Wishlist', 'th-wishlist'),
            'i18n_error'          => __('An error occurred. Please try again.', 'th-wishlist' ),
            'i18n_empty_wishlist' => __('Your wishlist is currently empty.', 'th-wishlist'),
            'redirect_to_cart'    => $thw_redirect_to_cart === '1',
            'cart_url'            => wc_get_cart_url(),
            'icon_style'          => isset($this->thwl_option['thw_button_display_style']) ? $this->thwl_option['thw_button_display_style'] : 'icon_text',
            'redirect_nonce'      => wp_create_nonce('thwl_wishlist_redirect_nonce'),
            'th_wishlist_brws_icon' => $this->thwl_option['th_wishlist_brws_icon'],
            'icons' => thwl_get_wishlist_icons_svg(),
            ) );
    }

    public function thwl_add_to_wishlist_button_shortcode() {
   
    global $product;

    if (!isset($product) || !is_a($product, 'WC_Product')) {
        return '';
    }

    $output = '';
    $wishlist = THWL_Data::get_or_create_wishlist();
    $product_id = $product->get_id();
    $variation_id = $product->is_type('variation') ? $product->get_id() : 0;
    $in_wishlist = $wishlist ? THWL_Data::is_product_in_wishlist($wishlist->id, $product_id, $variation_id) : false;

    // Handle login requirement
    if (isset($this->thwl_option['thw_require_login']) && '1' === $this->thwl_option['thw_require_login'] && !is_user_logged_in()) {
        $myaccount_page_id = get_option('woocommerce_myaccount_page_id');
        $myaccount_url = $myaccount_page_id ? esc_url(get_permalink($myaccount_page_id)) : wp_login_url();
        $output .= sprintf(
            '<div class="thw-add-to-wishlist-button-wrap"><a href="%s" class="thw-login-required">%s</a></div>',
            $myaccount_url,
            esc_html__('Login to add to wishlist', 'th-wishlist')
        );
        return $output;
    }

    // Text settings
    $add_text = !empty($this->thwl_option['thw_add_to_wishlist_text']) 
        ? $this->thwl_option['thw_add_to_wishlist_text'] 
        : esc_html__('Add to Wishlist', 'th-wishlist');
    $browse_text = !empty($this->thwl_option['thw_browse_wishlist_text']) 
        ? $this->thwl_option['thw_browse_wishlist_text'] 
        : esc_html__('Browse Wishlist', 'th-wishlist');
    $text = $in_wishlist ? $browse_text : $add_text;

    // Classes array
    $classes = $in_wishlist ? ['in-wishlist'] : [];
    $display_style = !empty($this->thwl_option['thw_button_display_style']) 
        ? $this->thwl_option['thw_button_display_style'] 
        : 'icon_text';

    // Button classes based on display style
    $btnclasses = '';
    switch ($display_style) {
        case 'icon_only_no_style':
            $classes[] = 'no-style';
            break;
        case 'icon_text':
            $classes[] = 'th-icon-text';
            break;
        case 'icon':
            $classes[] = 'th-icon';
            $btnclasses = 'th-button';
            break;
        case 'text':
            $classes[] = 'th-text';
            break;
    }

    $class_attr = implode(' ', array_filter($classes));
    $icon_html = '';
    $text_html = sprintf('<span>%s</span>', esc_html($text));


    // Handle icons
    $icons = thwl_get_wishlist_icons_svg();
    if ($in_wishlist) {
        $th_wishlist_brws_icon = !empty($this->thwl_option['th_wishlist_brws_icon']) 
            ? $this->thwl_option['th_wishlist_brws_icon'] 
            : 'heart-filled';
        $selected_brwsicon = isset($icons[$th_wishlist_brws_icon]) ? $th_wishlist_brws_icon : 'heart-filled';
        if (in_array($display_style, ['icon', 'icon_text', 'icon_only_no_style'])) {
            $icon_html = sprintf('<span class="thw-icon browse">%s</span>', $icons[$selected_brwsicon]['svg']);
        }
    } else {
        
        $th_wishlist_add_icon = !empty($this->thwl_option['th_wishlist_add_icon']) 
            ? $this->thwl_option['th_wishlist_add_icon'] 
            : 'heart-outline';
        $selected_addicon = isset($icons[$th_wishlist_add_icon]) ? $th_wishlist_add_icon : 'heart-outline';
        if (in_array($display_style, ['icon', 'icon_text', 'icon_only_no_style'])) {
            $icon_html = sprintf('<span class="thw-icon add">%s</span>', $icons[$selected_addicon]['svg']);
        }
    }

    // Adjust content based on display style
    if (in_array($display_style, ['icon', 'icon_only_no_style'])) {
        $text_html = '';
    } elseif ($display_style === 'text') {
        $icon_html = '';
    }

    // Wrapper classes
    $wrap_class = '';
    if (is_singular('product') && get_queried_object_id() == $product->get_id()) {
    $wrap_class = 'th-wishlist-single';
    }
    $themedefault = !empty($this->thwl_option['thw_btn_style_theme']) && '1' === $this->thwl_option['thw_btn_style_theme'] 
        ? 'thw-btn-theme-style' 
        : 'thw-btn-custom-style';

    // Build output
    $output .= sprintf(
        '<div class="thw-add-to-wishlist-button-wrap %s %s">',
        esc_attr($wrap_class),
        esc_attr($themedefault)
    );

    if ('icon' === $display_style) {
        $output .= sprintf(
            '<button class="thw-add-to-wishlist-button %s %s" data-product-id="%s" data-variation-id="%s">%s%s</button>',
            esc_attr($btnclasses),
            esc_attr($class_attr),
            esc_attr($product_id),
            esc_attr($variation_id),
            $icon_html,
            $text_html
        );
    } else {
        $output .= sprintf(
            '<a class="thw-add-to-wishlist-button %s %s" data-product-id="%s" data-variation-id="%s">%s%s</a>',
            esc_attr($btnclasses),
            esc_attr($class_attr),
            esc_attr($product_id),
            esc_attr($variation_id),
            $icon_html,
            $text_html
        );
    }

    $output .= '</div>';

    return $output;

    }

   public function add_to_wishlist_button(){

   echo do_shortcode('[thwl_wishlist_button]');

   }

   public function thwl_hook_wishlist_loop_button_position() {

    $thw_show_in_loop = isset( $this->thwl_option['thw_show_in_loop'] ) ? $this->thwl_option['thw_show_in_loop'] : 1;

    if ( ! $thw_show_in_loop ) {

		return;
	}

    $position = isset( $this->thwl_option['thw_in_loop_position'] ) ? $this->thwl_option['thw_in_loop_position'] : 'after_crt_btn';

    switch ( $position ) {

            case 'before_crt_btn':
               
                if ( thwl_is_wc_block_template( 'archive-product' ) ) {
                    // For blockified loop: hook before Add to Cart (product-button)
                    add_filter( 'render_block_woocommerce/product-button', array( $this, 'inject_wishlist_in_block' ), 5, 3 );
                } else {
                    // Classic template: hook before Add to Cart in loop
                    add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_to_wishlist_button' ), 7 );
                }
                break;

            case 'after_crt_btn':
                if ( thwl_is_wc_block_template( 'archive-product' ) ) {
                    add_filter( 'render_block_woocommerce/product-button', array( $this, 'inject_wishlist_in_block' ),20, 3  );
                } else {
                    add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_to_wishlist_button' ), 15 );
                }
                break;

            case 'on_top':
                if ( thwl_is_wc_block_template( 'archive-product' ) ) {
                    add_filter( 'render_block_woocommerce/product-image', array( $this, 'inject_wishlist_in_block' ), 10, 3 );
                } else {
                    add_action( 'woocommerce_before_shop_loop_item', array( $this, 'add_to_wishlist_button' ), 5 );
                }
                break;

            case 'on_shortcode':
                // Do not hook automatically
                break;
        }

  }

  public function thwl_hook_wishlist_single_button_position() {

    $thw_show_in_product = isset( $this->thwl_option['thw_show_in_product'] ) ? $this->thwl_option['thw_show_in_product'] : '';
    $position = isset( $this->thwl_option['thw_in_single_position'] ) ? $this->thwl_option['thw_in_single_position'] : 'after_crt_btn';

    if ( ! is_singular( 'product' )) {
        return; 
    }

    if( $thw_show_in_product == '0' ){
        return; 
    }

    if ( thwl_is_wc_block_template( 'single-product' ) ) {
        
       $this->add_button_for_blockified_template('single-product', $position);
       
    }else{

        switch ( $position ) {
        case 'before_summ':
            // Hook before "Add to Cart" by using before item end
            add_action( 'woocommerce_before_single_product_summary', array( $this, 'add_to_wishlist_button' ), 21 );
            break;

        case 'after_crt_btn':
            add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_to_wishlist_button' ), 1 );
            break;

        case 'after_summ':
            add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_to_wishlist_button' ), 11 );
            break;

        case 'on_shortcode':
            // Do not hook automatically
            break;
       }

    }

  }

  /**
 * Inject wishlist button into blockified WooCommerce templates.
 *
 * @param string $template Template slug (e.g., 'single-product').
 * @param string $position Insertion position (e.g., 'after_crt_btn', 'after_thumb', 'after_summ').
 */
    public function add_button_for_blockified_template( $template, $position ) {
        
        $hooked = false;

        switch ( $position ) {
            case 'after_crt_btn':
                //$block = ( 'single-product' === $template ) ? 'add-to-cart-form' : 'product-button';
                add_filter( "render_block_woocommerce/add-to-cart-form", array( $this, 'inject_wishlist_in_block' ), 10, 3 );
                $hooked = true;
                break;

            case 'before_summ':
                add_filter( 'render_block_woocommerce/product-image-gallery', array( $this, 'inject_wishlist_in_block' ), 10, 3 );
                $hooked = true;
                break;

            case 'after_summ':
                add_filter( 'render_block_woocommerce/product-details', array( $this, 'inject_wishlist_in_block' ), 10, 3 );
                $hooked = true;
                break;
        }

        if ( $hooked ) {
            do_action( 'thwl_wishlist_blockified_hook_attached', $template, $position );
        }
    }

    /**
     * Appends the wishlist button HTML to a WooCommerce block's output.
     *
     * @param string $block_content The original block HTML.
     * @param array  $block The full parsed block array.
     * @param WP_Block $instance The block instance.
     * @return string Modified block content.
     */
    public function inject_wishlist_in_block( $block_content, $block, $instance ) {
        ob_start();
        $this->add_to_wishlist_button();
        $wishlist_button_html = ob_get_clean();
        // Append after original content. You could also prepend or place conditionally.
        return $block_content . $wishlist_button_html;
    }

    public function thwl_wishlist_page_shortcode() {
        
    global $product;
    $output = '';
    $wishlist = null;
    $wishlist_token = isset( $_GET['wishlist_token'] ) ? sanitize_text_field( wp_unslash( $_GET['wishlist_token'] ) ) : null;
    $nonce = isset( $_GET['wishlist_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['wishlist_nonce'] ) ) : null;
    
    if ( $wishlist_token ) {
        
        if ( $wishlist_token && ! wp_verify_nonce( $nonce, 'thwl_wishlist_nonce_action' ) ) {
        return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
        return;
        }

        $shared_wishlist = THWL_Data::get_wishlist_by_token( $wishlist_token );       
        if ( $shared_wishlist ) {
            $is_owner = is_user_logged_in() && $shared_wishlist->user_id === get_current_user_id();

            if ( 'private' !== $shared_wishlist->privacy || $is_owner || current_user_can( 'manage_options' ) ) {
                $wishlist = $shared_wishlist;
            } else {
                $output .= sprintf(
                    '<p>%s</p>',
                    esc_html__( 'This wishlist is private and cannot be viewed.', 'th-wishlist' )
                );
                return $output;
            }
        } else {
            $output .= sprintf(
                '<p>%s</p>',
                esc_html__( 'The wishlist you are trying to view does not exist or has been deleted.', 'th-wishlist' )
            );
            return $output;
        }
    } else {
        $wishlist = THWL_Data::get_or_create_wishlist();
    }

    if ( ! $wishlist ) {
        $output .= sprintf(
            '<p>%s</p>',
            esc_html__( 'Could not retrieve your wishlist.', 'th-wishlist' )
        );
        return $output;
    }
    
    $items = THWL_Data::get_wishlist_items( $wishlist->id );

    $columns = !empty( $this->thwl_option['th_wishlist_table_columns'] ) 
        ? $this->thwl_option['th_wishlist_table_columns'] 
        : [];
    $themedefault = !empty( $this->thwl_option['thw_btn_style_theme'] ) && '1' === $this->thwl_option['thw_btn_style_theme'] 
        ? 'thw-table-theme-style' 
        : 'thw-table-custom-style';

    $output .= sprintf(
        '<div class="thw-wishlist-wrapper %s">',
        esc_attr( $themedefault )
    );
    $output .= sprintf(
        '<h2>%s</h2>',
        esc_html( $wishlist->wishlist_name )
    );
    $output .= '<form class="thw-wishlist-form">';
    $output .= '<table class="thw-wishlist-table"><thead><tr>';

    $default_labels = [
        'checkbox' => '<input type="checkbox" id="thw-select-all" />',
        'thumbnail' => esc_html__( 'Image', 'th-wishlist' ),
        'name' => esc_html__( 'Product', 'th-wishlist' ),
        'price' => esc_html__( 'Price', 'th-wishlist' ),
        'stock' => esc_html__( 'Stock Status', 'th-wishlist' ),
        'quantity' => esc_html__( 'Quantity', 'th-wishlist' ),
        'add_to_cart' => esc_html__( 'Button', 'th-wishlist' ),
        'date' => esc_html__( 'Date Added', 'th-wishlist' ),
        'remove' => esc_html__( 'Remove', 'th-wishlist' ),
    ];

    $saved_labels = !empty( $this->thwl_option['th_wishlist_table_column_labels'] ) 
        ? $this->thwl_option['th_wishlist_table_column_labels'] 
        : [];

    foreach ( $columns as $key ) {
        if ( isset( $default_labels[$key] ) ) {
            $label = 'checkbox' === $key 
                ? $default_labels['checkbox']
                : ( !empty( $saved_labels[$key] ) ? esc_html( $saved_labels[$key] ) : $default_labels[$key] );
            $output .= sprintf(
                '<th class="product-%s">%s</th>',
                esc_attr( $key ),
                $label
            );
        }
    }

    $output .= '</tr></thead><tbody>';

    if ( !empty( $items ) ) {
        foreach ( $items as $item ) {
            $_product = wc_get_product( $item->variation_id ? $item->variation_id : $item->product_id );
            if ( !$_product ) {
                continue;
            }

            $output .= sprintf(
                '<tr data-item-id="%s" data-product-id="%s">',
                esc_attr( $item->id ),
                esc_attr( $_product->get_id() )
            );

            foreach ( $columns as $key ) {
                $output .= sprintf( '<td class="product-%s">', esc_attr( $key ) );
                switch ( $key ) {
                    case 'checkbox':
                        $output .= sprintf(
                            '<input type="checkbox" name="wishlist_items[]" value="%s">',
                            esc_attr( $item->id )
                        );
                        break;
                    case 'thumbnail':
                        $output .= sprintf(
                            '<a href="%s">%s</a>',
                            esc_url( $_product->get_permalink() ),
                            wp_kses_post( $_product->get_image() )
                        );
                        break;
                    case 'name':
                        $output .= sprintf(
                            '<a href="%s">%s</a>',
                            esc_url( $_product->get_permalink() ),
                            esc_html( $_product->get_name() )
                        );
                        if ( $_product->is_type( 'variation' ) ) {
                            $output .= wp_kses_post( wc_get_formatted_variation( $_product, true ) );
                        }
                        break;
                    case 'price':
                        $output .= wp_kses_post( $_product->get_price_html() );
                        break;
                    case 'stock':
                        $stock_status = $_product->get_stock_status();
                        $stock_class = 'instock' === $stock_status ? 'in-stock' : 'out-of-stock';
                        $stock_text = 'instock' === $stock_status 
                            ? esc_html__( 'In Stock', 'th-wishlist' ) 
                            : esc_html__( 'Out of Stock', 'th-wishlist' );
                        $output .= sprintf(
                            '<span class="stock %s">%s</span>',
                            esc_attr( $stock_class ),
                            $stock_text
                        );
                        break;
                    case 'quantity':
                        if ( in_array( 'quantity', $columns, true ) ) {
                            $output .= sprintf(
                                '<input type="number" class="thw-qty" value="%s" min="1" step="1" data-item-id="%s">',
                                esc_attr( $item->quantity ),
                                esc_attr( $item->id )
                            );
                        }
                        break;
                    case 'add_to_cart':
                        $output .= '<div class="thw-add-to-cart-cell">';
                        $output .= $this->thw_render_add_to_cart_button( $_product, $item, $wishlist );
                        $output .= '</div>';
                        break;
                    case 'date':
                        $output .= sprintf(
                            '<span>%s</span>',
                            esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->added_at ) ) )
                        );
                        break;
                    case 'remove':
                        $output .= sprintf(
                            '<a href="#" class="thw-remove-item" title="%s">×</a>',
                            esc_attr__( 'Remove this product', 'th-wishlist' )
                        );
                        break;
                }
                $output .= '</td>';
            }
            $output .= '</tr>';
        }
    } else {
        $output .= sprintf(
            '<tr><td colspan="%s">%s</td></tr>',
            esc_attr( count( $columns ) ),
            esc_html__( 'Your wishlist is currently empty.', 'th-wishlist' )
        );
    }

    $output .= '</tbody></table></form>';

    $output .= '<div class="thw-wishlist-actions">';
    
    if ( in_array( 'checkbox', $columns, true ) && !empty( $items ) ) {
        $output .= sprintf(
            '<button class="button wp-element-button add_to_cart_button thw-add-all-to-cart">%s</button>',
            esc_html__( 'Add Selected to Cart', 'th-wishlist' )
        );
    }

    $output .= $this->render_social_share_links( $wishlist );

    $output .= '</div>';
    $output .= '</div>';

    return $output;

    }

    public function render_social_share_links( $wishlist ) {
    if (
        empty( $this->thwl_option['thw_show_social_share'] ) ||
        '1' !== $this->thwl_option['thw_show_social_share'] ||
        'private' === $wishlist->privacy ||
        empty( $wishlist->wishlist_token )
    ) {
        return '';
    }

    $output = '';

    $share_url = add_query_arg(
        'wishlist_token',
        $wishlist->wishlist_token,
        get_permalink( $this->thwl_option['thwl_page_id'] )
    );

    $encoded_url = urlencode( $share_url );
    $encoded_title = urlencode( __( 'My Wishlist', 'th-wishlist' ) );

    $output .= '<div class="thw-social-share">';
    $output .= sprintf(
        '<span class="thw-social-text">%s</span>',
        esc_html__( 'Share on:', 'th-wishlist' )
    );

    // Facebook
    $output .= sprintf(
        '<a href="%s" target="_blank" title="%s" class="thw-share-facebook"><span class="dashicons dashicons-facebook"></span></a>',
        esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_url ),
        esc_attr__( 'Facebook', 'th-wishlist' )
    );

    // Twitter (X)
    $output .= sprintf(
        '<a href="%s" target="_blank" title="%s" class="thw-share-twitter"><span class="dashicons dashicons-twitter"></span></a>',
        esc_url( 'https://twitter.com/intent/tweet?url=' . $encoded_url . '&text=' . $encoded_title ),
        esc_attr__( 'X (Twitter)', 'th-wishlist' )
    );

    // WhatsApp
    $output .= sprintf(
        '<a href="%s" target="_blank" title="%s" class="thw-share-whatsapp"><span class="dashicons dashicons-whatsapp"></span></a>',
        esc_url( 'https://wa.me/?text=' . $encoded_title . '%20' . $encoded_url ),
        esc_attr__( 'WhatsApp', 'th-wishlist' )
    );

    // Email
    $output .= sprintf(
        '<a href="%s" title="%s" class="thw-share-email"><span class="dashicons dashicons-email-alt"></span></a>',
        esc_url( 'mailto:?subject=' . $encoded_title . '&body=' . $encoded_url ),
        esc_attr__( 'Email', 'th-wishlist' )
    );

    // Copy link
    $output .= sprintf(
        '<a href="#" class="thw-copy-link-button" data-link="%s" title="%s"><span class="dashicons dashicons-admin-links"></span></a>',
        esc_attr( $share_url ),
        esc_attr__( 'Copy Link', 'th-wishlist' )
    );

    $output .= '</div>';

    return $output;
}

    
    public function thw_render_add_to_cart_button( $product, $item, $wishlist ) {

    if ( $this->thwl_option['thw_redirect_to_cart'] === '1' ) {
        // Build add to cart button HTML
        $button_attributes = [
            'class' => 'button wp-element-button add_to_cart_button thw-add-to-cart-ajax',
            'data-product-id' => esc_attr( $product->get_id() ),
            'data-quantity' => esc_attr( $item->quantity ),
            'data-item-id' => esc_attr( $item->id ),
            'data-wishlist-token' => esc_attr( $wishlist->wishlist_token ),
        ];

        $button_html = sprintf(
            '<button %s>%s</button>',
            implode(' ', array_map(
                function( $key, $value ) { return sprintf( '%s="%s"', $key, $value ); },
                array_keys( $button_attributes ),
                $button_attributes
            )),
            esc_html__( 'Add to Cart', 'th-wishlist' )
        );

        $output = sprintf( '<div class="thw-add-to-cart-cell">%s</div>', $button_html );

    }else{
        
        $custom_product = $product;

        if ( ! $custom_product || ! is_a( $custom_product, 'WC_Product' ) ) {
                return;
            }

            global $product;
            $previous_product = $product;
            $product = $custom_product;
            // Capture WooCommerce add to cart template output
            ob_start();
            woocommerce_template_loop_add_to_cart( [ 'quantity' => $item->quantity ] );
            $output = ob_get_clean();

            // Restore global product
            $product = $previous_product;

            } 

            return $output;
    }
   
    // AJAX Handlers
    public function thwl_add_to_wishlist_ajax() {
        if ($this->thwl_option['thw_require_login'] === '1' && !is_user_logged_in()) {
            wp_send_json_error(['message' => 'login_required']);
            return;
        }
        check_ajax_referer('thwl-add-nonce', 'nonce');
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $wishlist = THWL_Data::get_or_create_wishlist();
        if ($wishlist && THWL_Data::add_item($wishlist->id, $product_id)) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function thwl_remove_from_wishlist_ajax() {
        check_ajax_referer('thwl-remove-nonce', 'nonce');
        $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        THWL_Data::remove_item($item_id);
        wp_send_json_success();
    }
    
    public function thwl_update_item_quantity_ajax() {
        check_ajax_referer('thwl-update-qty-nonce', 'nonce');
        $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
        THWL_Data::update_item_quantity($item_id, $quantity);
        wp_send_json_success();
    }
    
    public function thwl_add_all_to_cart_ajax() {
        check_ajax_referer('thwl-add-all-nonce', 'nonce');
        $item_ids = isset($_POST['items']) ? array_map('absint', $_POST['items']) : [];
        foreach($item_ids as $item_id) {
            $item = THWL_Data::get_item($item_id);
            if($item) {
                WC()->cart->add_to_cart($item->product_id, $item->quantity, $item->variation_id);
            }
        }
        wp_send_json_success(['message' => 'Products added to cart.']);
    }

    //....................................................../
    //global and flexible shorcode to add wishlist any where
    //....................................................../
   // [thwl_add_to_wishlist product_id="10" custom_icon='<svg viewBox="0 0 20 20" fill="currentColor"><path d="..."/></svg>']
   public function thwl_add_to_wishlist_button_flexible_shortcode( $atts = [] ) {
    
    global $product;

    // Default product ID from global $product
    $default_product_id = ( isset( $product ) && is_a( $product, 'WC_Product' ) ) ? $product->get_id() : 0;

    // Merge shortcode attributes
    $atts = shortcode_atts( [
        'product_id'    => $default_product_id,
        'add_text'      => !empty( $this->thwl_option['thw_add_to_wishlist_text'] ) 
            ? $this->thwl_option['thw_add_to_wishlist_text'] 
            : esc_html__( 'Add to Wishlist', 'th-wishlist' ),
        'browse_text'   => !empty( $this->thwl_option['thw_browse_wishlist_text'] ) 
            ? $this->thwl_option['thw_browse_wishlist_text'] 
            : esc_html__( 'Browse Wishlist', 'th-wishlist' ),
        'icon_style'    => !empty( $this->thwl_option['thw_button_display_style'] ) 
            ? $this->thwl_option['thw_button_display_style'] 
            : 'icon_text',
        'custom_icon'   => !empty( $this->thwl_option['thw_custom_icon_url'] ) 
            ? $this->thwl_option['thw_custom_icon_url'] 
            : '',
    ], $atts, 'thw_add_to_wishlist' );

    // Load product using passed product_id
    $product = wc_get_product( $atts['product_id'] );
    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return '';
    }

    // Handle login requirement
    if ( !empty( $this->thwl_option['thw_require_login'] ) && '1' === $this->thwl_option['thw_require_login'] && ! is_user_logged_in() ) {
        $myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
        $myaccount_url = $myaccount_page_id ? esc_url( get_permalink( $myaccount_page_id ) ) : wp_login_url();
        return sprintf(
            '<div class="thw-add-to-wishlist-button-wrap"><a href="%s" class="thw-login-required">%s</a></div>',
            $myaccount_url,
            esc_html__( 'Login to add to wishlist', 'th-wishlist' )
            );
        }

    $wishlist = THWL_Data::get_or_create_wishlist();
    $product_id = $product->get_id();
    $variation_id = $product->is_type( 'variation' ) ? $product->get_id() : 0;
    $in_wishlist = $wishlist ? THWL_Data::is_product_in_wishlist( $wishlist->id, $product_id, $variation_id ) : false;

    $text = $in_wishlist ? $atts['browse_text'] : $atts['add_text'];

    $classes = $in_wishlist ? ['in-wishlist'] : [];
    $btnclasses = '';
    switch ( $atts['icon_style'] ) {
        case 'icon_only_no_style':
            $classes[] = 'no-style';
            break;
        case 'icon_text':
            $classes[] = 'th-icon-text';
            break;
        case 'icon':
            $classes[] = 'th-icon';
            $btnclasses = 'th-button';
            break;
        case 'text':
            $classes[] = 'th-text';
            break;
    }

    $class_attr = implode( ' ', array_filter( $classes ) );
    $icon_html = '';
    $text_html = sprintf( '<span>%s</span>', esc_html( $text ) );

    if ( in_array( $atts['icon_style'], [ 'icon', 'icon_text', 'icon_only_no_style' ], true ) ) {
        if ( ! empty( $atts['custom_icon'] ) ) {
            if ( filter_var( $atts['custom_icon'], FILTER_VALIDATE_URL ) ) {
                $icon_html = sprintf(
                    '<img src="%s" class="thw-icon" alt="%s" />',
                    esc_url( $atts['custom_icon'] ),
                    esc_attr__( 'Wishlist Icon', 'th-wishlist' )
                );
            } elseif ( strpos( $atts['custom_icon'], '<svg' ) !== false || strpos( $atts['custom_icon'], '<span' ) !== false ) {
                $icon_html = sprintf( '<span class="thw-icon">%s</span>', $atts['custom_icon'] );
            } else {
                $icon_html = '<span class="thw-icon"><span class="dashicons dashicons-heart"></span></span>';
            }
        } else {
            $icon_html = '<span class="thw-icon"><span class="dashicons dashicons-heart"></span></span>';
        }
    }

    if ( in_array( $atts['icon_style'], [ 'icon', 'icon_only_no_style' ], true ) ) {
        $text_html = '';
    } elseif ( $atts['icon_style'] === 'text' ) {
        $icon_html = '';
    }

    $wrap_class = is_singular( 'product' ) ? 'th-wishlist-single' : '';

    $output = sprintf(
        '<div class="thw-add-to-wishlist-button-wrap %s">',
        esc_attr( $wrap_class )
    );
    $output .= sprintf(
        '<button class="thw-add-to-wishlist-button %s" data-product-id="%s" data-variation-id="%s">%s%s</button>',
        esc_attr( trim( $btnclasses . ' ' . $class_attr ) ),
        esc_attr( $product_id ),
        esc_attr( $variation_id ),
        $icon_html,
        $text_html
    );
    $output .= '</div>';

    return $output;
}

// ajax mange table function
/**
 * Handle adding product to cart and managing wishlist via AJAX.
 */
public function thwl_add_to_cart_and_manage() {
    
    check_ajax_referer( 'thwl_wishlist_redirect_nonce', 'nonce' );

    // Validate that required POST variables exist
    if ( ! isset( $_POST['product_id'], $_POST['quantity'], $_POST['item_id'], $_POST['token'] ) ) {
        wp_send_json_error( [ 'message' => __( 'Missing required data.', 'th-wishlist' ) ] );
    }

    // Sanitize inputs
    $product_id = absint( wp_unslash( $_POST['product_id'] ) );
    $quantity   = max( 1, absint( wp_unslash( $_POST['quantity'] ) ) );
    $item_id    = absint( wp_unslash( $_POST['item_id'] ) );
    $token      = sanitize_text_field( wp_unslash( $_POST['token'] ) );

    // Validate product_id and item_id
    if ( ! $product_id || ! $item_id ) {
        wp_send_json_error( [ 'message' => __( 'Invalid product or item ID.', 'th-wishlist' ) ] );
    }

    // Verify product exists and is purchasable
    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
        wp_send_json_error( [ 'message' => __( 'Product not available.', 'th-wishlist' ) ] );
    }

    // Add product to cart
    WC()->cart->add_to_cart( $product_id, $quantity );

    // Remove item from wishlist based on settings
    if ( isset( $this->thwl_option['redirect_to_cart'] ) && '1' === $this->thwl_option['redirect_to_cart'] ) {
       THWL_Data::remove_item( $item_id );
    } else {
       THWL_Data::remove_item( $item_id ); // Remove item regardless if condition is met
    }

    wp_send_json_success( [
        'cart_url' => wc_get_cart_url(),
        'message'  => __( 'Product added and wishlist updated.', 'th-wishlist' )
    ] );
}

}