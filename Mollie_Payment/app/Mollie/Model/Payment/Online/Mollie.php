<?php
/**
 * This file is part of Leafiny.
 *
 * Copyright (C) Magentix SARL
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/**
 * Class Mollie_Model_Payment_Online_Mollie
 */
class Mollie_Model_Payment_Online_Mollie extends Payment_Model_Payment
{
    /**
     * @var string PAYMENT_METHOD
     */
    const PAYMENT_METHOD = 'mollie_cc';

    /**
     * Retrieve method name
     *
     * @return string
     */
    public function getMethod(): string
    {
        return self::PAYMENT_METHOD;
    }

    /**
     * Process payment
     *
     * @param Leafiny_Object $sale
     * @throws Exception|Throwable
     */
    public function processPayment(Leafiny_Object $sale): void
    {
        if (!$this->getApiKey()) {
            throw new Exception('Mollie API Key is undefined');
        }

        $payment = $this->getClient()->payments->create(
            [
                'amount' => [
                    'currency' => $sale->getData('sale_currency'),
                    'value'    => number_format((float)$sale->getData('incl_tax_total'), 2, '.', '')
                ],
                'description' => App::translate($this->getDescription()),
                'redirectUrl' => App::getBaseUrl(true) . 'payment/mollie/complete/?key=' . $sale->getData('key'),
                'webhookUrl'  => App::getBaseUrl(true) . 'payment/mollie/webhook/',
            ]
        );

        $paymentData = json_decode($sale->getData('payment_data'), true);

        $paymentData['payment_id'] = $payment->id;
        $paymentData['redirect'] = $payment->getCheckoutUrl();

        $sale->setData('status', Commerce_Model_Sale_Status::SALE_STATUS_PENDING_PAYMENT);
        $sale->setData('payment_title', App::translate($this->getTitle()));
        $sale->setData('payment_state', 'pending');
        $sale->setData('payment_data', json_encode($paymentData));
        $sale->setData('payment_ref', $payment->id);
    }

    /**
     * Process response
     *
     * @param Leafiny_Object $response
     */
    public function processResponse(Leafiny_Object $response): void
    {
        $paymentId = $response->getData('id');

        try {
            if (!$this->getApiKey()) {
                throw new Exception('Mollie API Key is undefined');
            }

            if (!$paymentId) {
                throw new Exception('Payment id is missing');
            }

            $payment = $this->getPayment($paymentId);
            if (!$payment) {
                throw new Exception(printf('Error retrieving payment %s', (string)$paymentId));
            }

            /** @var Commerce_Model_Sale $saleModel */
            $saleModel = App::getSingleton('model', 'sale');
            $sale = $saleModel->get($paymentId, 'payment_ref');

            if (!$sale->getData('sale_id')) {
                throw new Exception(printf('Payment ref %s not found', (string)$paymentId));
            }

            $state = $this->getPaymentState($payment);

            $sale->setData('payment_state', $state);
            $saleModel->save($sale);

            /** @var Commerce_Model_Sale_History $historyModel */
            $historyModel = App::getSingleton('model', 'sale_history');
            $historyModel->save(
                new Leafiny_Object(
                    [
                        'sale_id'     => $sale->getData('sale_id'),
                        'status_code' => Commerce_Model_Sale_Status::SALE_STATUS_PENDING,
                        'language'    => App::getLanguage(),
                        'comment'     => sprintf(
                            App::translate('The status of transaction %s is: %s'), $payment->id, App::translate($state)
                        )
                    ]
                )
            );

            if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {
                $sale->setData('status', Commerce_Model_Sale_Status::SALE_STATUS_PROCESSING);
                /** @var Commerce_Helper_Order $helper */
                $helper = App::getSingleton('helper', 'order');
                $helper->complete($sale);
            }
        } catch (Throwable $throwable) {
            App::log($throwable, Core_Interface_Log::ERR);
        }
    }

    /**
     * Retrieve payment description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->getCustom('description');
    }

    /**
     * Retrieve payment
     *
     * @param string $paymentId
     *
     * @return \Mollie\Api\Resources\Payment
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    protected function getPayment(string $paymentId): \Mollie\Api\Resources\Payment
    {
        return $this->getClient()->payments->get($paymentId);
    }

    /**
     * Retrieve Client
     *
     * @return \Mollie\Api\MollieApiClient
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    protected function getClient(): \Mollie\Api\MollieApiClient
    {
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($this->getApiKey());

        return $mollie;
    }

    /**
     * Retrieve API Key
     *
     * @return string|null
     */
    protected function getApiKey(): ?string
    {
        return $this->getCustom('api_key');
    }

    /**
     * Retrieve payment state
     *
     * @param \Mollie\Api\Resources\Payment $payment
     *
     * @return string
     */
    protected function getPaymentState(\Mollie\Api\Resources\Payment $payment): string
    {
        if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_PAID;
        }

        if ($payment->isOpen()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_OPEN;
        }

        if ($payment->isPending()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_PENDING;
        }

        if ($payment->isFailed()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_FAILED;
        }

        if ($payment->isExpired()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_EXPIRED;
        }

        if ($payment->isCanceled()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_CANCELED;
        }

        if ($payment->hasRefunds()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_REFUNDS;
        }

        if ($payment->hasChargebacks()) {
            return Mollie_Helper_Mollie::PAYMENT_RESULT_CHARGEBACKS;
        }

        return Mollie_Helper_Mollie::PAYMENT_RESULT_UNKNOWN;
    }
}
