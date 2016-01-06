<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Config;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\Config\Reader\Env;

/**
 * @codingStandardsIgnoreFile
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter
     */
    private $converter;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $communicationConfigMock;

    /**
     * @var \Magento\Framework\MessageQueue\Config\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->communicationConfigMock = $this->getMockBuilder('Magento\Framework\Communication\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->converter = $objectManager->getObject(
            'Magento\Framework\MessageQueue\Config\Reader\Xml\Converter',
            [
                'communicationConfig' => $this->communicationConfigMock,
                'xmlValidator' => $this->validatorMock
            ]
        );
    }

    /**
     * Test converting valid configuration
     */
    public function testConvert()
    {
        $this->communicationConfigMock->expects($this->any())->method('getTopics')->willReturn([]);
        $this->validatorMock->expects($this->any())
            ->method('buildWildcardPattern')
            ->willReturnMap($this->getWildcardPatternMap());
        $expected = $this->getConvertedQueueConfig();
        $xmlFile = __DIR__ . '/_files/queue.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Get content of _files/queue.xml converted into array.
     *
     * @return array
     */
    protected function getConvertedQueueConfig()
    {
        return include(__DIR__ . '/_files/expected_queue.php');
    }

    /**
     * Get wildcard pattern map from the file
     *
     * @return array
     */
    protected function getWildcardPatternMap()
    {
        return include(__DIR__ . '/_files/wildcard_pattern_map.php');
    }
}
