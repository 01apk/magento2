<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Related;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Related\RelatedDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CrossSell Products Resolver
 */
class CrossSellProducts implements ResolverInterface
{
    /**
     * @see module di.xml
     * @var RelatedDataProvider
     */
    private $dataProvider;

    /**
     * @param RelatedDataProvider $dataProvider
     */
    public function __construct(
        RelatedDataProvider $dataProvider
    ) {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $data = $this->dataProvider->getProducts($info, $value);

        return $data;
    }
}
