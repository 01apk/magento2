<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\Order;

class OrderTotals implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Order $orderModel */
        $orderModel = $value['model'];
        $currency = $orderModel->getOrderCurrencyCode();
        $totals = [
                'base_grand_total' => ['value' => $orderModel->getBaseGrandTotal(), 'currency' => $currency],
                'grand_total' => ['value' =>  $orderModel->getGrandTotal(), 'currency' => $currency],
                'subtotal' => ['value' =>  $orderModel->getSubtotal(), 'currency' => $currency],
                'tax' => ['value' =>  $orderModel->getTaxAmount(), 'currency' => $currency],
                'shipping_handling' => ['value' => $orderModel->getShippingAmount(), 'currency' => $currency]
        ];
        return $totals;
    }
}
