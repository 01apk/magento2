<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;

class DataProvider implements DataProviderInterface
{
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var Range
     */
    protected $range;

    /**
     * @var IntervalFactory
     */
    protected $intervalFactory;

    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @param ConnectionManager $connectionManager
     * @param FieldMapperInterface $fieldMapper
     * @param Range $range
     * @param IntervalFactory $intervalFactory
     * @param Config $clientConfig
     */
    public function __construct(
        ConnectionManager $connectionManager,
        FieldMapperInterface $fieldMapper,
        Range $range,
        IntervalFactory $intervalFactory,
        Config $clientConfig
    ) {
        $this->connectionManager = $connectionManager;
        $this->fieldMapper = $fieldMapper;
        $this->range = $range;
        $this->intervalFactory = $intervalFactory;
        $this->clientConfig = $clientConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregations = [
            'count' => 0,
            'max' => 0,
            'min' => 0,
            'std' => 0,
        ];
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName('price');
        $requestQuery = [
            'index' => $this->clientConfig->getIndexName(),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'fields' => [
                    '_id',
                    '_score',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'store_id' => 1,
                                ],
                            ],
                            [
                                'terms' => [
                                    '_id' => $entityIds,
                                ],
                            ],
                        ],
                    ],
                ],
                'aggregations' => [
                    'price' => [
                        'nested' => [
                            'path' => $fieldName,
                        ],
                        'aggregations' => [
                            'price_stats' => [
                                'extended_stats' => [
                                    'field' => $fieldName . '.price',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);

        if (isset($queryResult['aggregations']['price']['price_stats'])) {
            $aggregations = [
                'count' => $queryResult['aggregations']['price']['price_stats']['count'],
                'max' => $queryResult['aggregations']['price']['price_stats']['max'],
                'min' => $queryResult['aggregations']['price']['price_stats']['min'],
                'std' => $queryResult['aggregations']['price']['price_stats']['std_deviation'],
            ];
        }

        return $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        $query = [];
        $fieldName = $this->fieldMapper->getFieldName($bucket->getField());
//        $mergedEntityIds = implode(' ' . Query::QUERY_OPERATOR_OR . ' ', $entityStorage->getSource());
//        $this->dimensionsBuilder->build($dimensions, $query);
//        $query->addField($fieldName)
//            ->createFilterQuery('interval')
//            ->setQuery('id:(%1%)', [$mergedEntityIds]);

        return $this->intervalFactory->create(['query' => $query, 'fieldName' => $fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        $result = [];

//        if (!$entityStorage->getSource()) {
//            return $result;
//        }
//
//        $query = $this->queryFactory->create();
//        $query->createFilterQuery('ids')
//            ->setQuery(
//                'id:(%1%)',
//                [
//                    implode(' ' . Query::QUERY_OPERATOR_OR . ' ', $entityStorage->getSource()),
//                ]
//            );
//        $this->dimensionsBuilder->build($dimensions, $query);
//
//        $facetSet = $query->getFacetSet();
//        /** @var \Solarium\QueryType\Select\Query\Component\Facet\Range $facet */
//        $facet = $facetSet->createFacetRange($bucket->getName());
//        $facet->setField($this->fieldMapper->getFieldName($bucket->getField()));
//        $facet->setStart(0);
//        $facet->setEnd($this->getAggregations($entityStorage)['max']);
//        $facet->setGap($range);
//        $facet->setMinCount(1);
//
//        $resultBucket = $this->connectionManager->getConnection()
//            ->query($query)
//            ->getFacetSet()
//            ->getFacet($bucket->getName());
//        foreach ($resultBucket as $rangeStart => $count) {
//            $result[$rangeStart / $range + 1] = $count;
//        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;

                $data[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $data;
    }
}
