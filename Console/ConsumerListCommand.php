<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Console;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;

/**
 * Command for starting MessageQueue consumers.
 * @since 2.0.0
 */
class ConsumerListCommand extends Command
{
    const COMMAND_QUEUE_CONSUMERS_LIST = 'queue:consumers:list';

    /**
     * @var ConsumerConfig
     * @since 2.2.0
     */
    private $consumerConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     * @param string|null $name
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function __construct(QueueConfig $queueConfig, $name = null)
    {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumers = $this->getConsumers();
        $output->writeln($consumers);
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_CONSUMERS_LIST);
        $this->setDescription('List of MessageQueue consumers');
        $this->setHelp(
            <<<HELP
This command shows list of MessageQueue consumers.
HELP
        );
        parent::configure();
    }

    /**
     * @return string[]
     * @since 2.0.0
     */
    private function getConsumers()
    {
        $consumerNames = [];
        foreach ($this->getConsumerConfig()->getConsumers() as $consumer) {
            $consumerNames[] = $consumer->getName();
        }
        return $consumerNames;
    }

    /**
     * Get consumer config.
     *
     * @return ConsumerConfig
     *
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getConsumerConfig()
    {
        if ($this->consumerConfig === null) {
            $this->consumerConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ConsumerConfig::class);
        }
        return $this->consumerConfig;
    }
}
