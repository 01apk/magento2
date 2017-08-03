<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Elasticsearch\Model\Config;

/**
 * Alias name resolver
 * @api
 * @since 2.1.0
 */
class SearchIndexNameResolver
{
    /**
     * @var Config
     * @since 2.1.0
     */
    private $clientConfig;

    /**
     * @param Config $clientConfig
     * @since 2.1.0
     */
    public function __construct(
        Config $clientConfig
    ) {
        $this->clientConfig = $clientConfig;
    }

    /**
     * Returns the index (alias) name
     *
     * @param int $storeId
     * @param string $indexerId
     * @return string
     * @since 2.1.0
     */
    public function getIndexName($storeId, $indexerId)
    {
        $mappedIndexerId = $this->getIndexMapping($indexerId);
        return $this->clientConfig->getIndexPrefix() . '_' . $mappedIndexerId . '_' . $storeId;
    }

    /**
     * Get index name by indexer ID
     *
     * @param string $indexerId
     * @return string
     * @since 2.1.0
     */
    private function getIndexMapping($indexerId)
    {
        if ($indexerId == Fulltext::INDEXER_ID) {
            $mappedIndexerId = 'product';
        } else {
            $mappedIndexerId = $indexerId;
        }
        return $mappedIndexerId;
    }
}
