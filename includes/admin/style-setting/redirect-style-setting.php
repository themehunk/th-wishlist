<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$options = get_option( 'thwl_settings', [] );
?>
<div class="thwl-settings-wrapper thw-redirect-wishlist-dependent" style="display:flex; gap:30px;">
    <!-- Left Settings Panel -->
    <div class="thwl-settings-left" style="flex:1;">
        <!-- Add to Wishlist Icon -->
        <div class="thwl-setting-group">
            <label class="thwl-setting-label"><?php esc_html_e( 'Add to Wishlist Icon', 'th-wishlist' ); ?></label>
            <?php 
            $allowed_svg_tags = array(
                'svg' => array(
                    'class'=> true,'width'=> true,'height'=> true,'viewbox'=> true,
                    'fill'=> true,'stroke'=> true,'stroke-width'=> true,'xmlns'=> true
                ),
                'path' => array(
                    'd'=> true,'fill'=> true,'stroke'=> true,'stroke-linecap'=> true,
                    'stroke-linejoin'=> true,'clip-rule'=> true,'fill-rule'=> true
                ),
            );

            $selected_icon = $options['thw_redirect_wishlist_page_icon'] ?? '';
            $addicondashicons = thwl_get_wishlist_icons_svg();
            $thw_redirect_wishlist_page_icon_color = isset( $options['thw_redirect_wishlist_page_icon_color'] ) ? $options['thw_redirect_wishlist_page_icon_color'] : '#111';
            $thw_redirect_wishlist_page_icon_color_hvr = isset( $options['thw_redirect_wishlist_page_icon_color_hvr'] ) ? $options['thw_redirect_wishlist_page_icon_color_hvr'] : '#111';
            $thw_redirect_wishlist_page_icon_size = isset( $options['thw_redirect_wishlist_page_icon_size'] ) ? $options['thw_redirect_wishlist_page_icon_size'] : '24';
            ?>
            <div class="thw-icon-options" style="display:flex; flex-wrap:wrap; gap:10px;">
                <?php foreach ( $addicondashicons as $icon_key => $icon_data ) : ?>
                    <label class="thw-dashicon-option" style="cursor:pointer; display:flex; flex-direction:column; align-items:center;">
                        <input type="radio" name="settings[thw_redirect_wishlist_page_icon]" value="<?php echo esc_attr( $icon_key ); ?>" <?php checked( $selected_icon, $icon_key ); ?> />
                        <span title="<?php echo esc_attr( $icon_data['name'] ); ?>">
                            <?php echo wp_kses( $icon_data['svg'], $allowed_svg_tags ); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="thwl-setting-content thwl-2-col">
            <?php 
            self::thwl_render_color_picker('thw_redirect_wishlist_page_icon_color', $thw_redirect_wishlist_page_icon_color, __('Color','th-wishlist'), $thw_redirect_wishlist_page_icon_color); 
            self::thwl_render_color_picker('thw_redirect_wishlist_page_icon_color_hvr', $thw_redirect_wishlist_page_icon_color_hvr, __('Hover','th-wishlist'), $thw_redirect_wishlist_page_icon_color_hvr);?>
            <div class="th-number">
                <div class="th-numb-label">
                        <p><?php esc_html_e( 'Size', 'th-wishlist' ); ?></p>
                </div>
                <div class="th-numb-input">
                         <input type="number" class="small-text" id="thw_redirect_wishlist_page_icon_size" name="settings[thw_redirect_wishlist_page_icon_size]" value="<?php echo esc_attr( $thw_redirect_wishlist_page_icon_size ); ?>" min="0" max="100" step="">
                         <span><?php esc_html_e( 'px', 'th-wishlist' ); ?></span>
                </div>
            </div> 
           
            </div>
        </div>
        
    </div>
    <!-- Right Live Preview Panel -->
    <div class="thwl-live-preview-right" style="flex:1;">
        <h4><?php esc_html_e( 'Live Preview', 'th-wishlist' ); ?></h4>
        <a id="thwl_button_preview_redirect" style="font-size: 14px; display:flex; align-items:center; padding:10px 20px; gap:5px; border:none; border-radius:4px; cursor:pointer; width: max-content;">
            <span id="thwl_icon_preview_redirect">
                <?php 
                if(isset($addicondashicons[$selected_icon])){
                    echo wp_kses($addicondashicons[$selected_icon]['svg'], $allowed_svg_tags);
                }
                ?>
            </span>
        </a>
    </div>
</div>

