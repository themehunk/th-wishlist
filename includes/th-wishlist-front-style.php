<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Generate dynamic front-end styles
 */
function thwl_front_style() {

	$custom_css = '';

	// Get saved settings with fallback defaults
	$th_wishlist_option = get_option( 'thwl_settings', THWL_Settings::thwl_get_default_settings() );

	// These values should be sanitized on save, not escaped here
	$th_wishlist_add_icon_color   = $th_wishlist_option['th_wishlist_add_icon_color'];
	$th_wishlist_brws_icon_color  = $th_wishlist_option['th_wishlist_brws_icon_color'];
	$th_wishlist_btn_txt_color    = $th_wishlist_option['th_wishlist_btn_txt_color'];
	$th_wishlist_btn_bg_color     = $th_wishlist_option['th_wishlist_btn_bg_color'];

	// Button CSS
	$custom_css .= "
	.thw-btn-custom-style .thw-add-to-wishlist-button .thw-icon {
		color: {$th_wishlist_add_icon_color};
	}
	.thw-btn-custom-style .thw-add-to-wishlist-button.in-wishlist .thw-icon {
		color: {$th_wishlist_brws_icon_color};
	}
	.thw-btn-custom-style .thw-add-to-wishlist-button {
		color: {$th_wishlist_btn_txt_color};
		background: {$th_wishlist_btn_bg_color};
	}
	";

	// Table button colors
	$th_wishlist_tb_btn_bg_color  = $th_wishlist_option['th_wishlist_tb_btn_bg_color'];
	$th_wishlist_tb_btn_txt_color = $th_wishlist_option['th_wishlist_tb_btn_txt_color'];

	$custom_css .= "
	.thw-table-custom-style .thw-wishlist-actions .thw-add-all-to-cart,
	.thw-table-custom-style .thw-add-to-cart-cell .button {
		color: {$th_wishlist_tb_btn_bg_color};
		background: {$th_wishlist_tb_btn_txt_color};
	}
	";

	// Table background and text colors
	$th_wishlist_table_bg_color  = $th_wishlist_option['th_wishlist_table_bg_color'];
	$th_wishlist_table_brd_color = $th_wishlist_option['th_wishlist_table_brd_color'];
	$th_wishlist_table_txt_color = $th_wishlist_option['th_wishlist_table_txt_color'];

	$custom_css .= "
	.thw-table-custom-style .thw-wishlist-table {
		color: {$th_wishlist_table_txt_color};
		background: {$th_wishlist_table_bg_color};
	}
	.thw-table-custom-style .thw-wishlist-table th,
	.thw-table-custom-style .thw-wishlist-table td {
		border-color: {$th_wishlist_table_brd_color};
	}
	";

	// Social Share colors
	// Facebook
	$custom_css .= "
	.thw-table-custom-style .thw-social-share a.thw-share-facebook {
		color: {$th_wishlist_option['th_wishlist_shr_fb_color']};
	}
	.thw-table-custom-style .thw-social-share a.thw-share-facebook:hover {
		color: {$th_wishlist_option['th_wishlist_shr_fb_hvr_color']};
	}
	";

	// Twitter (X)
	$custom_css .= "
	.thw-table-custom-style .thw-social-share a.thw-share-twitter {
		color: {$th_wishlist_option['th_wishlist_shr_x_color']};
	}
	.thw-table-custom-style .thw-social-share a.thw-share-twitter:hover {
		color: {$th_wishlist_option['th_wishlist_shr_x_hvr_color']};
	}
	";

	// WhatsApp
	$custom_css .= "
	.thw-table-custom-style .thw-social-share a.thw-share-whatsapp {
		color: {$th_wishlist_option['th_wishlist_shr_w_color']};
	}
	.thw-table-custom-style .thw-social-share a.thw-share-whatsapp:hover {
		color: {$th_wishlist_option['th_wishlist_shr_w_hvr_color']};
	}
	";

	// Email
	$custom_css .= "
	.thw-table-custom-style .thw-social-share a.thw-share-email {
		color: {$th_wishlist_option['th_wishlist_shr_e_color']};
	}
	.thw-table-custom-style .thw-social-share a.thw-share-email:hover {
		color: {$th_wishlist_option['th_wishlist_shr_e_hvr_color']};
	}
	";

	// Copy URL
	$custom_css .= "
	.thw-table-custom-style .thw-social-share a.thw-copy-link-button {
		color: {$th_wishlist_option['th_wishlist_shr_c_color']};
	}
	.thw-table-custom-style .thw-social-share a.thw-copy-link-button:hover {
		color: {$th_wishlist_option['th_wishlist_shr_c_hvr_color']};
	}
	";
	return $custom_css;
}