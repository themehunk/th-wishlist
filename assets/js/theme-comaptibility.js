(function ($) {
    'use strict';

    function thWrapThemeActions() {

        $('.woocommerce .products .product').each(function () {

            var $product = $(this);

            // Already processed.
            if ( $product.children('.th-theme-actions-wrapper').length ) {
                return;
            }

            var $actions = $product.children('.th-theme-action');

            // Wrapper sirf tab jab 2 ya usse zyada actions hon.
            if ( $actions.length < 2 ) {
                return;
            }

            var $wrapper = $('<div/>', {
                class: 'th-theme-actions-wrapper'
            });

            // Wishlist pehle.
            $product.children('.thw-add-to-wishlist-button-wrap.th-theme-action').appendTo($wrapper);

            // Compare baad me.
            $product.children('.thunk-compare.th-theme-action').appendTo($wrapper);

            // Agar future me aur actions aaye to unhe bhi append kar do.
            $product.children('.th-theme-action').appendTo($wrapper);

            $product.append($wrapper);

        });
    }

    $(thWrapThemeActions);

    $(document.body).on(
        'updated_wc_div added_to_cart removed_from_cart wc_fragments_loaded',
        thWrapThemeActions
    );

    $(document).ajaxComplete(thWrapThemeActions);

})(jQuery);