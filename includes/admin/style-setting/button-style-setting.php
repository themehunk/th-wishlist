<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$saved_options = get_option( 'thwl_settings', [] );
$options = wp_parse_args( $saved_options, self::thwl_get_default_settings() );
?>
<div class="thwl-settings-wrapper" style="display:flex; gap:30px;">
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

            $selected_icon = $options['th_wishlist_add_icon'] ?? '';
            $addicondashicons = thwl_get_wishlist_icons_svg();
            $th_wishlist_add_icon_color = $options['th_wishlist_add_icon_color'] ?? '#111';
            ?>
            <div class="thw-icon-options" style="display:flex; flex-wrap:wrap; gap:10px;">
                <?php foreach ( $addicondashicons as $icon_key => $icon_data ) : ?>
                    <label class="thw-dashicon-option" style="cursor:pointer; display:flex; flex-direction:column; align-items:center;">
                        <input type="radio" name="settings[th_wishlist_add_icon]" value="<?php echo esc_attr( $icon_key ); ?>" <?php checked( $selected_icon, $icon_key ); ?> />
                        <span title="<?php echo esc_attr( $icon_data['name'] ); ?>">
                            <?php echo wp_kses( $icon_data['svg'], $allowed_svg_tags ); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="thwl-setting-content thwl-2-col">
            <?php 
            self::thwl_render_color_picker('th_wishlist_add_icon_color', $th_wishlist_add_icon_color, __('Add to Wishlist Icon','th-wishlist'), $th_wishlist_add_icon_color); 
            ?>
            </div>
        </div>
        <!-- Browse Wishlist Icon -->
        <div class="thwl-setting-group" style="margin-top:20px;">
            <label class="thwl-setting-label"><?php esc_html_e( 'Browse Wishlist Icon', 'th-wishlist' ); ?></label>
            <?php 
            $selected_brws_icon = $options['th_wishlist_brws_icon'] ?? '';
            $brwsicondashicons = thwl_get_wishlist_icons_svg();
            $th_wishlist_brws_icon_color = $options['th_wishlist_brws_icon_color'] ?? '#111';
            ?>
            <div class="thw-icon-options" style="display:flex; flex-wrap:wrap; gap:10px;">
                <?php foreach ( $brwsicondashicons as $icon_key => $icon_data ) : ?>
                    <label class="thw-dashicon-option" style="cursor:pointer; display:flex; flex-direction:column; align-items:center;">
                        <input type="radio" name="settings[th_wishlist_brws_icon]" value="<?php echo esc_attr( $icon_key ); ?>" <?php checked( $selected_brws_icon, $icon_key ); ?> />
                        <span title="<?php echo esc_attr( $icon_data['name'] ); ?>">
                            <?php echo wp_kses( $icon_data['svg'], $allowed_svg_tags ); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="thwl-setting-content thwl-2-col">
            <?php 
            self::thwl_render_color_picker('th_wishlist_brws_icon_color', $th_wishlist_brws_icon_color, __('Browse Wishlist Icon','th-wishlist'), $th_wishlist_brws_icon_color); 
            ?>
            </div>
        </div>
        <!-- Button Colors -->
        <div class="thwl-setting-group" >
            <div class="thwl-setting-content thwl-2-col">
            <?php 
            $th_wishlist_btn_bg_color = $options['th_wishlist_btn_bg_color'] ?? 'transparent';
            $th_wishlist_btn_txt_color = $options['th_wishlist_btn_txt_color'] ?? '#333';
            self::thwl_render_color_picker('th_wishlist_btn_txt_color', $th_wishlist_btn_txt_color, __('Button Text','th-wishlist'), $th_wishlist_btn_txt_color); 
            //self::render_color_picker('th_wishlist_btn_bg_color', $th_wishlist_btn_bg_color, __('Button Background color','th-wishlist-pro'), $th_wishlist_btn_bg_color); 
           ?>
           </div>
        </div>
    </div>

    <!-- Right Live Preview Panel -->
    <div class="thwl-live-preview-right" style="flex:1;">
        <a id="thwl_button_preview" style="font-size: 14px; display:flex; align-items:center; padding:10px 20px; gap:5px; border:none; border-radius:4px; cursor:pointer; width: max-content;">
            <span id="thwl_icon_preview">
                <?php 
                if(isset($addicondashicons[$selected_icon])){
                    echo wp_kses($addicondashicons[$selected_icon]['svg'], $allowed_svg_tags);
                }
                ?>
            </span>
            <?php esc_html_e('Wishlist', 'th-wishlist'); ?>
            </a>
            <a id="thwl_button_preview_browse"  class="browse" style="display:flex; font-size: 14px;align-items:center; padding:10px 20px; gap:5px; border:none; border-radius:4px; cursor:pointer; width: max-content;">
            <span id="thwl_brws_icon_preview">
                <?php 
                if(isset($addicondashicons[$selected_brws_icon])){
                    echo wp_kses($addicondashicons[$selected_brws_icon]['svg'], $allowed_svg_tags);
                }
                ?>
            </span>
            <?php esc_html_e('Browse', 'th-wishlist'); ?>
            </a>
    </div>
</div>