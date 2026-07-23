<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$thwl_saved_options = get_option( 'thwl_settings', [] );
$thwl_options       = wp_parse_args( $thwl_saved_options, self::thwl_get_default_settings() );

$thwl_tb_btn_txt_color = $thwl_options['th_wishlist_tb_btn_txt_color'] ?? '#fff';
$thwl_tb_btn_bg_color  = $thwl_options['th_wishlist_tb_btn_bg_color']  ?? '#6a4df5';
$thwl_table_bg_color   = $thwl_options['th_wishlist_table_bg_color']   ?? '#fff';
$thwl_table_brd_color  = $thwl_options['th_wishlist_table_brd_color']  ?? '#eee';
$thwl_table_txt_color  = $thwl_options['th_wishlist_table_txt_color']  ?? '#111';

$thwl_shr_fb_color     = $thwl_options['th_wishlist_shr_fb_color']     ?? '#1877F2';
$thwl_shr_fb_hvr_color = $thwl_options['th_wishlist_shr_fb_hvr_color'] ?? '#1877F2';
$thwl_shr_x_color      = $thwl_options['th_wishlist_shr_x_color']      ?? '#000';
$thwl_shr_x_hvr_color  = $thwl_options['th_wishlist_shr_x_hvr_color']  ?? '#000';
$thwl_shr_w_color      = $thwl_options['th_wishlist_shr_w_color']      ?? '#25D366';
$thwl_shr_w_hvr_color  = $thwl_options['th_wishlist_shr_w_hvr_color']  ?? '#25D366';
$thwl_shr_e_color      = $thwl_options['th_wishlist_shr_e_color']      ?? '#E4405F';
$thwl_shr_e_hvr_color  = $thwl_options['th_wishlist_shr_e_hvr_color']  ?? '#E4405F';
$thwl_shr_c_color      = $thwl_options['th_wishlist_shr_c_color']      ?? '#333';
$thwl_shr_c_hvr_color  = $thwl_options['th_wishlist_shr_c_hvr_color']  ?? '#333';

/* helper to render a color row inline */
$render_color_row = function( $id, $value, $label, $default ) {
    ?>
    <div class="thwl-style-row">
        <span class="thwl-style-row-label"><?php echo esc_html( $label ); ?></span>
        <div class="thwl-style-row-control">
            <button type="button" class="th-color-reset thwl-style-reset"
                data-target="<?php echo esc_attr( $id ); ?>"
                title="Reset color">
                <span class="dashicons dashicons-image-rotate"></span>
            </button>
            <input type="text"
                id="<?php echo esc_attr( $id ); ?>"
                name="settings[<?php echo esc_attr( $id ); ?>]"
                value="<?php echo esc_attr( $value ); ?>"
                class="th_color_picker thwl-style-color-swatch"
                data-default-color="<?php echo esc_attr( $default ); ?>"
                style="background-color: <?php echo esc_attr( $value ); ?>" />
        </div>
    </div>
    <?php
};
?>

