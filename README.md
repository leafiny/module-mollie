# Mollie_Payment

The module integrates the payment with Mollie.

# Installation

Copy `Mollie_Payment` into your Leafiny `modules` directory.

We need **composer** to add **mollie/mollie-api-php** library. Run the following command:

```
composer require mollie/mollie-api-php
```

Deploy the resources if the website root is `pub`:

```
php deploy.php
```

# Dependency

**Mollie_Payment** need the native **Leafiny_Payment** module.

# Configuration

In global config file (ex: `etc/config.dev.php`), add the Mollie **api_key** in **model** configuration:

```php
$config = [
    'model' => [
        /* ... */
        Mollie_Model_Payment_Online_Mollie::PAYMENT_METHOD => [
            'api_key' => 'xxxx_xxxxxxxxxxxxxxxxxx',
        ]
    ],
    /* ... */
];
```