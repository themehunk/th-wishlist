<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$thwl_saved_options = get_option( 'thwl_settings', [] );
$thwl_options       = wp_parse_args( $thwl_saved_options, self::thwl_get_default_settings() );

$thwl_allowed_svg_tags = [
    'svg'  => [
        'class' => true, 'width' => true, 'height' => true, 'viewbox' => true,
        'fill'  => true, 'stroke' => true, 'stroke-width' => true, 'xmlns' => true,
    ],
    'path' => [
        'd' => true, 'fill' => true, 'stroke' => true, 'stroke-linecap' => true,
        'stroke-linejoin' => true, 'clip-rule' => true, 'fill-rule' => true,
    ],
];

$thwl_selected_icon    = $thwl_options['thw_redirect_wishlist_page_icon'] ?? 'heart-outline';
$thwl_icons_list       = thwl_get_wishlist_icons_svg();
$thwl_icon_color       = $thwl_options['thw_redirect_wishlist_page_icon_color'] ?? '#111';
$thwl_icon_color_hover = $thwl_options['thw_redirect_wishlist_page_icon_color_hvr'] ?? '#111';
$thwl_icon_size        = $thwl_options['thw_redirect_wishlist_page_icon_size'] ?? '24';
?>

<div class="thwl-redirect-wrapper thw-redirect-wishlist-dependent">

    <!-- ===== Left: Settings Panel ===== -->
    <div class="thwl-redirect-panel thwl-redirect-settings">

        <div class="thwl-redirect-panel-header">
            <span class="dashicons dashicons-heart"></span>
            <?php esc_html_e( 'Wishlist Icon', 'th-wishlist' ); ?>
        </div>

        <!-- Icon Picker -->
        <div class="thwl-redirect-section">
            <p class="thwl-redirect-section-label"><?php esc_html_e( 'Choose Icon', 'th-wishlist' ); ?></p>
            <div class="thwl-redirect-icon-grid">
                <?php foreach ( $thwl_icons_list as $thwl_icon_key => $thwl_icon_data ) : ?>
                <label class="thwl-redirect-icon-option <?php echo ( $thwl_selected_icon === $thwl_icon_key ) ? 'selected' : ''; ?>">
                    <input type="radio"
                        name="settings[thw_redirect_wishlist_page_icon]"
                        value="<?php echo esc_attr( $thwl_icon_key ); ?>"
                        <?php checked( $thwl_selected_icon, $thwl_icon_key ); ?> />
                    <span title="<?php echo esc_attr( $thwl_icon_data['name'] ); ?>">
                        <?php echo wp_kses( $thwl_icon_data['svg'], $thwl_allowed_svg_tags ); ?>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Settings Rows -->
        <div class="thwl-redirect-rows">

            <!-- Color -->
            <div class="thwl-redirect-row">
                <span class="thwl-redirect-row-label"><?php esc_html_e( 'Color', 'th-wishlist' ); ?></span>
                <div class="thwl-redirect-row-control">
                    <button type="button" class="th-color-reset thwl-redirect-reset"
                        data-target="thw_redirect_wishlist_page_icon_color"
                        title="<?php esc_attr_e( 'Reset color', 'th-wishlist' ); ?>">
                        <span class="dashicons dashicons-image-rotate"></span>
                    </button>
                    <input type="text"
                        id="thw_redirect_wishlist_page_icon_color"
                        name="settings[thw_redirect_wishlist_page_icon_color]"
                        value="<?php echo esc_attr( $thwl_icon_color ); ?>"
                        class="th_color_picker thwl-redirect-color-swatch"
                        data-default-color="<?php echo esc_attr( $thwl_icon_color ); ?>"
                        style="background-color: <?php echo esc_attr( $thwl_icon_color ); ?>" />
                </div>
            </div>

            <!-- Hover Color -->
            <div class="thwl-redirect-row">
                <span class="thwl-redirect-row-label"><?php esc_html_e( 'Hover Color', 'th-wishlist' ); ?></span>
                <div class="thwl-redirect-row-control">
                    <button type="button" class="th-color-reset thwl-redirect-reset"
                        data-target="thw_redirect_wishlist_page_icon_color_hvr"
                        title="<?php esc_attr_e( 'Reset color', 'th-wishlist' ); ?>">
                        <span class="dashicons dashicons-image-rotate"></span>
                    </button>
                    <input type="text"
                        id="thw_redirect_wishlist_page_icon_color_hvr"
                        name="settings[thw_redirect_wishlist_page_icon_color_hvr]"
                        value="<?php echo esc_attr( $thwl_icon_color_hover ); ?>"
                        class="th_color_picker thwl-redirect-color-swatch"
                        data-default-color="<?php echo esc_attr( $thwl_icon_color_hover ); ?>"
                        style="background-color: <?php echo esc_attr( $thwl_icon_color_hover ); ?>" />
                </div>
            </div>

            <!-- Size -->
            <div class="thwl-redirect-row">
                <span class="thwl-redirect-row-label"><?php esc_html_e( 'Size', 'th-wishlist' ); ?></span>
                <div class="thwl-redirect-row-control">
                    <input type="number"
                        id="thw_redirect_wishlist_page_icon_size"
                        name="settings[thw_redirect_wishlist_page_icon_size]"
                        value="<?php echo esc_attr( $thwl_icon_size ); ?>"
                        class="thwl-redirect-size-input"
                        min="10" max="100" />
                    <span class="thwl-redirect-unit">px</span>
                </div>
            </div>

        </div><!-- .thwl-redirect-rows -->
    </div><!-- .thwl-redirect-settings -->

    <!-- ===== Right: Live Preview ===== -->
    <div class="thwl-redirect-panel thwl-redirect-preview">

        <div class="thwl-redirect-panel-header">
            <span class="dashicons dashicons-visibility"></span>
            <?php esc_html_e( 'Live Preview', 'th-wishlist' ); ?>
        </div>

        <div class="thwl-redirect-preview-area">
            <a id="thwl_button_preview_redirect" class="thwl-redirect-preview-btn" href="#">
                <span id="thwl_icon_preview_redirect">
                    <?php
                    if ( isset( $thwl_icons_list[ $thwl_selected_icon ] ) ) {
                        echo wp_kses( $thwl_icons_list[ $thwl_selected_icon ]['svg'], $thwl_allowed_svg_tags );
                    }
                    ?>
                </span>
            </a>
        </div>

        <p class="thwl-redirect-preview-hint">
            <?php esc_html_e( 'Hover over the icon to see hover color.', 'th-wishlist' ); ?>
        </p>

    </div><!-- .thwl-redirect-preview -->

</div><!-- .thwl-redirect-wrapper -->
