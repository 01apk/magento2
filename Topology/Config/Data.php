<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Topology config data storage. Caches merged config.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        ReaderInterface $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'message_queue_topology_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
