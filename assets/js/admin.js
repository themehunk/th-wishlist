jQuery(document).ready(function($) {
    // Media uploader for custom icon
    $('#thw_upload_icon_button').click(function(e) {
        e.preventDefault();
        var frame = wp.media({ title: 'Upload Icon', multiple: false });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#thw_custom_icon_url').val(attachment.url);
        });
        frame.open();
    });

    // Toggle custom icon URL field visibility
    function toggleCustomIcon() {
        $('.thw-custom-icon-row').toggleClass('show', $('#thw_use_custom_icon').is(':checked'));
    }
    toggleCustomIcon();
    $('#thw_use_custom_icon').on('change', toggleCustomIcon);

    // Make columns sortable
    $('#thw-sortable-columns').sortable({
    axis: 'y',
    opacity: 0.7,
    placeholder: 'ui-state-highlight',
    update: function() {
        $('#thw-sortable-columns li').each(function() {
            var checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('name', checkbox.is(':checked') ? 'settings[th_wishlist_table_columns][]' : '');
        });
    }
    });

    // Update checkbox name based on checked state
    $('#thw-sortable-columns input[type="checkbox"]').on('change', function() {
        $(this).prop('name', $(this).is(':checked') ? 'settings[th_wishlist_table_columns][]' : '');
    });

    // Save settings via AJAX
    $('#thw-settings-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $notice = $('#thw-settings-notice');
        var data = $form.serializeArray();
        data.push({ name: 'action', value: 'th_wishlist_save_settings' });
        data.push({ name: '_wpnonce', value: $form.data('nonce') });
  
        $.ajax({
            url: thWishlistAdmin.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function() {
                $form.find('button[type="submit"]').prop('disabled', true).text('Saving...');
            },
            success: function(response) {
                $notice.removeClass('success error').addClass(response.success ? 'success' : 'error').text(response.data).show();
                setTimeout(function() { $notice.fadeOut(); }, 3000);
            },
            error: function() {
                $notice.removeClass('success').addClass('error').text(thWishlistAdmin.i18n.save_error).show();
                setTimeout(function() { $notice.fadeOut(); }, 3000);
            },
            complete: function() {
                $form.find('button[type="submit"]').prop('disabled', false).text('Save Settings');
            }
        });
    });

    // Reset settings via AJAX
    $('#thw-reset-settings').on('click', function() {
        if (!confirm(thWishlistAdmin.i18n.confirm_reset)) {
            return;
        }
        var $button = $(this);
        var $notice = $('#thw-settings-notice');
        var data = {
            action: 'th_wishlist_reset_settings',
            _wpnonce: $button.data('nonce')
        };

        $.ajax({
            url: thWishlistAdmin.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function() {
                $button.prop('disabled', true).text('Resetting...');
            },
            success: function(response) {
                $notice.removeClass('success error').addClass(response.success ? 'success' : 'error').text(response.data).show();
                if (response.success) {
                    location.reload();
                } else {
                    setTimeout(function() { $notice.fadeOut(); }, 3000);
                }
            },
            error: function() {
                $notice.removeClass('success').addClass('error').text(thWishlistAdmin.i18n.reset_error).show();
                setTimeout(function() { $notice.fadeOut(); }, 3000);
            },
            complete: function() {
                $button.prop('disabled', false).text('Reset to Defaults');
            }
        });
    });
});

jQuery(document).ready(function($) {
    function myColorPicker() {
        let value_ = this;
        const inputElement = $(value_);
        const defaultColor = inputElement.css("background-color") || 'rgba(0, 0, 0, 1)';
        const pickr = new Pickr({
            el: value_,
            useAsButton: true,
            default: defaultColor,
            theme: 'nano',
            swatches: [
                'rgba(244, 67, 54, 1)',
                'rgba(233, 30, 99, 0.95)',
                'rgba(156, 39, 176, 0.9)',
                'rgba(103, 58, 183, 0.85)',
                'rgba(63, 81, 181, 0.8)',
                'rgba(33, 150, 243, 0.75)',
                'rgba(255, 193, 7, 1)',
            ],
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    input: true,
                },
            },
        })
        .on('change', (color, instance) => {
            let color_ = color.toRGBA().toString(0);
            // Preview CSS on input element
            inputElement.css('background-color', color_);
            // Apply color to input value
            inputElement.val(color_);
            // Enable save button
            $('#submit').removeAttr('disabled');
        })
        .on('init', (instance) => {
            $(instance._root.app).addClass('visible');
        })
        .on('hide', (instance) => {
            instance._root.app.remove();
        });
    }

    // Attach Pickr to inputs with class color_picker
    $(document).on('click', 'input.th_color_picker', myColorPicker);
});