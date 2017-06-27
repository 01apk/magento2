<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;

/**
 * @api
 */
interface QueryInterface
{
    /**
     * @param array $selectQuery
     * @param RequestQueryInterface $requestQuery
     * @param string $conditionType
     * @return array
     */
    public function build(
        array $selectQuery,
        RequestQueryInterface $requestQuery,
        $conditionType
    );
}
