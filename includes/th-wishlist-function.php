<?php 

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'th_is_wc_block_template' ) ) {

	function th_is_wc_block_template( string $template_name ): bool {
        
		static $cache = array();

		if ( isset( $cache[ $template_name ] ) ) {
			return $cache[ $template_name ];
		}

		// Default false
		$cache[ $template_name ] = false;

		// WooCommerce >= 7.9 and block theme required
		if ( ! function_exists( 'WC' ) || version_compare( WC()->version, '7.9.0', '<' ) || ! wp_is_block_theme() ) {
			return false;
		}

		// Default blockified templates from WC 7.9+
		$blockified_templates = array(
			'archive-product',
			'product-search-results',
			'single-product',
			'taxonomy-product_attribute',
			'taxonomy-product_cat',
			'taxonomy-product_tag',
			'cart',
			'checkout',
		);

		$templates = get_block_templates( array( 'slug__in' => array( $template_name ) ) );

		$is_block_template = function ( string $content ) use ( $template_name ): bool {
			switch ( $template_name ) {
				case 'cart':
					return has_block( 'woocommerce/cart', $content );
				case 'checkout':
					return has_block( 'woocommerce/checkout', $content );
				default:
					return ! has_block( 'woocommerce/legacy-template', $content );
			}
		};

		// If the template exists
		if ( ! empty( $templates ) && isset( $templates[0]->content ) ) {
			$content = $templates[0]->content;

			if ( ! $is_block_template( $content ) ) {
				return $cache[ $template_name ] = false;
			}

			// Check for patterns embedded inside
			if ( has_block( 'core/pattern', $content ) ) {
				foreach ( parse_blocks( $content ) as $block ) {
					if ( $block['blockName'] === 'core/pattern' ) {
						$slug = $block['attrs']['slug'] ?? '';
						if ( $slug && WP_Block_Patterns_Registry::get_instance()->is_registered( $slug ) ) {
							$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
							if ( isset( $pattern['content'] ) && ! $is_block_template( $pattern['content'] ) ) {
								return $cache[ $template_name ] = false;
							}
						}
					}
				}
			}

			// Valid block template
			return $cache[ $template_name ] = true;

		} elseif ( in_array( $template_name, $blockified_templates, true ) ) {
			// Template is blockified by default
			return $cache[ $template_name ] = true;
		}

		return $cache[ $template_name ];
	}
}

// get icon
function thw_get_wishlist_icons_svg() {
    $addicondashicons = array(
        'heart-outline' => array(
            'name' => 'Heart Outline',
            'svg' => '<svg class="th-wishlist-icon-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>'
        ),
        'heart-filled' => array(
            'name' => 'Heart Filled',
            'svg' => '<svg class="th-wishlist-icon-svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>'
        ),
        'star-outline' => array(
            'name' => 'Star Outline',
            'svg' => '<svg class="th-wishlist-icon-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21L12 17.77L5.82 21L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>'
        ),
        'star-filled' => array(
            'name' => 'Star Filled',
            'svg' => '<svg class="th-wishlist-icon-svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21L12 17.77L5.82 21L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>'
        ),
        'bookmark-outline' => array(
            'name' => 'Bookmark Outline',
            'svg' => '<svg class="th-wishlist-icon-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M6.32 2.577c2.83-.33 5.66-.33 8.49 0 1.497.174 2.57 1.46 2.57 2.93V21l-6.165-3.583-7.165 3.583V5.507c0-1.47 1.073-2.756 2.57-2.93Z"/></svg>'
        ),
        'bookmark-filled' => array(
            'name' => 'Bookmark Filled',
            'svg' => '<svg class="th-wishlist-icon-svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0 1 11.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 0 1-1.085.67L12 18.089l-7.165 3.583A.75.75 0 0 1 3.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93Z"/></svg>'
        ),
    );
    return $addicondashicons;
}
/**
 * Sanitizes and outputs SVG markup for safe rendering in WordPress.
 *
 * @param string $svg        The SVG markup to sanitize.
 * @param string $text_domain The text domain for translations (optional).
 * @return string Sanitized SVG markup or fallback message.
 */
function thw_sanitize_svg_output( $svg, $text_domain = 'th-wishlist' ) {
    // Check if SVG data exists
    if ( ! empty( $svg ) ) {
        // Define allowed SVG tags and attributes
        $allowed_svg_tags = array(
            'svg'  => array(
                'class'        => true,
                'width'        => true,
                'height'       => true,
                'viewbox'      => true,
                'fill'         => true,
                'stroke'       => true,
                'stroke-width' => true,
                'xmlns'        => true,
            ),
            'path' => array(
                'd'              => true,
                'fill'           => true,
                'stroke'         => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'clip-rule'      => true,
                'fill-rule'      => true,
            ),
        );

        // Sanitize and return SVG
        return wp_kses( $svg, $allowed_svg_tags );
    }

    // Fallback for missing SVG
    return esc_html__( 'No icon available', $text_domain );
}
