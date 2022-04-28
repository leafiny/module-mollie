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
 * Class Catalog_Page_Product_View
 */
class Mollie_Page_Webhook extends Core_Page
{
    /**
     * Execute action
     *
     * @return void
     */
    public function action(): void
    {
        parent::action();

        /** @var Mollie_Model_Payment_Online_Mollie $model */
        $model = App::getObject('model', Mollie_Model_Payment_Online_Mollie::PAYMENT_METHOD);
        $model->processResponse($this->getPost());
    }
}
