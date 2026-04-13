// AJAX Product live search functionality
jQuery(document).ready(function($) {
    
    $('#product-search-input').on('input', function () {
        let search = $(this).val().trim();
        let category = $('#selected-product-cat').val();

        if (search.length > 0) {
            $.ajax({
                url: newscrunch_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'newscrunch_live_product_search',
                    keyword: search,
                    category: category
                },
                beforeSend: function () {
                    $('.product-search-results-container').show();
                    $('.product-search-results-container').html('<div class="searching-text">' + newscrunch_ajax.searching_text + '</div>');
                },
                success: function (response) {
                    $('.product-search-results-container').html(response);
                },
                error: function() {
                    $('.product-search-results-container').empty();
                }
            });
        } else {
            // Clear and hide results when input is empty
            $('.product-search-results-container').empty().hide();
        }
    });

    // Ensure container is hidden by default when page loads
    $('.product-search-results-container').hide();
});