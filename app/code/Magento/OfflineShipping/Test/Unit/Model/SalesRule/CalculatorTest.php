<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    /**
     * @var Calculator|MockObject
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = $this->createPartialMock(
            Calculator::class,
            ['_getRules', '__wakeup']
        );
    }

    /**
     * @return bool
     */
    public function testProcessFreeShipping()
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->createPartialMock(Item::class, ['getAddress', '__wakeup']);
        $item->expects($this->once())->method('getAddress')->will($this->returnValue($addressMock));

        $this->_model->expects($this->once())
            ->method('_getRules')
            ->with($addressMock)
            ->will($this->returnValue([]));

        $this->assertInstanceOf(
            Calculator::class,
            $this->_model->processFreeShipping($item)
        );

        return true;
    }
}
