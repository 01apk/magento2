<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Provides QTY buy request data for adding products to cart
 */
class QuantityDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $qty = $this->arrayManager->get('data/quantity', $cartItemData);
        if (!isset($qty)) {
            throw new GraphQlInputException(__('Missing key "quantity" in cart item data'));
        }
        $qty = (float)$qty;

        if ($qty <= 0) {
            throw new GraphQlInputException(
                __('Please enter a number greater than 0 in this field.')
            );
        }

        return ['qty' => $qty];
    }
}
