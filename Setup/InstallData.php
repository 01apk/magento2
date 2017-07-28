<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var MessageQueueConfig
     * @since 2.0.0
     */
    private $messageQueueConfig;

    /**
     * Initialize dependencies.
     *
     * @param MessageQueueConfig $messageQueueConfig
     * @since 2.0.0
     */
    public function __construct(MessageQueueConfig $messageQueueConfig)
    {
        $this->messageQueueConfig = $messageQueueConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $binds = $this->messageQueueConfig->getBinds();
        $queues = [];
        foreach ($binds as $bind) {
            $queues[] = $bind[MessageQueueConfig::BIND_QUEUE];
        }
        $queues = array_unique($queues);
        /** Populate 'queue' table */
        foreach ($queues as $queueName) {
            $setup->getConnection()->insert($setup->getTable('queue'), ['name' => $queueName]);
        }

        $setup->endSetup();
    }
}
