<?php

$config = [
    'model' => [
        Mollie_Model_Payment_Online_Mollie::PAYMENT_METHOD => [
            'class'       => Mollie_Model_Payment_Online_Mollie::class,
            'title'       => 'Credit Card',
            'description' => 'Credit Card Payment',
            'api_key'     => '',
            'is_enabled'  => true,
        ]
    ],

    'page' => [
        '/payment/mollie/webhook/' => [
            'class'    => Mollie_Page_Webhook::class,
            'template' => null,
        ]
    ],

    'helper' => [
        'payment' => [
            'payment_methods' => [
                Mollie_Model_Payment_Online_Mollie::PAYMENT_METHOD => 100,
            ],
        ],
    ],

    'block' => [
        Mollie_Model_Payment_Online_Mollie::PAYMENT_METHOD . '.payment.info' => [
            'template' => 'Mollie_Payment::block/mollie/info.twig',
        ],
        Mollie_Model_Payment_Online_Mollie::PAYMENT_METHOD . '.payment.complete' => [
            'template' => 'Mollie_Payment::block/mollie/complete.twig',
        ],
    ],

];