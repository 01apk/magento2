<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Creditmemo\Total\Cost;
use Magento\Sales\Model\Order\Creditmemo\Total\Discount;
use Magento\Tax\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiscountTest extends TestCase
{
    /**
     * @var Cost
     */
    protected $total;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemoMock;

    /**
     * @var Item|MockObject
     */
    protected $creditmemoItemMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|MockObject
     */
    protected $orderItemMock;

    /**
     * @var Config|MockObject
     */
    private $taxConfig;

    protected function setUp(): void
    {
        $this->orderMock = $this->createPartialMock(
            Order::class,
            ['getBaseShippingDiscountAmount', 'getBaseShippingAmount', 'getShippingAmount']
        );
        $this->orderItemMock = $this->createPartialMock(Order::class, [
                'isDummy', 'getDiscountInvoiced', 'getBaseDiscountInvoiced', 'getQtyInvoiced', 'getQty',
                'getDiscountRefunded', 'getQtyRefunded'
            ]);
        $this->creditmemoMock = $this->createPartialMock(Creditmemo::class, [
                'setBaseCost', 'getAllItems', 'getOrder', 'getBaseShippingAmount', 'roundPrice',
                'setDiscountAmount', 'setBaseDiscountAmount', 'getBaseShippingInclTax', 'getBaseShippingTaxAmount'
            ]);
        $this->creditmemoItemMock = $this->createPartialMock(Item::class, [
                'getHasChildren', 'getBaseCost', 'getQty', 'getOrderItem', 'setDiscountAmount',
                'setBaseDiscountAmount', 'isLast'
            ]);
        $this->taxConfig = $this->createMock(Config::class);

        $this->total = new Discount($this->taxConfig);
    }

    public function testCollect()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingDiscountAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->exactly(2))
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectNoBaseShippingAmount()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn(0);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingInclTax')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingTaxAmount')
            ->willReturn(0);
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingDiscountAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->exactly(2))
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectZeroShipping()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn('0.0000');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingDiscountAmount');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingAmount');
        $this->orderMock->expects($this->never())
            ->method('getShippingAmount');
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectNonZeroShipping()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('You can not refund shipping if there is no shipping amount.');
        $this->creditmemoMock->expects($this->once())
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn('10.0000');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingDiscountAmount');
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn('0.0000');
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }
}
