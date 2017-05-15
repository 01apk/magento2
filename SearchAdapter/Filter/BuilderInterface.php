<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * @api
 */
interface BuilderInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @return string
     */
    public function build(RequestFilterInterface $filter, $conditionType);
}
