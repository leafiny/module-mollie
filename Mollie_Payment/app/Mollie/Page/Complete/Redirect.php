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
 * Class Mollie_Page_Complete_Redirect
 */
class Mollie_Page_Complete_Redirect extends Core_Page
{
    /**
     * Redirect the customer when returning to the website
     * This is due to the strict cookie policy. We have to restore the session.
     *
     * @return void
     */
    public function action(): void
    {
        parent::action();

        $params = $this->getParams();
        $result = $params->getData('result');
        $key = $params->getData('key');

        if (!$result || !$key) {
            $this->redirect();
        }

        /** @var Commerce_Helper_Checkout $helperCheckout */
        $helperCheckout = App::getSingleton('helper', 'checkout');

        $this->init();

        if ($result === Mollie_Helper_Mollie::PAYMENT_RESULT_CANCELED) {
            $this->setErrorMessage(
                App::translate('The payment has been canceled. Please try again or choose another payment method.')
            );
            $this->redirect($this->getUrl($helperCheckout->getStepUrl()));
        }

        if ($result === Mollie_Helper_Mollie::PAYMENT_RESULT_FAILED) {
            $this->setErrorMessage(
                App::translate('The payment was declined by the payment gateway. Please try again or choose another payment method.')
            );
            $this->redirect($this->getUrl($helperCheckout->getStepUrl()));
        }

        if ($result === Mollie_Helper_Mollie::PAYMENT_RESULT_EXPIRED) {
            $this->setErrorMessage(
                App::translate('The payment has expired. Please try again or choose another payment method.')
            );
            $this->redirect($this->getUrl($helperCheckout->getStepUrl()));
        }

        if ($result === Mollie_Helper_Mollie::PAYMENT_RESULT_PAID) {
            $this->redirect(
                $this->getUrl('/checkout/order/complete/') . '?key=' . $key
            );
        }

        $this->redirect();
    }

    /**
     * Reassign sale and customer to the session
     *
     * @return void
     */
    protected function init(): void
    {
        try {
            $session = App::getSession(Core_Template_Abstract::CONTEXT_DEFAULT);

            if (!$session) {
                return;
            }

            $session->set('sale_id', null);

            $params = $this->getParams();
            $key = $params->getData('key');

            if (!$key) {
                return;
            }

            /** @var Commerce_Model_Sale $saleModel */
            $saleModel = App::getSingleton('model', 'sale');
            $sale = $saleModel->get($key, 'key');

            if ($sale->getData('customer_id')) {
                $session->set('customer_id', $sale->getData('customer_id'));
            }

            if ($sale->getData('state') === Commerce_Model_Sale::SALE_STATE_CART) {
                $session->set('sale_id', $sale->getData('sale_id'));
            }
        } catch (Throwable $throwable) {
            App::log($throwable, Core_Interface_Log::ERR);
        }
    }
}
