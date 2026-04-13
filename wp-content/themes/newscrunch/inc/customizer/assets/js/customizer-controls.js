(function($) {
    wp.customize.bind('ready', function() {

        function toggleSearchSettings(layout) {
            
            var isLayout10 = (layout === '10');

            // Controls that should be hidden when layout == 10
            ['hide_show_search_icon', 'select_search_layout', 'hide_show_live_search'].forEach(function(controlId) {
                var control = wp.customize.control(controlId);
                if (control) {
                    control.active.set(!isLayout10);
                    control.container.closest('.customize-control').toggle(!isLayout10);
                }
            });

            // Special case: 'hide_show_product_search' should ONLY be shown when layout == 10
            var productSearchControl = wp.customize.control('hide_show_product_search');
            if (productSearchControl) {
                productSearchControl.active.set(isLayout10);
                productSearchControl.container.closest('.customize-control').toggle(isLayout10);
            }

        }

        // Apply toggle on initial load
        setTimeout(function() {
            var initialLayout = wp.customize('header_layout').get();
            toggleSearchSettings(initialLayout);
        }, 300);

        // Apply toggle whenever header_layout changes
        wp.customize('header_layout', function(setting) {
            setting.bind(function(newVal) {
                toggleSearchSettings(newVal);
            });
        });

    });
})(jQuery);