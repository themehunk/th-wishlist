<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$saved_options = get_option( 'thwl_settings', [] );
    $options = wp_parse_args( $saved_options, self::thwl_get_default_settings() );

                     $th_wishlist_tb_btn_txt_color = isset( $options['th_wishlist_tb_btn_txt_color'] ) ? $options['th_wishlist_tb_btn_txt_color'] : '';
                    $th_wishlist_tb_btn_bg_color = isset( $options['th_wishlist_tb_btn_bg_color'] ) ? $options['th_wishlist_tb_btn_bg_color'] : '';

                    $th_wishlist_table_bg_color = isset( $options['th_wishlist_table_bg_color'] ) ? $options['th_wishlist_table_bg_color'] : '#fff';
                    $th_wishlist_table_brd_color = isset( $options['th_wishlist_table_brd_color'] ) ? $options['th_wishlist_table_brd_color'] : '#eee';
                    $th_wishlist_table_txt_color = isset( $options['th_wishlist_table_txt_color'] ) ? $options['th_wishlist_table_txt_color'] : '#111';

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

<div class="thwl-settings-wrapper" style="display:flex; gap:30px;">
    <!-- Left Settings Panel -->
    <div class="thwl-settings-left">
        <div class="thwl-setting-group" >
            <label class="thwl-setting-label"><?php esc_html_e( 'Button', 'th-wishlist' ); ?></label>
                  <div class="thwl-setting-content thwl-2-col">
                       <?php 
                            self::thwl_render_color_picker('th_wishlist_tb_btn_txt_color', $th_wishlist_tb_btn_txt_color, __('Color','th-wishlist'),$th_wishlist_tb_btn_txt_color); 
                            self::thwl_render_color_picker('th_wishlist_tb_btn_bg_color', $th_wishlist_tb_btn_bg_color, __('Background','th-wishlist'),$th_wishlist_tb_btn_bg_color); 
                        ?> 
                  </div>
        </div>
        <div class="thwl-setting-group" >
            <label class="thwl-setting-label"><?php esc_html_e( 'Wishlist Table', 'th-wishlist' ); ?></label>
                <div class="thwl-setting-content thwl-2-col">
                            <?php 
                                self::thwl_render_color_picker('th_wishlist_table_bg_color', $th_wishlist_table_bg_color, __('Backround','th-wishlist'),$th_wishlist_table_bg_color); 
                                self::thwl_render_color_picker('th_wishlist_table_brd_color', $th_wishlist_table_brd_color, __('Border','th-wishlist'),$th_wishlist_table_brd_color); 
                                self::thwl_render_color_picker('th_wishlist_table_txt_color', $th_wishlist_table_txt_color, __('Text','th-wishlist'),$th_wishlist_table_txt_color); 
                            ?>   
               </div>
        </div>

        <div class="thwl-setting-group" >
            <label class="thwl-setting-label"><?php esc_html_e( 'Facebook', 'th-wishlist' ); ?></label>
                <div class="thwl-setting-content thwl-2-col">
                <?php 
                            self::thwl_render_color_picker('th_wishlist_shr_fb_color', $th_wishlist_shr_fb_color, __('Color','th-wishlist'),$th_wishlist_shr_fb_color); 
                            self::thwl_render_color_picker('th_wishlist_shr_fb_hvr_color', $th_wishlist_shr_fb_hvr_color, __('Hover','th-wishlist'),$th_wishlist_shr_fb_hvr_color);  
                        ?>
               </div>
        </div>

         <div class="thwl-setting-group" >
            <label class="thwl-setting-label"><?php esc_html_e( 'X', 'th-wishlist' ); ?></label>
                <div class="thwl-setting-content thwl-2-col">
                <?php 
                            self::thwl_render_color_picker('th_wishlist_shr_x_color', $th_wishlist_shr_x_color, __('Color','th-wishlist'),$th_wishlist_shr_x_color); 
                            self::thwl_render_color_picker('th_wishlist_shr_x_hvr_color', $th_wishlist_shr_x_hvr_color, __('Hover','th-wishlist'),$th_wishlist_shr_x_hvr_color);  
                        ?>
               </div>
        </div>

        <div class="thwl-setting-group" >
            <label class="thwl-setting-label"><?php esc_html_e( 'Whatsapp', 'th-wishlist' ); ?></label>
                <div class="thwl-setting-content thwl-2-col">
                 <?php 
                            self::thwl_render_color_picker('th_wishlist_shr_w_color', $th_wishlist_shr_w_color, __('Color','th-wishlist'),$th_wishlist_shr_w_color); 
                            self::thwl_render_color_picker('th_wishlist_shr_w_hvr_color', $th_wishlist_shr_w_hvr_color, __('Hover','th-wishlist'),$th_wishlist_shr_w_hvr_color);  
                        ?>
               </div>
        </div>

        <div class="thwl-setting-group" >
            <label class="thwl-setting-label"><?php esc_html_e( 'Email', 'th-wishlist' ); ?></label>
                <div class="thwl-setting-content thwl-2-col">
                 <?php 
                            self::thwl_render_color_picker('th_wishlist_shr_e_color', $th_wishlist_shr_e_color, __('Color','th-wishlist'),$th_wishlist_shr_e_color); 
                            self::thwl_render_color_picker('th_wishlist_shr_e_hvr_color', $th_wishlist_shr_e_hvr_color, __('Hover','th-wishlist'),$th_wishlist_shr_e_hvr_color);  
                        ?>
               </div>
        </div>

        <div class="thwl-setting-group" >
            <label class="thwl-setting-label"><?php esc_html_e( 'Copy Url', 'th-wishlist' ); ?></label>
                <div class="thwl-setting-content thwl-2-col">
                 <?php 
                            self::thwl_render_color_picker('th_wishlist_shr_c_color', $th_wishlist_shr_c_color, __('Color','th-wishlist'),$th_wishlist_shr_c_color); 
                            self::thwl_render_color_picker('th_wishlist_shr_c_hvr_color', $th_wishlist_shr_c_hvr_color, __('Hover','th-wishlist'),$th_wishlist_shr_c_hvr_color);  
                        ?>
               </div>
        </div>
    </div>
    <!-- Right Live Preview Panel -->
    <div class="thwl-live-preview-right">
       <h4><?php esc_html_e( 'Live Preview', 'th-wishlist' ); ?></h4>
        <div class="thw-wishlist-wrapper thw-table-custom-style lite">
        <table class="thwl-preview-table thwl-preview-table-1" 
            style="width:100%; border-collapse:collapse; background-color:<?php echo esc_attr($th_wishlist_table_bg_color); ?>; color:<?php echo esc_attr($th_wishlist_table_txt_color); ?>; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;">
            <thead>
                <tr>
                    <th style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;"> 
                    <input type="checkbox"  />
                    </th>
                    <th style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;"><?php esc_html_e('Product', 'th-wishlist'); ?></th>
                    <th style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;"><?php esc_html_e('Title', 'th-wishlist'); ?></th>
                    <th style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;"><?php esc_html_e('Price', 'th-wishlist'); ?></th>
                    <th style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;"><?php esc_html_e('Stock', 'th-wishlist'); ?></th>
                    <th style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;"><?php esc_html_e('Action', 'th-wishlist'); ?></th>
                    <th style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>;"><?php esc_html_e('Remove', 'th-wishlist'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>; text-align:center;">
                        <input type="checkbox" />
                    </td>
                <td style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>; text-align:center;">
                        <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" 
                            alt="<?php esc_attr_e( 'Dummy Product', 'th-wishlist' ); ?>" 
                            style="max-width:60px; border:1px solid #ddd; border-radius:4px;" />
                    </td>
                    <td class="title" style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>; ">
                        <?php esc_html_e('Sample Product', 'th-wishlist'); ?>
                    </td>
                    <td class="price" style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>; ">
                        <?php esc_html_e( '$49.00', 'th-wishlist' ); ?>
                    </td>
                    <td class="stock" style="padding:10px;border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>; ">
                        <?php esc_html_e('In Stock', 'th-wishlist'); ?>
                    </td>
                    <td class="cart" style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>; text-align:center;">
                        <button style=" padding:6px 12px; border:none; border-radius:4px; cursor:pointer;">
                            <?php esc_html_e('Add to Cart', 'th-wishlist'); ?>
                        </button>
                    </td>
                    <td class="remove" style="padding:10px; border:1px solid <?php echo esc_attr($th_wishlist_table_brd_color); ?>; text-align:center;">
                        <span  style=" margin-left:10px; cursor:pointer;">
                            <?php esc_html_e( '✕', 'th-wishlist' ); ?>
                        </span>
                    </td>
                    
                </tr>
            </tbody>
        </table>
        <div class="thw-wishlist-actions">
        <div class="all-button"
                    style="color:<?php echo esc_attr( $th_wishlist_tb_btn_txt_color ); ?>; 
                            background-color:<?php echo esc_attr( $th_wishlist_tb_btn_bg_color ); ?>; 
                            font-size:16px; 
                            padding:10px 20px; 
                            border-radius:4px; 
                            display:inline-block;">
                    <?php esc_html_e( 'Button', 'th-wishlist' ); ?>
        </div>
        <div class="thw-social-share">
            <span class="thw-social-text"></span><a href="#" target="_blank" title="Facebook" class="thw-share-facebook"><span class="dashicons dashicons-facebook"></span></a><a href="#" target="_blank" title="X (Twitter)" class="thw-share-twitter"><span class="dashicons dashicons-twitter"></span></a><a href="#" target="_blank" title="WhatsApp" class="thw-share-whatsapp"><span class="dashicons dashicons-whatsapp"></span></a>
            <a href="#" title="Email" class="thw-share-email"><span class="dashicons dashicons-email-alt"></span></a><a href="#" class="thw-copy-link-button" data-link="#" title="Copy Link"><span class="dashicons dashicons-admin-links"></span></a></div>
         </div>
        </div>
    </div>
</div>