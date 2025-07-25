jQuery(function($) {
    // Add to wishlist
    $(document).on('click', '.thw-add-to-wishlist-button:not(.thw-login-required)', function(e) {
        e.preventDefault();

        var $button = $(this);
        var product_id = $button.data('product-id');
        var variation_id = $button.data('variation-id');

        if ($button.hasClass('in-wishlist')) {
            if (thwl_wishlist_params.wishlist_page_url) {
                window.location.href = thwl_wishlist_params.wishlist_page_url;
            }
            return;
        }

        $.ajax({
            type: 'POST',
            url: thwl_wishlist_params.ajax_url,
            data: { action: 'thwl_add_to_wishlist', nonce: thwl_wishlist_params.add_nonce, product_id: product_id, variation_id: variation_id },
            beforeSend: function() { $button.addClass('loading'); },
            success: function(response) {
                if (response.success) {
                    if (thwl_wishlist_params.icon_style !== 'icon') {
                    $button.find('span').last().text(thwl_wishlist_params.i18n_added);
                    }
                    $button.addClass('in-wishlist');
                    // Update SVG icon based on wishlist status
                        if (['icon', 'icon_text', 'icon_only_no_style'].includes(thwl_wishlist_params.icon_style)) {
                            // Assume thwl_wishlist_params.icons contains the SVG icon data (e.g., from PHP)
                            var icons = thwl_wishlist_params.icons; // Ensure this is passed from PHP to JS
                            var selected_brwsicon = thwl_wishlist_params.th_wishlist_brws_icon || 'heart-filled'; // Default to heart-filled
                            var icon_html = '<span class="thw-icon browse">' + (icons[selected_brwsicon]?.svg || icons['heart-filled'].svg) + '</span>';

                            // Replace the current icon with the browse wishlist icon
                            $button.find('.thw-icon').replaceWith(icon_html);
                        }
                } else {
                    alert(thwl_wishlist_params.i18n_error);
                }
            },
            complete: function() { $button.removeClass('loading'); }
        });
    });

    // Remove from wishlist
    $(document).on('click', '.thw-remove-item', function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        var item_id = $row.data('item-id');

        $.ajax({
            type: 'POST',
            url: thwl_wishlist_params.ajax_url,
            data: { action: 'thwl_remove_from_wishlist', nonce: thwl_wishlist_params.remove_nonce, item_id: item_id },
            beforeSend: function() { $row.css('opacity', '0.5'); },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        if ($row.siblings().length === 0) {
                            var colspan = $row.children().length;
                            $row.closest('tbody').html('<tr><td colspan="' + colspan + '">' + thwl_wishlist_params.i18n_empty_wishlist + '</td></tr>');
                        }
                        $row.remove();
                    });
                } else {
                     $row.css('opacity', '1');
                }
            }
        });
    });

    // Update quantity
    $(document).on('change', '.thw-qty', function() {
        var $input = $(this);
        var item_id = $input.data('item-id');
        var quantity = $input.val();
        // Update WooCommerce add_to_cart_button data-quantity
        var $row = $input.closest('tr');
        var $button = $row.find('.add_to_cart_button');

        if ($button.length) {
            $button.attr('data-quantity', quantity);
        }

        $.post(thwl_wishlist_params.ajax_url, { action: 'thwl_update_item_quantity', nonce: thwl_wishlist_params.update_qty_nonce, item_id: item_id, quantity: quantity });
    });

    // Select/Deselect all
    $('#thw-select-all').on('change', function() {
        $('.thw-wishlist-table tbody input[type="checkbox"]').prop('checked', this.checked);
    });

    // Add all selected to cart
    $('.thw-add-all-to-cart').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var items = [];
        $('.thw-wishlist-table tbody input[type="checkbox"]:checked').each(function() {
            items.push($(this).val());
        });

        if (items.length === 0) {
            alert('Please select products to add to cart.');
            return;
        }

        $.ajax({
            type: 'POST',
            url: thwl_wishlist_params.ajax_url,
            data: { action: 'thwl_add_all_to_cart', nonce: thwl_wishlist_params.add_all_nonce, items: items },
            beforeSend: function() { $button.addClass('loading'); },
            success: function() {
                if (thwl_wishlist_params.redirect_to_cart) {
                    window.location.href = thwl_wishlist_params.cart_url;
                } else {
                    $button.text('Added to Cart!');
                }
            },
            complete: function() { $button.removeClass('loading'); }
        });
    });

    // Copy link
    $(document).on('click', '.thw-copy-link-button', function() {
        var link = $(this).data('link');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(link).select();
        document.execCommand("copy");
        $temp.remove();
        alert('Wishlist link copied to clipboard!');
    });

    // redirect to cart or remove code

    $(document).on('click', '.thw-add-to-cart-ajax', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const product_id = $btn.data('product-id');
        const quantity = $btn.data('quantity') || 1;
        const item_id = $btn.data('item-id');
        const token = $btn.data('wishlist-token');
        const $row = $('tr[data-item-id="' + item_id + '"]');
        // Optional: disable button while processing
        $btn.prop('disabled', true).addClass('loading');
        $.ajax({
            type: 'POST',
            url: thwl_wishlist_params.ajax_url,
            data: {
                action: 'thwl_add_to_cart_and_manage',
                product_id,
                quantity,
                item_id,
                token,
                nonce: thwl_wishlist_params.redirect_nonce
            },
            success: function (response) {
                if (response.success) {
                    $row.fadeOut(300, function () {
                        const $tbody = $row.closest('tbody');
                        $row.remove();
                        // Check if this was the last item
                        if ($tbody.find('tr').length === 0) {
                            const colspan = $btn.closest('table').find('thead th').length;
                            $tbody.html('<tr><td colspan="' + colspan + '">' + thwl_wishlist_params.i18n_empty_wishlist + '</td></tr>');
                        }
                        // Redirect only after animation (optional)
                        if (thwl_wishlist_params.redirect_to_cart) {
                            window.location.href = thwl_wishlist_params.cart_url;
                        }
                    });
                } else {
                    $btn.prop('disabled', false).removeClass('loading');
                    alert(response.data.message || 'Error adding to cart.');
                }
            },
            error: function () {
                $btn.prop('disabled', false).removeClass('loading');
                alert('Something went wrong.');
            }
        });
    });
});
