<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Address;

use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

class ShippingMethodsDataProvider
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * AddressDataProvider constructor.
     *
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param ShippingMethodConverter $shippingMethodConverter
     */
    public function __construct(
        ExtensibleDataObjectConverter $dataObjectConverter,
        ShippingMethodConverter $shippingMethodConverter
    ) {
        $this->dataObjectConverter = $dataObjectConverter;
        $this->shippingMethodConverter = $shippingMethodConverter;
    }

    public function getAvailableShippingMethods(QuoteAddress $address): array
    {
        $methods = [];

        // Allow shipping rates by setting country id for new addresses
        if (!$address->getCountryId() && $address->getCountryCode()) {
            $address->setCountryId($address->getCountryCode());
        }

        $address->setCollectShippingRates(true);
        $address->collectShippingRates();
        $cart = $address->getQuote();

        $shippingRates = $address->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $methods[] = $this->dataObjectConverter->toFlatArray(
                    $this->shippingMethodConverter->modelToDataObject($rate, $cart->getQuoteCurrencyCode()),
                    [],
                    ShippingMethodInterface::class
                );
            }
        }

        return $methods;
    }
}
