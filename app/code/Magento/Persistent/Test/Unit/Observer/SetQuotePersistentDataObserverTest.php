<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Magento\Persistent\Observer\SetQuotePersistentDataObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Quote\Model\Quote;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Model\QuoteManager;

/**
 * Observer test for setting "is_persistent" value to quote
 */
class SetQuotePersistentDataObserverTest extends TestCase
{
    /**
     * @var SetQuotePersistentDataObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    protected function setUp(): void
    {
        $quoteMethods = ['setIsActive', 'setIsPersistent', '__wakeUp'];
        $eventMethods = ['getQuote', '__wakeUp'];
        $this->quoteMock = $this->createPartialMock(Quote::class, $quoteMethods);
        $this->helperMock = $this->createMock(Data::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->eventManagerMock = $this->createPartialMock(Event::class, $eventMethods);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->model = new SetQuotePersistentDataObserver(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->quoteManagerMock,
            $this->customerSessionMock
        );
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenQuoteNotExist()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock->expects($this->once())->method('getQuote');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionIsPersistent()
    {
        $this->sessionHelperMock->expects($this->exactly(2))->method('isPersistent')->will($this->returnValue(true));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(true));
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->quoteMock->expects($this->once())->method('setIsPersistent')->with(true);
        $this->model->execute($this->observerMock);
    }
}
