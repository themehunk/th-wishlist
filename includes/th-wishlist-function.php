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