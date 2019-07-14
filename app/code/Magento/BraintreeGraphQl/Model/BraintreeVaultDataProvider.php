<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BraintreeGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Format Braintree input into value expected when setting payment method
 */
class BraintreeVaultDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'braintree_cc_vault';

    /**
     * Format Braintree input into value expected when setting payment method
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array
    {
        if (!isset($args[static::PATH_ADDITIONAL_DATA])) {
            throw new GraphQlInputException(
                __('Required parameter "braintree_cc_vault" for "payment_method" is missing.')
            );
        }

        return $args[static::PATH_ADDITIONAL_DATA];
    }
}
