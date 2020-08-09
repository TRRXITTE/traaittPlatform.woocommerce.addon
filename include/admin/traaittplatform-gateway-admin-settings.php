<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'traaittplatform_gateway'),
        'label' => __('Enable this payment gateway', 'traaittplatform_gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'traaittplatform_gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment title the customer will see during the checkout process.', 'traaittplatform_gateway'),
        'default' => __('traaittPlatform Gateway', 'traaittplatform_gateway')
    ),
    'description' => array(
        'title' => __('Description', 'traaittplatform_gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment description the customer will see during the checkout process.', 'traaittplatform_gateway'),
        'default' => __('Pay securely using traaittPlatform. You will be provided payment details after checkout.', 'traaittplatform_gateway')
    ),
    'discount' => array(
        'title' => __('Discount for using traaittPlatform', 'traaittplatform_gateway'),
        'desc_tip' => __('Provide a discount to your customers for making a private payment with traaittPlatform', 'traaittplatform_gateway'),
        'description' => __('Enter a percentage discount (i.e. 5 for 5%) or leave this empty if you do not wish to provide a discount', 'traaittplatform_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'valid_time' => array(
        'title' => __('Order valid time', 'traaittplatform_gateway'),
        'desc_tip' => __('Amount of time order is valid before expiring', 'traaittplatform_gateway'),
        'description' => __('Enter the number of seconds that the funds must be received in after order is placed. 3600 seconds = 1 hour', 'traaittplatform_gateway'),
        'type' => __('number'),
        'default' => '3600'
    ),
    'confirms' => array(
        'title' => __('Number of confirmations', 'traaittplatform_gateway'),
        'desc_tip' => __('Number of confirms a transaction must have to be valid', 'traaittplatform_gateway'),
        'description' => __('Enter the number of confirms that transactions must have. Enter 0 to zero-confim. Each confirm will take approximately four minutes', 'traaittplatform_gateway'),
        'type' => __('number'),
        'default' => '10'
    ),
    'traaittplatform_address' => array(
        'title' => __('traaittPlatform Address', 'traaittplatform_gateway'),
        'label' => __('Public traaittPlatform Address'),
        'type' => 'text',
        'desc_tip' => __('traaittPlatform Wallet Address (ETRX)', 'traaittplatform_gateway')
    ),
    'daemon_host' => array(
        'title' => __('traaittService Host/IP', 'traaittplatform_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the traaittService Host/IP to authorize the payment with', 'traaittplatform_gateway'),
        'default' => '127.0.0.1',
    ),
    'daemon_port' => array(
        'title' => __('traaittService Port', 'traaittplatform_gateway'),
        'type' => __('number'),
        'desc_tip' => __('This is the traaittService port to authorize the payment with', 'traaittplatform_gateway'),
        'default' => '8070',
    ),
    'daemon_password' => array(
        'title' => __('traaittService Password', 'traaittplatform_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the traaittService password to authorize the payment with', 'traaittplatform_gateway'),
        'default' => '',
    ),
    'show_qr' => array(
        'title' => __('Show QR Code', 'traaittplatform_gateway'),
        'label' => __('Show QR Code', 'traaittplatform_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to show a QR code after checkout with payment details.'),
        'default' => 'no'
    ),
    'use_traaittplatform_price' => array(
        'title' => __('Show Prices in traaittPlatform', 'traaittplatform_gateway'),
        'label' => __('Show Prices in traaittPlatform', 'traaittplatform_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to convert ALL prices on the frontend to traaittPlatform (experimental)'),
        'default' => 'no'
    ),
    'use_traaittplatform_price_decimals' => array(
        'title' => __('Display Decimals', 'traaittplatform_gateway'),
        'type' => __('number'),
        'description' => __('Number of decimal places to display on frontend. Upon checkout exact price will be displayed.'),
        'default' => 2,
    ),
);
