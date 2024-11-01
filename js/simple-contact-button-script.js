jQuery(document).ready(function($) {
    function loadSpecificPosts(postType, callback) {
        $('#loading_indicator').addClass('visible');
        $.ajax({
            url: simpleContactButton.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_specific_posts',
                post_type: postType,
                _ajax_nonce: simpleContactButton.nonce
            },
            success: function(response) {
                $('#loading_indicator').removeClass('visible');
                if (response.success) {
                    $('#specific_post_dropdown').html(response.data);

                    // Set the selected value to savedPageId
                    if (simpleContactButton.savedPageId) {
                        $('#specific_post_dropdown').val(simpleContactButton.savedPageId);
                    }

                    if (callback) callback();
                } else {
                    $('#specific_post_dropdown').html('<option value="">' + simpleContactButton.errorLoadingPosts + '</option>');
                }
            },
            error: function() {
                $('#loading_indicator').removeClass('visible');
                $('#specific_post_dropdown').html('<option value="">' + simpleContactButton.errorLoadingPosts + '</option>');
            }
        });
    }

    // Event handler for 'Button Activation' dropdown change
    $('#button_activation').change(function() {
        const activationValue = $(this).val();
        if (activationValue === 'specific') {
            $('#specific_pages_section, #specific_post_dropdown_section').show();

            // Load posts if a post type is already selected
            if ($('#post_type_dropdown').val()) {
                loadSpecificPosts($('#post_type_dropdown').val());
            }
        } else {
            $('#specific_pages_section, #specific_post_dropdown_section').hide();
            // Reset post type and specific post when switching back to sitewide or homepage
            $('#post_type_dropdown').val('');
            $('#specific_post_dropdown').html('');
        }
    });

    // Event handler for 'Post Type' dropdown change
    $('#post_type_dropdown').change(function() {
        const postType = $(this).val();
        if (postType) {
            loadSpecificPosts(postType);
        } else {
            $('#specific_post_dropdown').html('');
        }
    });

    // **Initial Load Logic**
    // Load saved values on page load
    const activationValue = simpleContactButton.activationSetting;
    if (activationValue === 'specific') {
        $('#button_activation').val('specific');
        $('#specific_pages_section, #specific_post_dropdown_section').show();

        if (simpleContactButton.savedPostType) {
            $('#post_type_dropdown').val(simpleContactButton.savedPostType);

            // Load posts and set the selected page/post
            loadSpecificPosts(simpleContactButton.savedPostType, function() {
                if (simpleContactButton.savedPageId) {
                    $('#specific_post_dropdown').val(simpleContactButton.savedPageId);
                }
            });
        }
    } else {
        // Set the 'Button Activation' dropdown to the saved value ('sitewide' or 'homepage')
        $('#button_activation').val(activationValue);

        // Hide sections if activation setting is not 'specific'
        $('#specific_pages_section, #specific_post_dropdown_section').hide();
    }
});