<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\Search\Request\Dimension;
use Magento\Elasticsearch\Model\Config;

/**
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Elasticsearch/_files/products.php
 */
class IndexHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var Dimension
     */
    protected $dimension;

    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var Product
     */
    protected $productApple;

    /**
     * @var Product
     */
    protected $productBanana;

    /**
     * @var Product
     */
    protected $productOrange;

    /**
     * @var Product
     */
    protected $productPapaya;

    /**
     * @var Product
     */
    protected $productCherry;

    /**
     * Setup method
     */
    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(
            'Magento\Indexer\Model\Indexer'
        );
        $this->indexer->load('catalogsearch_fulltext');

        $this->connectionManager = Bootstrap::getObjectManager()->create(
            'Magento\Elasticsearch\SearchAdapter\ConnectionManager'
        );

        $this->client = $this->connectionManager->getConnection();

        $this->dimension = Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Search\Request\Dimension',
            ['name' => 'scope', 'value' => '1']
        );

        $this->clientConfig = Bootstrap::getObjectManager()->create(
            'Magento\Elasticsearch\Model\Config'
        );

        $this->productApple = $this->getProductBySku('fulltext-1');
        $this->productBanana = $this->getProductBySku('fulltext-2');
        $this->productOrange = $this->getProductBySku('fulltext-3');
        $this->productPapaya = $this->getProductBySku('fulltext-4');
        $this->productCherry = $this->getProductBySku('fulltext-5');
    }

    /**
     * Test reindex process
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testReindexAll()
    {
        $this->indexer->reindexAll();

        $products = $this->searchByName('Apple');
        $this->assertCount(1, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]['_id']);

        $products = $this->searchByName('Simple Product');
        $this->assertCount(5, $products);
    }

    /**
     * Search docs in Elasticsearch by name
     *
     * @param string $text
     * @return array
     */
    protected function searchByName($text)
    {
        $storeId = $this->dimension->getValue();
        $searchQuery = [
            'index' => $this->clientConfig->getIndexName(),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'must' => [
                            [
                                'term' => [
                                    'store_id' => $storeId,
                                ]
                            ],
                        ],
                        'should' => [
                            [
                                'match' => [
                                    'name' => $text,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->client->query($searchQuery);
        $products = isset($queryResult['hits']['hits']) ? $queryResult['hits']['hits'] : [];
        return $products;
    }

    /**
     * Return product by SKU
     *
     * @param string $sku
     * @return Product
     */
    protected function getProductBySku($sku)
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product'
        );
        return $product->loadByAttribute('sku', $sku);
    }
}
