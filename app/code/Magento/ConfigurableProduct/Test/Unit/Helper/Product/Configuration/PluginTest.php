<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Helper\Product\Configuration;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\ConfigurableProduct\Helper\Product\Configuration\Plugin;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $itemMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp(): void
    {
        $this->itemMock = $this->createMock(ItemInterface::class);
        $this->productMock = $this->createMock(Product::class);
        $this->typeInstanceMock = $this->createPartialMock(
            Configurable::class,
            ['getSelectedAttributesInfo', '__wakeup']
        );
        $this->itemMock->expects($this->once())->method('getProduct')->will($this->returnValue($this->productMock));
        $this->closureMock = function () {
            return ['options'];
        };
        $this->subjectMock = $this->createMock(Configuration::class);
        $this->plugin = new Plugin();
    }

    public function testAroundGetOptionsWhenProductTypeIsConfigurable()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(Configurable::TYPE_CODE)
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->typeInstanceMock)
        );
        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getSelectedAttributesInfo'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(['attributes'])
        );
        $this->assertEquals(
            ['attributes', 'options'],
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetOptionsWhenProductTypeIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('simple'));
        $this->productMock->expects($this->never())->method('getTypeInstance');
        $this->assertEquals(
            ['options'],
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }
}
