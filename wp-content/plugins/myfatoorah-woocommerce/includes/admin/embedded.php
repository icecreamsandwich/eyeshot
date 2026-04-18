<?php

/**
 * Settings
 */
return array(
    'enabled'     => array(
        'title'   => __('Enable/Disable', 'woocommerce'),
        'type'    => 'checkbox',
        'default' => 'no',
        'label'   => __('Enable MyFatoorah', 'myfatoorah-woocommerce'),
    ),
    'title'       => array(
        'title'             => __('Title', 'woocommerce'),
        'type'              => 'text',
        'description'       => __('This controls the title which the user sees during checkout.', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => __($this->method_title, 'myfatoorah-woocommerce'), //todo trans
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'description' => array(
        'title'             => __('Description', 'woocommerce'),
        'type'              => 'textarea',
        'description'       => __('This controls the description which the user sees during checkout.', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => __('Checkout with MyFatoorah Payment Gateway', 'myfatoorah-woocommerce'),
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'icon'        => array(
        'title'             => __('MyFatoorah Logo URL', 'myfatoorah-woocommerce'),
        'type'              => 'text',
        'description'       => __('Please insert your logo URL which the user sees during checkout.', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => MYFATOORAH_WOO_ASSETS_URL . '/images/myfatoorah.png',
        'sanitize_callback' => 'sanitize_url'
    )
);
