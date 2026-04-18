<?php

/**
 * Settings for MyFatoorah Gateway.
 */
$fontSize = [];
for ($i = 2; $i <= 24; $i += 2) {
    $fontSize[$i] = $i;
}

$fontFamily = [
    ''              => __('Select Font', 'myfatoorah-woocommerce'),
    'Arial'         => 'Arial',
    'cursive'       => 'Cursive',
    'emoji'         => 'Emoji',
    'fangsong'      => 'Fangsong',
    'fantasy'       => 'Fantasy',
    'math'          => 'Math',
    'monospace'     => 'Monospace',
    'sans-serif'    => 'Sans-serif',
    'serif'         => 'Serif',
    'sofia'         => 'Sofia',
    'system-ui'     => 'System-ui',
    'tahoma'        => 'Tahoma',
    'ui-monospace'  => 'Ui-monospace',
    'ui-sans-serif' => 'Ui-sans-serif',
    'ui-serif'      => 'Ui-serif',
    'ui-rounded'    => 'Ui-rounded',
    'verdana'       => 'Verdana',
];

return [
    'enabled'          => array(
        'title'   => __('Enable/Disable', 'woocommerce'),
        'type'    => 'checkbox',
        'default' => 'no',
        'label'   => __('Enable MyFatoorah', 'myfatoorah-woocommerce'),
    ),
    'configuration'    => [
        'title' => __('Merchant Configurations', 'myfatoorah-woocommerce'),
        'type'  => 'title'
    ],
    'countryMode'      => array(
        'title'             => __('Vendor\'s Country', 'myfatoorah-woocommerce') . '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'              => 'select',
        'description'       => $this->getMfDesWithIcon(__('Select your MyFatoorah vendor\'s country. After that, use the API token key that belongs to this country.', 'myfatoorah-woocommerce')),
        'default'           => 'KWT',
        'options'           => $this->mfCountries,
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'testMode'         => array(
        'title'       => __('Test Mode', 'myfatoorah-woocommerce'),
        'type'        => 'checkbox',
        'description' => $this->getMfDesWithIcon(__('Select Test Mode checkbox only when using test API Key.', 'myfatoorah-woocommerce')),
        'default'     => 'yes',
        'label'       => __('Enable Test Mode', 'myfatoorah-woocommerce'),
    ),
    'apiKey'           => array(
        'title'             => __('API Key', 'myfatoorah-woocommerce') . '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'              => 'textarea',
        'description'       => __('Get your API Token Key from MyFatoorah Vendor Account.', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'webhookSecretKey' => array(
        'title'             => __('Webhook Secret Key', 'myfatoorah-woocommerce'),
        'type'              => 'text',
        'description'       => $this->getWebhookDesc(),
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'supplierCode'     => array(
        'title'             => __('MyFatoorah Supplier Code', 'myfatoorah-woocommerce'),
        'description'       => $this->getMfDesWithIcon(__('Add a valid and active supplier code that is created in MyFatoorah vendor account. Or, leave it empty.', 'myfatoorah-woocommerce')),
        'css'               => 'width:100px;',
        'type'              => 'number',
        'custom_attributes' => [
            'min'  => 0,
            'step' => 1,
        ],
    ),
    'options'          => [
        'title' => __('Order options', 'myfatoorah-woocommerce'),
        'type'  => 'title'
    ],
    'invoiceItems'     => array(
        'title'       => __('Invoice items', 'myfatoorah-woocommerce'),
        'type'        => 'checkbox',
        'description' => __('While disabling Invoice Items, MyFatoorah will send total order amount to the invoice page. In case of enabling MyFatoorah shipping, you should enable this option.', 'myfatoorah-woocommerce'),
        'default'     => 'yes',
        'label'       => __('Enable Invoice items', 'myfatoorah-woocommerce'),
    ),
    'orderStatus'      => array(
        'title'             => __('Order Status', 'woocommerce'),
        'type'              => 'select',
        'description'       => __('How to mark the successful payment in the Admin Orders Page.', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => 'processing',
        'options'           => [
            'processing' => __('Processing', 'woocommerce'),
            'completed'  => __('Completed', 'woocommerce'),
        ],
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'success_url'      => array(
        'title'             => __('Payment Success URL', 'myfatoorah-woocommerce'),
        'type'              => 'text',
        'description'       => __('Please insert your Success URL (optional).', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => '',
//        'placeholder' => 'https://www.example.com/success',
        'sanitize_callback' => 'sanitize_url'
    ),
    'fail_url'         => array(
        'title'             => __('Payment Fail URL', 'myfatoorah-woocommerce'),
        'type'              => 'text',
        'description'       => __('Please insert your Failed URL (optional).', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => '',
//        'placeholder' => 'https://www.example.com/failed',
        'sanitize_callback' => 'sanitize_url'
    ),
    'debug'            => array(
        'title'       => __('Debug Mode', 'myfatoorah-woocommerce'),
        'type'        => 'checkbox',
        'description' => __('Log MyFatoorah events in ', 'myfatoorah-woocommerce') . $this->pluginlog,
        'desc_tip'    => true,
        'default'     => 'yes',
        'label'       => __('Enable logging', 'myfatoorah-woocommerce'),
    ),
    'frontend'         => [
        'title' => __('Front-End', 'myfatoorah-woocommerce'),
        'type'  => 'title'
    ],
    'title'            => array(
        'title'             => __('Title', 'woocommerce'),
        'type'              => 'text',
        'description'       => __('This controls the title which the user sees during checkout.', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => __($this->method_title, 'myfatoorah-woocommerce'), //todo trans
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'description'      => array(
        'title'             => __('Description', 'woocommerce'),
        'type'              => 'textarea',
        'description'       => __('This controls the description which the user sees during checkout.', 'myfatoorah-woocommerce'),
        'desc_tip'          => true,
        'default'           => __('Checkout with MyFatoorah Payment Gateway', 'myfatoorah-woocommerce'),
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'icon'             => array(
        'title'       => __('MyFatoorah Logo URL', 'myfatoorah-woocommerce'),
        'type'        => 'text',
        'description' => __('Please insert your logo URL which the user sees during checkout.', 'myfatoorah-woocommerce'),
        'desc_tip'    => true,
        'default'     => MYFATOORAH_WOO_ASSETS_URL . '/images/myfatoorah.png',
    //'sanitize_callback' => 'sanitize_url'
    ),
    'listOptions'      => array(
        'title'             => __('List Payment Options', 'myfatoorah-woocommerce'),
        'type'              => 'select',
        'description'       => '<span id="listOptionsDesc" class="mf-hide">' . $this->getMfDesWithIcon(__('Click on save changes to synchronize the payment gateways with your MyFatoorah account.', 'myfatoorah-woocommerce', 'dashicons-update')) . '</span>',
        'default'           => 'multigateways',
        'options'           => [
            'multigateways' => __('List All Enabled Gateways in Checkout Page', 'myfatoorah-woocommerce'),
            'myfatoorah'    => __('Redirect to MyFatoorah Invoice Page', 'myfatoorah-woocommerce'),
        ],
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'design'           => [
        'title' => __('Theme', 'myfatoorah-woocommerce'),
        'type'  => 'title'
    ],
    'newDesign'        => array(
        'title'       => __('New Design', 'myfatoorah-woocommerce'),
        'type'        => 'checkbox',
        'description' => '<font color="darkgoldenrod"><span class="dashicon dashicons dashicons-remove"></span>' . __('Old Design is deprecated and it will be removed soon.', 'myfatoorah-woocommerce') . '</font>',
        'default'     => 'yes',
        'label'       => __('Enable New Design', 'myfatoorah-woocommerce'),
    ),
    'registerApplePay' => array(
        'title'       => __('Apple Pay Embedded', 'myfatoorah-woocommerce'),
        'type'        => 'checkbox',
        'description' => $this->getMfDesWithIcon(__('Create a folder named ".well-known" in the root path and copy the apple-developer-merchantid-domain-association file which you received from MyFatoorah support team (tech@myfatoorah.com)', 'myfatoorah-woocommerce')),
        'default'     => 'no',
        'label'       => __('Enable Apple Pay Embedded', 'myfatoorah-woocommerce'),
    ),
    'saveCard'         => array(
        'title'       => __('Save Card Information', 'myfatoorah-woocommerce'),
        'type'        => 'checkbox',
        'description' => __('This feature allows the customers to save their card details for the future payments.', 'myfatoorah-woocommerce'),
        'desc_tip'    => true,
        'default'     => 'no',
        'label'       => __('Enable MyFatoorah save card information feature for logged in users', 'myfatoorah-woocommerce'),
    ),
    'cardIcons'        => array(
        'title' => __('Embedded Icons', 'myfatoorah-woocommerce'),
        'type'  => 'checkbox',
        'label' => __('Hide Embedded Payment Icons', 'myfatoorah-woocommerce'),
    ),
    'theme'            => [
        'title'       => __('Theme styling', 'myfatoorah-woocommerce'),
        'type'        => 'title',
        'description' => "<br/>" . __('The following options affect how the front-end design will be displayed.', 'myfatoorah-woocommerce')
    ],
    'resetTheme'       => [
        'type'        => 'hidden',
        'title'       => __('Back to default theme', 'myfatoorah-woocommerce'),
        'description' => '<span style="color:#0093c9; cursor:pointer;"  id="mf_reset_icon" > <span class="dashicons dashicons-update" style="padding-left:10px"></span> ' . __('Click to reset theme', 'myfatoorah-woocommerce') . '</span>',
    ],
    'designFont'       => array(
        'title'             => __('Font Family', 'myfatoorah-woocommerce'),
        'type'              => 'select',
        'css'               => 'width:150px;',
        'options'           => $fontFamily,
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'designFontSize'   => array(
        'title'             => __('Font Size', 'myfatoorah-woocommerce'),
        'type'              => 'select',
        'css'               => 'width:150px;',
        'default'           => '12',
        'options'           => $fontSize,
        'sanitize_callback' => 'sanitize_text_field'
    ),
    'designColor'      => array(
        'title'             => __('Text Color', 'myfatoorah-woocommerce'),
        'type'              => 'text',
        'css'               => 'width:150px;',
        'default'           => '#888484',
        'placeholder'       => '#7abbff',
        'sanitize_callback' => 'sanitize_hex_color'
    ),
    'themeColor'       => array(
        'title'             => __('Theme Color', 'myfatoorah-woocommerce'),
        'type'              => 'text',
        'css'               => 'width:150px;',
        'default'           => '#0293cc',
        'placeholder'       => '#7abbff',
        'sanitize_callback' => 'sanitize_hex_color'
    ),
];
