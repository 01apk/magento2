<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Model\AttributeFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class AttributeFactoryTest extends TestCase
{
    /**
     * @var AttributeFactory
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_arguments = ['test1', 'test2'];

    /**
     * @var string
     */
    protected $_className = 'Test_Class';

    protected function setUp(): void
    {
        /** @var $objectManagerMock \Magento\Framework\ObjectManagerInterface */
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnCallback([$this, 'getModelInstance'])
        );

        $this->_factory = new AttributeFactory($objectManagerMock);
    }

    protected function tearDown(): void
    {
        unset($this->_factory);
    }

    /**
     * @covers \Magento\Eav\Model\AttributeFactory::createAttribute
     */
    public function testCreateAttribute()
    {
        $this->assertEquals($this->_className, $this->_factory->createAttribute($this->_className, $this->_arguments));
    }

    /**
     * @param $className
     * @param $arguments
     * @return mixed
     */
    public function getModelInstance($className, $arguments)
    {
        $this->assertArrayHasKey('data', $arguments);
        $this->assertEquals($this->_arguments, $arguments['data']);

        return $className;
    }
}
