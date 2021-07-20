<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Plugin;

use Magento\Quote\Model\Quote\Config;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;

/**
 * Quote Config Product Attributes Class
 */
class QuoteConfigProductAttributes
{
    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var array|null
     */
    private $activeAttributes;

    /**
     * @param RuleResource $ruleResource
     */
    public function __construct(RuleResource $ruleResource)
    {
        $this->ruleResource = $ruleResource;
    }

    /**
     * Append sales rule product attribute keys to select by quote item collection
     *
     * @param Config $subject
     * @param array $attributeKeys
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(Config $subject, array $attributeKeys): array
    {
        $attributes = $this->getActiveAttributes();

        foreach ($attributes as $attribute) {
            $attributeKeys[] = $attribute['attribute_code'];
        }

        return $attributeKeys;
    }

    /**
     * @return array
     */
    private function getActiveAttributes(): array
    {
        if ($this->activeAttributes === null) {
            $this->activeAttributes = $this->ruleResource->getActiveAttributes();
        }

        return $this->activeAttributes;
    }
}
