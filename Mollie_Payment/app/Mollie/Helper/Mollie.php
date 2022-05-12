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
 * Mollie_Helper_Mollie
 */
class Mollie_Helper_Mollie extends Core_Helper
{
    public const PAYMENT_RESULT_PAID = 'paid';

    public const PAYMENT_RESULT_PENDING = 'pending';

    public const PAYMENT_RESULT_OPEN = 'open';

    public const PAYMENT_RESULT_FAILED = 'failed';

    public const PAYMENT_RESULT_EXPIRED = 'expired';

    public const PAYMENT_RESULT_CANCELED = 'canceled';

    public const PAYMENT_RESULT_REFUNDS = 'refunds';

    public const PAYMENT_RESULT_CHARGEBACKS = 'chargebacks';

    public const PAYMENT_RESULT_UNKNOWN = 'unknown';
}
