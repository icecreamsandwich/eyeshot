<?php

/**
 * Settings for MyFatoorah Gateway.
 */
$countries_obj = new WC_Countries();
$countries     = $countries_obj->get_allowed_countries();
return array(
    'enabled'            => array(
        'title'   => __('Enable/Disable', 'woocommerce'),
        'type'    => 'checkbox',
        'default' => 'no',
        'label'   => __('Enable MyFatoorah Shipping', 'myfatoorah-woocommerce'),
    ),
    'title'              => array(
        'title'             => __('Title', 'woocommerce'),
        'type'              => 'text',
        'description'       => __('Title to be display on site', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => __('MyFatoorah Shipping', 'myfatoorah-woocommerce'),
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'shipping'           => array(
        'title'   => __('Shipping Methods', 'myfatoorah-woocommerce') . ' <font style="color:red;">*</font>',
        'type'    => 'multiselect',
        'label'   => __('DHL / Aramex', 'myfatoorah-woocommerce'),
        'options' => array(1 => 'DHL', 2 => 'Aramex'),
    ),
    'exe_ship_countries' => array(
        'title'       => __('Exclude countries from shipping rates', 'myfatoorah-woocommerce'),
        'type'        => 'multiselect',
        'description' => __('Exclude countries from MyFatoorah shipping rates.', 'myfatoorah-woocommerce'),
        'default'     => '',
        'desc_tip'    => true,
        'options'     => $countries,
    ),
);
