<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Configuration for the consumer.
 * @since 2.0.0
 */
interface ConsumerConfigurationInterface
{
    const CONSUMER_NAME = "consumer_name";

    const QUEUE_NAME = "queue_name";
    const MAX_MESSAGES = "max_messages";
    const SCHEMA_TYPE = "schema_type";
    const TOPICS = 'topics';
    const TOPIC_TYPE = 'consumer_type';
    const TOPIC_HANDLERS = 'handlers';

    const TYPE_SYNC = 'sync';
    const TYPE_ASYNC = 'async';
    const INSTANCE_TYPE_BATCH = 'batch';
    const INSTANCE_TYPE_SINGULAR = 'singular';

    /**
     * Get consumer name.
     *
     * @return string
     * @since 2.0.0
     */
    public function getConsumerName();

    /**
     * Get the name of queue which consumer will read from.
     *
     * @return string
     * @since 2.0.0
     */
    public function getQueueName();

    /**
     * Get consumer type sync|async.
     *
     * @return string
     * @deprecated 2.2.0
     * @see \Magento\Framework\Communication\ConfigInterface::getTopic
     * @throws \LogicException
     * @since 2.1.0
     */
    public function getType();

    /**
     * Get maximum number of message, which will be read by consumer before termination of the process.
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getMaxMessages();

    /**
     * Get handlers by topic type.
     *
     * @param string $topicName
     * @return callback[]
     * @throws \LogicException
     * @since 2.1.0
     */
    public function getHandlers($topicName);

    /**
     * Get topics.
     *
     * @return string[]
     * @since 2.1.0
     */
    public function getTopicNames();

    /**
     * @param string $topicName
     * @return string
     * @since 2.1.0
     */
    public function getMessageSchemaType($topicName);

    /**
     * @return QueueInterface
     * @since 2.1.0
     */
    public function getQueue();
}
