<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp;

use Magento\Framework\App\DeploymentConfig;

/**
 * Reads the RabbitMQ config in the deployed environment
 */
class RabbitMqConfig
{
    /**
     * Queue config key
     */
    const QUEUE_CONFIG = 'queue';

    /**
     * RabbitMQ config key
     */
    const RABBITMQ_CONFIG = 'rabbit';

    const HOST = 'host';
    const PORT = 'port';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const VIRTUAL_HOST = 'virtual_host';
    const SSL = 'ssl';

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $config;

    /**
     * Associative array of RabbitMQ configuration
     *
     * @var array
     */
    private $rabbitConfig;

    /**
     * Constructor
     *
     * Example environment config:
     * 'queue' =>
     *     array(
     *         'rabbit' => array(
     *             'host' => 'localhost',
     *             'port' => 5672,
     *             'username' => 'guest',
     *             'password' => 'guest',
     *             'virtual_host' => '/',
     *             'ssl' => [],
     *     ),
     * ),
     *
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the configuration set for the key.
     *
     * @param string $key
     * @return string
     */
    public function getValue($key)
    {
        $this->load();
        return isset($this->rabbitConfig[$key]) ? $this->rabbitConfig[$key] : null; 
    }

    /**
     * Load statuses (enabled/disabled) of cache types
     *
     * @return void
     */
    private function load()
    {
        if (null === $this->rabbitConfig) {
            $queueConfig = $this->config->getConfigData(self::QUEUE_CONFIG);
            $this->rabbitConfig = isset($queueConfig[self::RABBITMQ_CONFIG]) ? $queueConfig[self::RABBITMQ_CONFIG] : [];
        }
    }
}
