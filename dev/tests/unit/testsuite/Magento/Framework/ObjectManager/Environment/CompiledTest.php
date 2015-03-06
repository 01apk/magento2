<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Environment;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\Environment\Compiled
     */
    protected $_compiled;

    protected function setUp()
    {
        $envFactoryMock = $this->getMock('Magento\Framework\ObjectManager\EnvironmentFactory', [], [], '', false);
        $this->_compiled = new \Magento\Framework\ObjectManager\Environment\CompiledTesting($envFactoryMock);
    }

    public function testGetMode()
    {
        $this->assertEquals(Compiled::MODE, $this->_compiled->getMode());
    }

    public function testGetObjectManagerFactory()
    {
        $this->assertInstanceOf(
            'Magento\Framework\ObjectManager\Factory\Compiled',
            $this->_compiled->getObjectManagerFactory(['shared_instances' => []])
        );
    }
}
