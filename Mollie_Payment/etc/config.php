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
        ],
        '/payment/mollie/complete/' => [
            'class'      => Mollie_Page_Complete::class,
            'template'   => 'Mollie_Payment::page/redirect.twig',
            'content'    => 'Mollie_Payment::page/complete/redirect.twig',
            'meta_title' => 'Payment Complete',
            'javascript' => [
                'Mollie_Payment::js/redirect.js' => 10,
            ],
        ],
        '/payment/mollie/complete/redirect/' => [
            'class'    => Mollie_Page_Complete_Redirect::class,
            'template' => null,
        ],
    ],

    'helper' => [
        'payment' => [
            'payment_methods' => [
                Mollie_Model_Payment_Online_Mollie::PAYMENT_METHOD => 100,
            ],
        ],
        'mollie' => [
            'class' => Mollie_Helper_Mollie::class,
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