<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\Structure\Converter;
use Magento\Config\Model\Config\Structure\Mapper\Dependencies;
use Magento\Config\Model\Config\Structure\Mapper\Factory;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(Factory::class);

        $mapperMock = $this->createMock(Dependencies::class);
        $mapperMock->expects($this->any())->method('map')->will($this->returnArgument(0));
        $factoryMock->expects($this->any())->method('create')->will($this->returnValue($mapperMock));

        $this->_model = new Converter($factoryMock);
    }

    public function testConvertCorrectlyConvertsConfigStructureToArray()
    {
        $testDom = dirname(dirname(__DIR__)) . '/_files/system_2.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $expectedArray = include dirname(dirname(__DIR__)) . '/_files/converted_config.php';
        $this->assertEquals($expectedArray, $this->_model->convert($dom));
    }
}
