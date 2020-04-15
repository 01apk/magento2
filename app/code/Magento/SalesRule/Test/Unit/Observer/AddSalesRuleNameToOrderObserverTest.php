<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Observer\AddSalesRuleNameToOrderObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddSalesRuleNameToOrderObserverTest extends TestCase
{
    /**
     * @var AddSalesRuleNameToOrderObserver|MockObject
     */
    protected $model;

    /**
     * @var Coupon|MockObject
     */
    protected $couponMock;

    /**
     * @var RuleFactory|MockObject
     */
    protected $ruleFactory;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            AddSalesRuleNameToOrderObserver::class,
            [
                'ruleFactory' => $this->ruleFactory,
                'coupon' => $this->couponMock,
            ]
        );
    }

    protected function initMocks()
    {
        $this->couponMock = $this->createPartialMock(Coupon::class, [
                '__wakeup',
                'save',
                'load',
                'getId',
                'setTimesUsed',
                'getTimesUsed',
                'getRuleId',
                'loadByCode',
                'updateCustomerCouponTimesUsed'
            ]);
        $this->ruleFactory = $this->createPartialMock(RuleFactory::class, ['create']);
    }

    public function testAddSalesRuleNameToOrderWithoutCouponCode()
    {
        $observer = $this->createPartialMock(Observer::class, ['getOrder']);
        $order = $this->createPartialMock(
            Order::class,
            ['setCouponRuleName', 'getCouponCode', '__wakeup']
        );

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $this->couponMock->expects($this->never())
            ->method('loadByCode');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testAddSalesRuleNameToOrderWithoutRule()
    {
        $observer = $this->createPartialMock(Observer::class, ['getOrder']);
        $order = $this->createPartialMock(
            Order::class,
            ['setCouponRuleName', 'getCouponCode', '__wakeup']
        );
        $couponCode = 'coupon code';

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $order->expects($this->once())
            ->method('getCouponCode')
            ->will($this->returnValue($couponCode));
        $this->ruleFactory->expects($this->never())
            ->method('create');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testAddSalesRuleNameToOrder()
    {
        $observer = $this->createPartialMock(Observer::class, ['getOrder']);
        $rule = $this->createPartialMock(Rule::class, ['load', 'getName', '__wakeup']);
        $order = $this->createPartialMock(
            Order::class,
            ['setCouponRuleName', 'getCouponCode', '__wakeup']
        );
        $couponCode = 'coupon code';
        $ruleId = 1;

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $order->expects($this->once())
            ->method('getCouponCode')
            ->will($this->returnValue($couponCode));
        $this->couponMock->expects($this->once())
            ->method('getRuleId')
            ->will($this->returnValue($ruleId));
        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($rule));
        $rule->expects($this->once())
            ->method('load')
            ->with($ruleId)
            ->will($this->returnSelf());
        $order->expects($this->once())
            ->method('setCouponRuleName');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }
}
