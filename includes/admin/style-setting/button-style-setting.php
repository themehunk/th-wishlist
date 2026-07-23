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

$thwl_add_icons        = thwl_get_wishlist_icons_svg();
$thwl_selected_add     = $thwl_options['th_wishlist_add_icon']       ?? 'heart-outline';
$thwl_add_icon_color   = $thwl_options['th_wishlist_add_icon_color'] ?? '#111';

$thwl_selected_brws    = $thwl_options['th_wishlist_brws_icon']       ?? 'heart-filled';
$thwl_brws_icon_color  = $thwl_options['th_wishlist_brws_icon_color'] ?? '#111';

$thwl_btn_txt_color    = $thwl_options['th_wishlist_btn_txt_color']   ?? '#333';
$thwl_btn_bg_color     = $thwl_options['th_wishlist_btn_bg_color']    ?? '#6a4df5';
?>

<div class="thwl-style-layout">

    <!-- ===== Left: Settings Column ===== -->
    <div class="thwl-style-settings-col">

        <!-- Card 1: Add to Wishlist Icon -->
        <div class="thwl-style-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-heart"></span>
                <?php esc_html_e( 'Add to Wishlist Icon', 'th-wishlist' ); ?>
            </div>

            <div class="thwl-style-card-body">
                <p class="thwl-style-section-label"><?php esc_html_e( 'Choose Icon', 'th-wishlist' ); ?></p>
                <div class="thwl-style-icon-grid">
                    <?php foreach ( $thwl_add_icons as $key => $data ) : ?>
                    <label class="thwl-style-icon-option <?php echo ( $thwl_selected_add === $key ) ? 'selected' : ''; ?>">
                        <input type="radio"
                            name="settings[th_wishlist_add_icon]"
                            value="<?php echo esc_attr( $key ); ?>"
                            <?php checked( $thwl_selected_add, $key ); ?> />
                        <span title="<?php echo esc_attr( $data['name'] ); ?>">
                            <?php echo wp_kses( $data['svg'], $thwl_allowed_svg_tags ); ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="thwl-style-rows">
                    <div class="thwl-style-row">
                        <span class="thwl-style-row-label"><?php esc_html_e( 'Icon Color', 'th-wishlist' ); ?></span>
                        <div class="thwl-style-row-control">
                            <button type="button" class="th-color-reset thwl-style-reset"
                                data-target="th_wishlist_add_icon_color"
                                title="<?php esc_attr_e( 'Reset color', 'th-wishlist' ); ?>">
                                <span class="dashicons dashicons-image-rotate"></span>
                            </button>
                            <input type="text"
                                id="thwl-icon-color"
                                name="settings[th_wishlist_add_icon_color]"
                                value="<?php echo esc_attr( $thwl_add_icon_color ); ?>"
                                class="th_color_picker thwl-style-color-swatch"
                                data-default-color="<?php echo esc_attr( $thwl_add_icon_color ); ?>"
                                style="background-color: <?php echo esc_attr( $thwl_add_icon_color ); ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- card 1 -->

        <!-- Card 2: Browse Wishlist Icon -->
        <div class="thwl-style-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-heart"></span>
                <?php esc_html_e( 'Browse Wishlist Icon', 'th-wishlist' ); ?>
            </div>

            <div class="thwl-style-card-body">
                <p class="thwl-style-section-label"><?php esc_html_e( 'Choose Icon', 'th-wishlist' ); ?></p>
                <div class="thwl-style-icon-grid">
                    <?php foreach ( $thwl_add_icons as $key => $data ) : ?>
                    <label class="thwl-style-icon-option <?php echo ( $thwl_selected_brws === $key ) ? 'selected' : ''; ?>">
                        <input type="radio"
                            name="settings[th_wishlist_brws_icon]"
                            value="<?php echo esc_attr( $key ); ?>"
                            <?php checked( $thwl_selected_brws, $key ); ?> />
                        <span title="<?php echo esc_attr( $data['name'] ); ?>">
                            <?php echo wp_kses( $data['svg'], $thwl_allowed_svg_tags ); ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="thwl-style-rows">
                    <div class="thwl-style-row">
                        <span class="thwl-style-row-label"><?php esc_html_e( 'Icon Color', 'th-wishlist' ); ?></span>
                        <div class="thwl-style-row-control">
                            <button type="button" class="th-color-reset thwl-style-reset"
                                data-target="th_wishlist_brws_icon_color"
                                title="<?php esc_attr_e( 'Reset color', 'th-wishlist' ); ?>">
                                <span class="dashicons dashicons-image-rotate"></span>
                            </button>
                            <input type="text"
                                id="thwl-icon-b-color"
                                name="settings[th_wishlist_brws_icon_color]"
                                value="<?php echo esc_attr( $thwl_brws_icon_color ); ?>"
                                class="th_color_picker thwl-style-color-swatch"
                                data-default-color="<?php echo esc_attr( $thwl_brws_icon_color ); ?>"
                                style="background-color: <?php echo esc_attr( $thwl_brws_icon_color ); ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- card 2 -->

        <!-- Card 3: Button Colors -->
        <div class="thwl-style-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-button"></span>
                <?php esc_html_e( 'Button Colors', 'th-wishlist' ); ?>
            </div>

            <div class="thwl-style-card-body">
                <div class="thwl-style-rows">
                    <div class="thwl-style-row">
                        <span class="thwl-style-row-label"><?php esc_html_e( 'Button Text', 'th-wishlist' ); ?></span>
                        <div class="thwl-style-row-control">
                            <button type="button" class="th-color-reset thwl-style-reset"
                                data-target="th_wishlist_btn_txt_color"
                                title="<?php esc_attr_e( 'Reset color', 'th-wishlist' ); ?>">
                                <span class="dashicons dashicons-image-rotate"></span>
                            </button>
                            <input type="text"
                                id="th_wishlist_btn_txt_color"
                                name="settings[th_wishlist_btn_txt_color]"
                                value="<?php echo esc_attr( $thwl_btn_txt_color ); ?>"
                                class="th_color_picker thwl-style-color-swatch"
                                data-default-color="<?php echo esc_attr( $thwl_btn_txt_color ); ?>"
                                style="background-color: <?php echo esc_attr( $thwl_btn_txt_color ); ?>" />
                        </div>
                    </div>

                    <div class="thwl-style-row">
                        <span class="thwl-style-row-label"><?php esc_html_e( 'Button Background', 'th-wishlist' ); ?></span>
                        <div class="thwl-style-row-control">
                            <button type="button" class="th-color-reset thwl-style-reset"
                                data-target="th_wishlist_btn_bg_color"
                                title="<?php esc_attr_e( 'Reset color', 'th-wishlist' ); ?>">
                                <span class="dashicons dashicons-image-rotate"></span>
                            </button>
                            <input type="text"
                                id="th_wishlist_btn_bg_color"
                                name="settings[th_wishlist_btn_bg_color]"
                                value="<?php echo esc_attr( $thwl_btn_bg_color ); ?>"
                                class="th_color_picker thwl-style-color-swatch"
                                data-default-color="<?php echo esc_attr( $thwl_btn_bg_color ); ?>"
                                style="background-color: <?php echo esc_attr( $thwl_btn_bg_color ); ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- card 3 -->

    </div><!-- .thwl-style-settings-col -->

    <!-- ===== Right: Live Preview ===== -->
    <div class="thwl-style-preview-col">
        <div class="thwl-style-card thwl-style-preview-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-visibility"></span>
                <?php esc_html_e( 'Live Preview', 'th-wishlist' ); ?>
            </div>

            <div class="thwl-style-preview-body">
                <!-- Add to Wishlist button preview -->
                <div class="thwl-style-preview-item">
                    <span class="thwl-style-preview-chip"><?php esc_html_e( 'Add', 'th-wishlist' ); ?></span>
                    <a id="thwl_button_preview"
                        class="thwl-style-btn-preview"
                        style="color:<?php echo esc_attr( $thwl_btn_txt_color ); ?>;background:<?php echo esc_attr( $thwl_btn_bg_color ); ?>;">
                        <span id="thwl_icon_preview">
                            <?php
                            if ( isset( $thwl_add_icons[ $thwl_selected_add ] ) ) {
                                echo wp_kses( $thwl_add_icons[ $thwl_selected_add ]['svg'], $thwl_allowed_svg_tags );
                            }
                            ?>
                        </span>
                        <?php esc_html_e( 'Wishlist', 'th-wishlist' ); ?>
                    </a>
                </div>

                <div class="thwl-style-preview-divider"></div>

                <!-- Browse Wishlist button preview -->
                <div class="thwl-style-preview-item">
                    <span class="thwl-style-preview-chip"><?php esc_html_e( 'Browse', 'th-wishlist' ); ?></span>
                    <a id="thwl_button_preview_browse"
                        class="thwl-style-btn-preview browse"
                        style="color:<?php echo esc_attr( $thwl_btn_txt_color ); ?>;background:<?php echo esc_attr( $thwl_btn_bg_color ); ?>;">
                        <span id="thwl_brws_icon_preview">
                            <?php
                            if ( isset( $thwl_add_icons[ $thwl_selected_brws ] ) ) {
                                echo wp_kses( $thwl_add_icons[ $thwl_selected_brws ]['svg'], $thwl_allowed_svg_tags );
                            }
                            ?>
                        </span>
                        <?php esc_html_e( 'Browse Wishlist', 'th-wishlist' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div><!-- .thwl-style-preview-col -->

</div><!-- .thwl-style-layout -->
