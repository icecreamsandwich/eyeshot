(function ($) {
    $(document).ready(function () {

        //important to update the date if the city is change
        if ($('#billing_city').prop('type') === 'select-one') {
            bindSelectWoo("billing");
        }
        $('#billing_city').change(function () {
            $('body').trigger('update_checkout');
        });


        if ($('#shipping_city').prop('type') === 'select-one') {
            bindSelectWoo("shipping");
        }
        $('#shipping_city').change(function () {
            $('body').trigger('update_checkout');
        });



        //change countries
        $('#billing_country').change(function () {
            callAjax('billing', $(this).val());
        });
        $('#shipping_country').change(function () {
            callAjax('shipping', $(this).val());
        });


        //ajax
        function callAjax(addr, country) {
            var selector = '#' + addr + '_city';
            $(selector).val('');
            $.ajax({
                cache: false,
                type: "POST",
                url: ajax_object.ajax_url,
                data: {
                    action: 'check_cities_field',
                    country_code: country
                },
                success: function (response)
                {
                    var selParent = $(selector).parent();
                    selParent.html('');
                    if (response === 'input') {
                        selParent.append('<input type="text" class="input-text " name="' + addr + '_city" id="' + addr + '_city" placeholder="" value="" autocomplete="address-level2">');
                    } else {
                        selParent.append("<select id='" + addr + "_city' name='" + addr + "_city' />");
                        $(selector).html('<option value=""> ' + response + '</option>');
                        bindSelectWoo(addr);
                    }
                    $(selector).change(function () {
                        $('body').trigger('update_checkout');
                    });

                }
            });
        }

        function bindSelectWoo(selector) {
            $('#' + selector + '_city').selectWoo({
                ajax: {
                    url: ajax_object.ajax_url,
                    dataType: 'json',
//                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term, // search term
                            country_code: $('#' + selector + '_country').val(),
                            action: 'get_cities'
                        };
                    },
                    processResults: function (data, params) {
                        if (data.success === false) {

                            $('.woocommerce-notices-wrapper').last().html('<ul class="woocommerce-error"><li>' + data.error + '</li></ul>');
                            $([document.documentElement, document.body]).animate({
                                scrollTop: $('.woocommerce-notices-wrapper').first().offset().top
                            }, 2000);

                            return {
                                results: []
                            };
                        }
                        $('.woocommerce-notices-wrapper').last().html('');

                        var terms = [];
                        if (data) {
                            $.each(data, function (id, text) {
                                terms.push({id: id, text: text});
                            });
                        }
                        return {
                            results: terms
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) {
                    return markup;
                }//let our custom formatter work
                //,minimumInputLength: 3
            });
        }

    });
}
)(jQuery);