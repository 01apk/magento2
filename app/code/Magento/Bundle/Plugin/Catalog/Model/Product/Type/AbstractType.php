<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Catalog\Model\Product\Type;

use Magento\Catalog\Model\Product\Type\AbstractType as Subject;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as BundleType;

/**
 * Plugin to add possibility to add bundle product with single option from list
 */
class AbstractType
{
    /**
     * Add possibility to add to cart from the list in case of one required option
     *
     * @param Subject $subject
     * @param bool $result
     * @param Product $product
     * @return bool
     */
    public function afterIsPossibleBuyFromList(Subject $subject, $result, $product)
    {
        if ($product->getTypeId() === BundleType::TYPE_BUNDLE) {
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            $result = count($typeInstance->getOptionsIds($product)) === 1
                && $typeInstance->hasRequiredOptions($product);
        }
        return $result;
    }
}