<div class="thwl-style-layout">

    <!-- ===== Left: Settings Column ===== -->
    <div class="thwl-style-settings-col">

        <!-- Card: Wishlist Table -->
        <div class="thwl-style-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e( 'Wishlist Table', 'th-wishlist' ); ?>
            </div>
            <div class="thwl-style-card-body">
                <div class="thwl-style-rows">
                    <?php $render_color_row( 'th_wishlist_table_bg_color',  $thwl_table_bg_color,  __( 'Background', 'th-wishlist' ), $thwl_table_bg_color ); ?>
                    <?php $render_color_row( 'th_wishlist_table_brd_color', $thwl_table_brd_color, __( 'Border', 'th-wishlist' ),     $thwl_table_brd_color ); ?>
                    <?php $render_color_row( 'th_wishlist_table_txt_color', $thwl_table_txt_color, __( 'Text', 'th-wishlist' ),       $thwl_table_txt_color ); ?>
                </div>
            </div>
        </div>

        <!-- Card: Action Button -->
        <div class="thwl-style-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-cart"></span>
                <?php esc_html_e( 'Action Button', 'th-wishlist' ); ?>
            </div>
            <div class="thwl-style-card-body">
                <div class="thwl-style-rows">
                    <?php $render_color_row( 'th_wishlist_tb_btn_txt_color', $thwl_tb_btn_txt_color, __( 'Text', 'th-wishlist' ),       $thwl_tb_btn_txt_color ); ?>
                    <?php $render_color_row( 'th_wishlist_tb_btn_bg_color',  $thwl_tb_btn_bg_color,  __( 'Background', 'th-wishlist' ), $thwl_tb_btn_bg_color ); ?>
                </div>
            </div>
        </div>

        <!-- Card: Social Share -->
        <div class="thwl-style-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-share"></span>
                <?php esc_html_e( 'Social Share', 'th-wishlist' ); ?>
            </div>
            <div class="thwl-style-card-body">
                <div class="thwl-style-rows">

                    <div class="thwl-style-row thwl-style-row-group-header">
                        <span class="thwl-style-group-title">
                            <span class="thwl-style-fb-dot"></span>
                            <?php esc_html_e( 'Facebook', 'th-wishlist' ); ?>
                        </span>
                    </div>
                    <?php $render_color_row( 'th_wishlist_shr_fb_color',     $thwl_shr_fb_color,     __( 'Color', 'th-wishlist' ),      $thwl_shr_fb_color ); ?>
                    <?php $render_color_row( 'th_wishlist_shr_fb_hvr_color', $thwl_shr_fb_hvr_color, __( 'Hover Color', 'th-wishlist' ), $thwl_shr_fb_hvr_color ); ?>

                    <div class="thwl-style-row thwl-style-row-group-header">
                        <span class="thwl-style-group-title">
                            <span class="thwl-style-x-dot"></span>
                            <?php esc_html_e( 'X (Twitter)', 'th-wishlist' ); ?>
                        </span>
                    </div>
                    <?php $render_color_row( 'th_wishlist_shr_x_color',     $thwl_shr_x_color,     __( 'Color', 'th-wishlist' ),      $thwl_shr_x_color ); ?>
                    <?php $render_color_row( 'th_wishlist_shr_x_hvr_color', $thwl_shr_x_hvr_color, __( 'Hover Color', 'th-wishlist' ), $thwl_shr_x_hvr_color ); ?>

                    <div class="thwl-style-row thwl-style-row-group-header">
                        <span class="thwl-style-group-title">
                            <span class="thwl-style-wa-dot"></span>
                            <?php esc_html_e( 'WhatsApp', 'th-wishlist' ); ?>
                        </span>
                    </div>
                    <?php $render_color_row( 'th_wishlist_shr_w_color',     $thwl_shr_w_color,     __( 'Color', 'th-wishlist' ),      $thwl_shr_w_color ); ?>
                    <?php $render_color_row( 'th_wishlist_shr_w_hvr_color', $thwl_shr_w_hvr_color, __( 'Hover Color', 'th-wishlist' ), $thwl_shr_w_hvr_color ); ?>

                    <div class="thwl-style-row thwl-style-row-group-header">
                        <span class="thwl-style-group-title">
                            <span class="thwl-style-ig-dot"></span>
                            <?php esc_html_e( 'Email', 'th-wishlist' ); ?>
                        </span>
                    </div>
                    <?php $render_color_row( 'th_wishlist_shr_e_color',     $thwl_shr_e_color,     __( 'Color', 'th-wishlist' ),      $thwl_shr_e_color ); ?>
                    <?php $render_color_row( 'th_wishlist_shr_e_hvr_color', $thwl_shr_e_hvr_color, __( 'Hover Color', 'th-wishlist' ), $thwl_shr_e_hvr_color ); ?>

                    <div class="thwl-style-row thwl-style-row-group-header">
                        <span class="thwl-style-group-title">
                            <span class="thwl-style-cp-dot"></span>
                            <?php esc_html_e( 'Copy Link', 'th-wishlist' ); ?>
                        </span>
                    </div>
                    <?php $render_color_row( 'th_wishlist_shr_c_color',     $thwl_shr_c_color,     __( 'Color', 'th-wishlist' ),      $thwl_shr_c_color ); ?>
                    <?php $render_color_row( 'th_wishlist_shr_c_hvr_color', $thwl_shr_c_hvr_color, __( 'Hover Color', 'th-wishlist' ), $thwl_shr_c_hvr_color ); ?>

                </div>
            </div>
        </div>

    </div><!-- .thwl-style-settings-col -->

    <!-- ===== Right: Live Preview ===== -->
    <div class="thwl-style-preview-col">
        <div class="thwl-style-card thwl-style-preview-card">
            <div class="thwl-style-card-header">
                <span class="dashicons dashicons-visibility"></span>
                <?php esc_html_e( 'Live Preview', 'th-wishlist' ); ?>
            </div>

            <div class="thwl-style-preview-body thwl-content-preview-body">

                <!-- Table Preview -->
                <div class="thw-wishlist-wrapper thw-table-custom-style lite">
                    <table class="thwl-preview-table thwl-preview-table-1"
                        style="width:100%;border-collapse:collapse;
                            background:<?php echo esc_attr( $thwl_table_bg_color ); ?>;
                            color:<?php echo esc_attr( $thwl_table_txt_color ); ?>;
                            border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;">
                        <thead>
                            <tr>
                                <th style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;font-weight:600;font-size:12px;"><?php esc_html_e( 'Image', 'th-wishlist' ); ?></th>
                                <th style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;font-weight:600;font-size:12px;"><?php esc_html_e( 'Product', 'th-wishlist' ); ?></th>
                                <th style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;font-weight:600;font-size:12px;"><?php esc_html_e( 'Price', 'th-wishlist' ); ?></th>
                                <th style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;font-weight:600;font-size:12px;"><?php esc_html_e( 'Action', 'th-wishlist' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;text-align:center;">
                                    <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>"
                                        alt="<?php esc_attr_e( 'Product', 'th-wishlist' ); ?>"
                                        style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #eee;" />
                                </td>
                                <td style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;font-size:13px;">
                                    <?php esc_html_e( 'Sample Product', 'th-wishlist' ); ?>
                                </td>
                                <td style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;font-size:13px;">
                                    $49.00
                                </td>
                                <td style="padding:8px 10px;border:1px solid <?php echo esc_attr( $thwl_table_brd_color ); ?>;text-align:center;">
                                    <button class="cart" style="padding:5px 12px;border:none;border-radius:4px;cursor:pointer;font-size:12px;
                                        color:<?php echo esc_attr( $thwl_tb_btn_txt_color ); ?>;
                                        background:<?php echo esc_attr( $thwl_tb_btn_bg_color ); ?>;">
                                        <?php esc_html_e( 'Add to Cart', 'th-wishlist' ); ?>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- All to Cart button -->
                    <div class="thw-wishlist-actions" style="margin-top:10px;">
                        <div class="all-button"
                            style="display:inline-flex;align-items:center;padding:8px 16px;border-radius:4px;font-size:13px;cursor:pointer;
                                color:<?php echo esc_attr( $thwl_tb_btn_txt_color ); ?>;
                                background:<?php echo esc_attr( $thwl_tb_btn_bg_color ); ?>;">
                            <?php esc_html_e( 'Add All to Cart', 'th-wishlist' ); ?>
                        </div>
                    </div>

                    <!-- Social Share preview -->
                    <div class="thw-social-share" style="margin-top:10px;display:flex;gap:8px;align-items:center;">
                        <a class="thw-share-facebook" href="#" style="color:<?php echo esc_attr( $thwl_shr_fb_color ); ?>;font-size:13px;text-decoration:none;font-weight:600;">f</a>
                        <a class="thw-share-twitter"  href="#" style="color:<?php echo esc_attr( $thwl_shr_x_color ); ?>;font-size:13px;text-decoration:none;font-weight:600;">𝕏</a>
                        <a class="thw-share-whatsapp" href="#" style="color:<?php echo esc_attr( $thwl_shr_w_color ); ?>;font-size:13px;text-decoration:none;font-weight:600;">W</a>
                        <a class="thw-share-email"    href="#" style="color:<?php echo esc_attr( $thwl_shr_e_color ); ?>;font-size:13px;text-decoration:none;font-weight:600;">@</a>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- .thwl-style-preview-col -->

</div><!-- .thwl-style-layout -->
