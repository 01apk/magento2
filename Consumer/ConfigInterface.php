<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;

/**
 * Consumer config interface provides access data declared in etc/queue_consumer.xml
 */
interface ConfigInterface
{
    /**
     * Get consumer configuration by consumer name.
     *
     * @param string
     * @return ConsumerConfigItemInterface
     * @throws LocalizedException
     */
    public function getConsumer($name);

    /**
     * Get list of all consumers declared in the system.
     * 
     * @return ConsumerConfigItemInterface[]
     */
    public function getConsumers();
}
