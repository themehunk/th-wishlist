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

    // Declare the property to avoid dynamic property deprecation warning
    private $th_wishlist_option;

    public function __construct() {

        // Use static method directly, no need to instantiate
        $this->th_wishlist_option = get_option( 'th_wishlist_settings', TH_Wishlist_Settings::get_default_settings() );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
        add_shortcode( 'th_wcwl_wishlist', array( $this, 'wishlist_page_shortcode' ) );
        add_shortcode('th_wcwl_wishlist_button', array( $this,'thw_add_to_wishlist_button_shortcode'));
        
        //flexible shortcode
        add_shortcode( 'thw_add_to_wishlist', array( $this, 'thw_add_to_wishlist_button_flexible_shortcode') );

        add_action( 'wp', array( $this, 'hook_wishlist_loop_button_position' ) );
        add_action( 'wp', array( $this, 'hook_wishlist_single_button_position' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_thw_add_to_wishlist', array( $this, 'add_to_wishlist_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_add_to_wishlist', array( $this, 'add_to_wishlist_ajax' ) );
        add_action( 'wp_ajax_thw_remove_from_wishlist', array( $this, 'remove_from_wishlist_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_remove_from_wishlist', array( $this, 'remove_from_wishlist_ajax' ) );
        add_action( 'wp_ajax_thw_update_item_quantity', array( $this, 'update_item_quantity_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_update_item_quantity', array( $this, 'update_item_quantity_ajax' ) );
        add_action( 'wp_ajax_thw_add_all_to_cart', array( $this, 'add_all_to_cart_ajax' ) );
        add_action( 'wp_ajax_nopriv_thw_add_all_to_cart', array( $this, 'add_all_to_cart_ajax' ) );
        
        add_action('wp_ajax_thw_add_to_cart_and_manage', array( $this, 'thw_add_to_cart_and_manage'));
        add_action('wp_ajax_nopriv_thw_add_to_cart_and_manage', array( $this, 'thw_add_to_cart_and_manage'));
    }

    public function enqueue_styles_scripts() {
        
        wp_enqueue_style( 'thw-wishlist', THW_URL . 'assets/css/wishlist.css', array(),'1.0.3');
        wp_enqueue_script( 'thw-wishlist', THW_URL . 'assets/js/wishlist.js', array( 'jquery' ), '1.0.3', true );
        
        $wishlist_page_id = isset($this->th_wishlist_option['th_wcwl_wishlist_page_id']) ? $this->th_wishlist_option['th_wcwl_wishlist_page_id'] : 0;
        $thw_redirect_to_cart = isset($this->th_wishlist_option['thw_redirect_to_cart']) ? $this->th_wishlist_option['thw_redirect_to_cart'] : '';
        
        wp_localize_script( 'thw-wishlist', 'thw_wishlist_params', array(
            'ajax_url'            => admin_url( 'admin-ajax.php' ),
            'add_nonce'           => wp_create_nonce( 'thw-add-nonce' ),
            'remove_nonce'        => wp_create_nonce( 'thw-remove-nonce' ),
            'update_qty_nonce'    => wp_create_nonce( 'thw-update-qty-nonce' ),
            'add_all_nonce'       => wp_create_nonce( 'thw-add-all-nonce' ),
            'wishlist_page_url'   => $wishlist_page_id ? get_permalink( $wishlist_page_id ) : '',
            'i18n_added'          => isset($this->th_wishlist_option['thw_browse_wishlist_text']) ? $this->th_wishlist_option['thw_browse_wishlist_text'] : __('Browse Wishlist', 'th-wishlist'),
            'i18n_error'          => __('An error occurred. Please try again.', 'th-wishlist' ),
            'i18n_empty_wishlist' => __('Your wishlist is currently empty.', 'th-wishlist'),
            'redirect_to_cart'    => $thw_redirect_to_cart === '1',
            'cart_url'            => wc_get_cart_url(),
            'icon_style'          => isset($this->th_wishlist_option['thw_button_display_style']) ? $this->th_wishlist_option['thw_button_display_style'] : 'icon_text',
            'redirect_nonce'      => wp_create_nonce('thw_wishlist_redirect_nonce'),
            ) );
    }

    public function thw_add_to_wishlist_button_shortcode() {

    global $product;

    if (!isset($product) || !is_a($product, 'WC_Product')) {
        return '';
    }

    ob_start();

    if (isset($this->th_wishlist_option['thw_require_login']) && $this->th_wishlist_option['thw_require_login'] === '1' && !is_user_logged_in()) {
        echo '<a href="' . esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) . '" class="button thw-login-required">' . __('Login to add to wishlist', 'th-wishlist') . '</a>';
        return ob_get_clean();
    }

    $wishlist = TH_Wishlist_Data::get_or_create_wishlist();
    $product_id = $product->get_id();
    $variation_id = $product->is_type('variation') ? $product->get_id() : 0;
    $in_wishlist = $wishlist ? TH_Wishlist_Data::is_product_in_wishlist($wishlist->id, $product_id, $variation_id) : false;

    $add_text = isset($this->th_wishlist_option['thw_add_to_wishlist_text']) ? $this->th_wishlist_option['thw_add_to_wishlist_text'] : __('Add to Wishlist', 'th-wishlist');
    $browse_text = isset($this->th_wishlist_option['thw_browse_wishlist_text']) ? $this->th_wishlist_option['thw_browse_wishlist_text'] : __('Browse Wishlist', 'th-wishlist');
    $text = $in_wishlist ? $browse_text : $add_text;

    $classes = [];

    if ( $in_wishlist ) {
        $classes[] = 'in-wishlist';
    }

    $display_style = isset($this->th_wishlist_option['thw_button_display_style']) ? $this->th_wishlist_option['thw_button_display_style'] : 'icon_text';
    $btnclasses ='';
    if ( $display_style === 'icon_only_no_style' ) {
        $classes[] = 'no-style';
    }elseif ( $display_style === 'icon_text' ) {
        $classes[] = 'th-icon-text';
    }elseif( $display_style === 'icon' ) {
        $classes[] = 'th-icon';
        $btnclasses = 'th-button';
    }elseif( $display_style === 'text' ) {
        $classes[] = 'th-text';
    }else{
       $classes[] = '';
    }

    // Convert to string for HTML
    $class_attr = implode( ' ', $classes );

    $icon_html = '';
    $text_html = '<span>' . esc_html($text) . '</span>';

    if (in_array($display_style, ['icon', 'icon_text', 'icon_only_no_style'])) {
        if (isset($this->th_wishlist_option['thw_use_custom_icon']) && $this->th_wishlist_option['thw_use_custom_icon'] === '1' && !empty($this->th_wishlist_option['thw_custom_icon_url'])) {
            $icon_html = '<img src="' . esc_url($this->th_wishlist_option['thw_custom_icon_url']) . '" class="thw-icon" alt="Wishlist Icon" />';
        } else {
            $icon_html = '<span class="thw-icon">
            <span class="dashicons dashicons-heart"></span>
            </span>';
        }
    }

    if (in_array($display_style, ['icon', 'icon_only_no_style'])) {
        $text_html = '';
    }

    if ($display_style === 'text') {
        $icon_html = '';
    }

    $wrap_class = is_singular('product') ? 'th-wishlist-single' : '';
    
    ?>

    <div class="thw-add-to-wishlist-button-wrap <?php echo esc_attr($wrap_class);?> ">
    <button class="thw-add-to-wishlist-button  <?php echo esc_attr($btnclasses); ?> <?php echo esc_attr($class_attr);?>" data-product-id="<?php echo esc_attr($product_id);?>" data-variation-id="<?php echo esc_attr($variation_id);?>"><?php echo $icon_html; ?><?php echo $text_html;?></button>
    </div>

    <?php 
        return ob_get_clean();
    }

   public function add_to_wishlist_button() {
    echo do_shortcode('[th_wcwl_wishlist_button]');
   }

   public function hook_wishlist_loop_button_position() {

    $thw_show_in_loop = isset( $this->th_wishlist_option['thw_show_in_loop'] ) ? $this->th_wishlist_option['thw_show_in_loop'] : 1;

    if ( ! $thw_show_in_loop ) {

		return;
	}

    $position = isset( $this->th_wishlist_option['thw_in_loop_position'] ) ? $this->th_wishlist_option['thw_in_loop_position'] : 'after_crt_btn';

    if ( is_admin() || is_singular( 'product' ) ) {
        return; 
    }

    switch ( $position ) {

            case 'before_crt_btn':
               
                if ( th_is_wc_block_template( 'archive-product' ) ) {
                    // For blockified loop: hook before Add to Cart (product-button)
                    add_filter( 'render_block_woocommerce/product-button', array( $this, 'inject_wishlist_in_block' ), 5, 3 );
                } else {
                    // Classic template: hook before Add to Cart in loop
                    add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_to_wishlist_button' ), 7 );
                }
                break;

            case 'after_crt_btn':
                if ( th_is_wc_block_template( 'archive-product' ) ) {
                    add_filter( 'render_block_woocommerce/product-button', array( $this, 'inject_wishlist_in_block' ),20, 3  );
                } else {
                    add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_to_wishlist_button' ), 15 );
                }
                break;

            case 'on_top':
                if ( th_is_wc_block_template( 'archive-product' ) ) {
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

  public function hook_wishlist_single_button_position() {


    $position = isset( $this->th_wishlist_option['thw_in_single_position'] ) ? $this->th_wishlist_option['thw_in_single_position'] : 'after_crt_btn';

    if ( ! is_singular( 'product' ) ) {
        return; 
    }

    if ( th_is_wc_block_template( 'single-product' ) ) {
        
       $this->add_button_for_blockified_template('single-product', $position);
       
    }else{
        switch ( $position ) {
        case 'after_thumb':
            // Hook before "Add to Cart" by using before item end
            add_action( 'woocommerce_before_single_product_summary', array( $this, 'add_to_wishlist_button' ), 21 );
            break;

        case 'after_crt_btn':
            add_action( 'woocommerce_single_product_summary', array( $this, 'add_to_wishlist_button' ), 0 );
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
                $block = ( 'single-product' === $template ) ? 'add-to-cart-form' : 'product-button';
                add_filter( "render_block_woocommerce/$block", array( $this, 'inject_wishlist_in_block' ), 10, 3 );
                $hooked = true;
                break;

            case 'after_thumb':
                add_filter( 'render_block_woocommerce/product-image-gallery', array( $this, 'inject_wishlist_in_block' ), 10, 3 );
                $hooked = true;
                break;

            case 'after_summ':
                add_filter( 'render_block_woocommerce/product-details', array( $this, 'inject_wishlist_in_block' ), 10, 3 );
                $hooked = true;
                break;
        }

        if ( $hooked ) {
            do_action( 'th_wishlist_blockified_hook_attached', $template, $position );
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
        $columns = $this->th_wishlist_option['th_wishlist_table_columns'];

        echo '<div class="thw-wishlist-wrapper">';
        echo '<h2>' . esc_html($wishlist->wishlist_name) . '</h2>';
        echo '<form class="thw-wishlist-form">';
        echo '<table class="thw-wishlist-table"><thead><tr>';

        // Default column labels.
        $default_labels = [
            'checkbox' => '<input type="checkbox" id="thw-select-all" />',
            'thumbnail' => __('Image', 'th-wishlist'),
            'name' => __('Product', 'th-wishlist'),
            'price' => __('Price', 'th-wishlist'),
            'stock' => __('Stock Status', 'th-wishlist'),
            'quantity' => __('Quantity', 'th-wishlist'),
            'add_to_cart' => __('Button', 'th-wishlist'),
            'date' => __('Date Added', 'th-wishlist'),
            'remove' => __('Remove', 'th-wishlist'),
        ];

        // Get saved labels from options.
        $saved_labels = isset( $this->th_wishlist_option['th_wishlist_table_column_labels'] ) 
            ? $this->th_wishlist_option['th_wishlist_table_column_labels'] 
            : [];

        foreach ( $columns as $key ) {
            if ( isset( $default_labels[ $key ] ) ) {
                $label = $key === 'checkbox' 
                    ? $default_labels['checkbox'] // keep checkbox raw HTML
                    : ( ! empty( $saved_labels[ $key ] ) ? esc_html( $saved_labels[ $key ] ) : esc_html( $default_labels[ $key ] ) );
                echo '<th class="product-' . esc_attr( $key ) . '">' . $label . '</th>';
            }
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
                             echo $this->thw_render_add_to_cart_button( $_product , $item, $wishlist);
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
        
        if (in_array('checkbox', $columns) && !empty($items)) {
            echo '<button class="button wp-element-button add_to_cart_button  thw-add-all-to-cart">' . __('Add Selected to Cart', 'th-wishlist') . '</button>';
        }

        $this->render_social_share_links( $wishlist );

        echo '</div>'; // .thw-wishlist-actions
        echo '</div>'; // .thw-wishlist-wrapper
        
        return ob_get_clean();
    }

    public function render_social_share_links( $wishlist ) {
        if (
            ! isset( $this->th_wishlist_option['thw_show_social_share'] ) ||
            $this->th_wishlist_option['thw_show_social_share'] !== '1' ||
            $wishlist->privacy === 'private' ||
            empty( $wishlist->wishlist_token )
        ) {
            return;
        }

        $share_url = add_query_arg(
            'wishlist_token',
            $wishlist->wishlist_token,
            get_permalink( $this->th_wishlist_option['th_wcwl_wishlist_page_id'] )
        );

        $encoded_url = urlencode( $share_url );
        $encoded_title = urlencode( __( 'My Wishlist', 'th-wishlist' ) );

        echo '<div class="thw-social-share">';
        echo '<span class="thw-social-text">' . esc_html__( 'Share on:', 'th-wishlist' ) . '</span>';

        // Facebook
        echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . esc_url( $encoded_url ) . '" target="_blank" title="Facebook" class="thw-share-facebook">';
        echo '<span class="dashicons dashicons-facebook"></span>';
        echo '</a>';

        // Twitter (X)
        echo '<a href="https://twitter.com/intent/tweet?url=' . esc_url( $encoded_url ) . '&text=' . $encoded_title . '" target="_blank" title="X (Twitter)" class="thw-share-twitter">';
        echo '<span class="dashicons dashicons-twitter"></span>';
        echo '</a>';

        // WhatsApp
        echo '<a href="https://wa.me/?text=' . $encoded_title . '%20' . $encoded_url . '" target="_blank" title="WhatsApp" class="thw-share-whatsapp">';
        echo '<span class="dashicons dashicons-whatsapp"></span>';
        echo '</a>';

        // Email
        echo '<a href="mailto:?subject=' . $encoded_title . '&body=' . $encoded_url . '" title="Email" class="thw-share-email">';
        echo '<span class="dashicons dashicons-email-alt"></span>';
        echo '</a>';

        // Copy link
        echo '<a href="#" class="thw-copy-link-button" data-link="' . esc_attr( $share_url ) . '" title="' . esc_attr__( 'Copy Link', 'th-wishlist' ) . '">';
        echo '<span class="dashicons dashicons-admin-links"></span>';
        echo '</a>';

        echo '</div>';
    }


    
    public function thw_render_add_to_cart_button( $product, $item, $wishlist ) {

    if ( $this->th_wishlist_option['thw_redirect_to_cart'] === '1' ) {
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
    public function add_to_wishlist_ajax() {
        if ($this->th_wishlist_option['thw_require_login'] === '1' && !is_user_logged_in()) {
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

    //....................................................../
    //global and flexible shorcode to add wishlist any where
    //....................................................../
   public function thw_add_to_wishlist_button_flexible_shortcode( $atts = [] ) {

    // For Example to Use Shorcode in Flexible add to cart wishlist
    // [thw_add_to_wishlist product_id="10" custom_icon='<svg viewBox="0 0 20 20" fill="currentColor"><path d="..."/></svg>'];
    
    global $product;

    // Default product ID from global $product
    $default_product_id = ( isset( $product ) && is_a( $product, 'WC_Product' ) ) ? $product->get_id() : 0;

    // Merge shortcode attributes
    $atts = shortcode_atts( [
        'product_id'    => $default_product_id,
        'add_text'      => isset( $this->th_wishlist_option['thw_add_to_wishlist_text'] ) ? $this->th_wishlist_option['thw_add_to_wishlist_text'] : __( 'Add to Wishlist', 'th-wishlist' ),
        'browse_text'   => isset( $this->th_wishlist_option['thw_browse_wishlist_text'] ) ? $this->th_wishlist_option['thw_browse_wishlist_text'] : __( 'Browse Wishlist', 'th-wishlist' ),
        'icon_style'    => isset( $this->th_wishlist_option['thw_button_display_style'] ) ? $this->th_wishlist_option['thw_button_display_style'] : 'icon_text',
        'custom_icon'   => isset( $this->th_wishlist_option['thw_custom_icon_url'] ) ? $this->th_wishlist_option['thw_custom_icon_url'] : '',
    ], $atts, 'thw_add_to_wishlist' );

    // Load product using passed product_id
    $product = wc_get_product( $atts['product_id'] );
    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return ''; // Invalid product
    }

    if ( isset( $this->th_wishlist_option['thw_require_login'] ) && $this->th_wishlist_option['thw_require_login'] === '1' && ! is_user_logged_in() ) {
        return '<a href="' . esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '" class="button thw-login-required">' . __( 'Login to add to wishlist', 'th-wishlist' ) . '</a>';
    }

    $wishlist = TH_Wishlist_Data::get_or_create_wishlist();
    $product_id = $product->get_id();
    $variation_id = $product->is_type( 'variation' ) ? $product->get_id() : 0;
    $in_wishlist = $wishlist ? TH_Wishlist_Data::is_product_in_wishlist( $wishlist->id, $product_id, $variation_id ) : false;

    $text = $in_wishlist ? $atts['browse_text'] : $atts['add_text'];

    $classes = [];
    if ( $in_wishlist ) {
        $classes[] = 'in-wishlist';
    }

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
        default:
            break;
    }

    $class_attr = implode( ' ', $classes );

    $icon_html = '';
    $text_html = '<span>' . esc_html( $text ) . '</span>';

    if ( in_array( $atts['icon_style'], [ 'icon', 'icon_text', 'icon_only_no_style' ], true ) ) {
    if ( ! empty( $atts['custom_icon'] ) ) {
        if ( filter_var( $atts['custom_icon'], FILTER_VALIDATE_URL ) ) {
            // Image URL
            $icon_html = '<img src="' . esc_url( $atts['custom_icon'] ) . '" class="thw-icon" alt="Wishlist Icon" />';
        } elseif ( strpos( $atts['custom_icon'], '<svg' ) !== false || strpos( $atts['custom_icon'], '<span' ) !== false ) {
            // Raw HTML (SVG or Dashicons span)
            $icon_html = '<span class="thw-icon">' . $atts['custom_icon'] . '</span>';
        } else {
            // Fallback to default icon
            $icon_html = '<span class="thw-icon"><span class="dashicons dashicons-heart"></span></span>';
        }
    } else {
        $icon_html = '<span class="thw-icon"><span class="dashicons dashicons-heart"></span></span>';
    }
    }

    if ( in_array( $atts['icon_style'], [ 'icon', 'icon_only_no_style' ], true ) ) {
        $text_html = '';
    }

    if ( $atts['icon_style'] === 'text' ) {
        $icon_html = '';
    }

    $wrap_class = is_singular( 'product' ) ? 'th-wishlist-single' : '';

    ob_start();
    ?>
    <div class="thw-add-to-wishlist-button-wrap <?php echo esc_attr( $wrap_class ); ?>">
        <button class="thw-add-to-wishlist-button <?php echo esc_attr( $btnclasses . ' ' . $class_attr ); ?>"
                data-product-id="<?php echo esc_attr( $product_id ); ?>"
                data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
            <?php echo $icon_html; ?>
            <?php echo $text_html; ?>
        </button>
    </div>
    <?php
    return ob_get_clean();
}


// ajax mange table function
public function thw_add_to_cart_and_manage() {

    check_ajax_referer('thw_wishlist_redirect_nonce', 'nonce');

    $product_id = absint($_POST['product_id']);
    $quantity   = max(1, absint($_POST['quantity']));
    $item_id    = absint($_POST['item_id']);
    $token      = sanitize_text_field($_POST['token']);

    if (!$product_id || !$item_id) {
        wp_send_json_error(['message' => 'Invalid data']);
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
        wp_send_json_error(['message' => 'Product not available.']);
    }

    // Add product to cart
    WC()->cart->add_to_cart($product_id, $quantity);

    if (isset($this->th_wishlist_option['redirect_to_cart']) === '1') {
        TH_Wishlist_Data::remove_item($item_id);
    }

    TH_Wishlist_Data::remove_item($item_id);

    wp_send_json_success([
        'cart_url' => wc_get_cart_url(),
        'message'  => 'Product added and wishlist updated.'
    ]);
}



}
