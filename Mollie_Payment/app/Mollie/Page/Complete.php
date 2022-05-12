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
 * Class Mollie_Page_Complete
 */
class Mollie_Page_Complete extends Core_Page
{
    /**
     * Payment is successful
     *
     * @return void
     */
    public function action(): void
    {
        parent::action();

        $params = $this->getParams();
        $key = $params->getData('key');

        if (!$key) {
            $this->redirect();
        }

        try {
            /** @var Commerce_Model_Sale $saleModel */
            $saleModel = App::getSingleton('model', 'sale');
            $sale = $saleModel->get($key, 'key');

            $this->setCustom('key', $sale->getData('key'));
            $this->setCustom('result', $sale->getData('payment_state'));
        } catch (Throwable $throwable) {
            App::log($throwable, Core_Interface_Log::ERR);
            $this->redirect();
        }
    }
}
