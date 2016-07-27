<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Base test case for message queue tests.
 */
abstract class QueueTestCaseAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[]
     */
    protected $consumers = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->publisher = $this->objectManager->get(PublisherInterface::class);
        /** @var \Magento\Framework\OsInfo $osInfo */
        $osInfo = $this->objectManager->get('Magento\Framework\OsInfo');
        if ($osInfo->isWindows()) {
            $this->markTestSkipped("This test relies on *nix shell and should be skipped in Windows environment.");
        }
        parent::setUp();
        foreach ($this->consumers as $consumer) {
            if (!$this->getConsumerProcessIds($consumer)) {
                exec("{$this->getConsumerStartCommand($consumer)} > /dev/null &");
            }
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        foreach ($this->consumers as $consumer) {
            foreach ($this->getConsumerProcessIds($consumer) as $consumerProcessId) {
                exec("kill {$consumerProcessId}");
            }
        }
    }

    /**
     * @param string $consumer
     * @return string[]
     */
    protected function getConsumerProcessIds($consumer)
    {
        exec("ps ax | grep -v grep | grep '{$this->getConsumerStartCommand($consumer)}' | awk '{print $1}'", $output);
        return $output;
    }

    /**
     * @param string $consumer
     * @return string
     */
    protected function getConsumerStartCommand($consumer)
    {
        $params = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams();
        $params['MAGE_DIRS']['base']['path'] = BP;
        $params = 'TEST_PARAMS="' . urldecode(http_build_query($params)) . '"';
        $binDirectory = realpath(TESTS_TEMP_DIR . '/../bin/');
        $magentoCli = $binDirectory . '/magento';
        $consumerStartCommand = $params . " php {$magentoCli} queue:consumers:start -vvv " . $consumer;
        return $consumerStartCommand;
    }
}
