<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * Class \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Term
 *
 * @since 2.1.0
 */
class Term implements BucketBuilderInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        $values = [];
        foreach ($queryResult['aggregations'][$bucket->getName()]['buckets'] as $resultBucket) {
            $values[$resultBucket['key']] = [
                'value' => $resultBucket['key'],
                'count' => $resultBucket['doc_count'],
            ];
        }
        return $values;
    }
}
