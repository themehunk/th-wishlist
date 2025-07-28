<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Generate dynamic front-end styles
 */
function thwl_front_style() {

	$th_wishlist_option = get_option( 'thwl_settings', THWL_Settings::thwl_get_default_settings() );

	return $custom_css = "
	.thw-btn-custom-style .thw-add-to-wishlist-button .thw-icon {
	color: " . esc_html($th_wishlist_option['th_wishlist_add_icon_color']) . ";
	}
	.thw-btn-custom-style .thw-add-to-wishlist-button.in-wishlist .thw-icon {
	color: " . esc_html($th_wishlist_option['th_wishlist_brws_icon_color']) . ";
	}
	.thw-btn-custom-style .thw-add-to-wishlist-button {
	color: " . esc_html($th_wishlist_option['th_wishlist_btn_txt_color']) . ";
	background: " . esc_html($th_wishlist_option['th_wishlist_btn_bg_color']) . ";
	}
	.thw-table-custom-style .thw-wishlist-actions .thw-add-all-to-cart,
	.thw-table-custom-style .thw-add-to-cart-cell .button {
		color: " . esc_html($th_wishlist_option['th_wishlist_tb_btn_txt_color']) . ";
		background: " . esc_html($th_wishlist_option['th_wishlist_tb_btn_bg_color']) . ";
	}
	.thw-table-custom-style .thw-wishlist-table {
	    color: " . esc_html($th_wishlist_option['th_wishlist_table_txt_color']) . ";
		background: " . esc_html($th_wishlist_option['th_wishlist_table_bg_color']) . ";
	}
	.thw-table-custom-style .thw-wishlist-table th,
	.thw-table-custom-style .thw-wishlist-table td {
		border-color: " . esc_html($th_wishlist_option['th_wishlist_table_brd_color']) . ";
	}
		.thw-table-custom-style .thw-social-share a.thw-share-facebook {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_fb_color']) . ";
	}
	.thw-table-custom-style .thw-social-share a.thw-share-facebook:hover {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_fb_hvr_color']) . ";
	}
		.thw-table-custom-style .thw-social-share a.thw-share-twitter {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_x_color']) . ";
	}
	.thw-table-custom-style .thw-social-share a.thw-share-twitter:hover {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_x_hvr_color']) . ";
	}
		.thw-table-custom-style .thw-social-share a.thw-share-twitter {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_x_color']) . ";
	}
	.thw-table-custom-style .thw-social-share a.thw-share-twitter:hover {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_x_hvr_color']) . ";
	}
		.thw-table-custom-style .thw-social-share a.thw-share-whatsapp {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_w_color']) . ";
	}
	.thw-table-custom-style .thw-social-share a.thw-share-whatsapp:hover {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_w_hvr_color']) . ";
	}
		.thw-table-custom-style .thw-social-share a.thw-share-email {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_e_color']) . ";
	}
	.thw-table-custom-style .thw-social-share a.thw-share-email:hover {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_e_hvr_color']) . ";
	}
		.thw-table-custom-style .thw-social-share a.thw-copy-link-button {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_c_color']) . ";
	}
	.thw-table-custom-style .thw-social-share a.thw-copy-link-button:hover {
		color: " . esc_html($th_wishlist_option['th_wishlist_shr_c_hvr_color']) . ";
	}
	";
}